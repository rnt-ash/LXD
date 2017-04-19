<?php
/**
* @copyright Copyright (c) ARONET GmbH (https://aronet.swiss)
* @license AGPL-3.0
*
* This code is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License, version 3,
* along with this program.  If not, see <http://www.gnu.org/licenses/>
*
*/

namespace RNTForest\ovz\controllers;

use Phalcon\Http\Client\Request;

use RNTForest\ovz\models\VirtualServers;
use RNTForest\ovz\models\PhysicalServers;
use RNTForest\ovz\models\IpObjects;
use RNTForest\core\models\Customers;
use RNTForest\core\models\Logins;
use RNTForest\ovz\forms\VirtualServersForm;
use RNTForest\ovz\forms\VirtualServersConfigureForm;
use RNTForest\ovz\forms\VirtualServersModifyForm;
use RNTForest\ovz\forms\IpObjectsForm;
use RNTForest\ovz\forms\SnapshotForm;
use RNTForest\ovz\forms\ReplicaActivateForm;
use RNTForest\ovz\forms\RootPasswordChangeForm;
use RNTForest\ovz\models\MonLocalJobs;
use RNTForest\ovz\models\MonRemoteJobs;
use RNTForest\ovz\forms\MonLocalJobsForm;
use RNTForest\ovz\forms\MonRemoteJobsForm;

use \RNTForest\core\libraries\Helpers;

class VirtualServersControllerBase extends \RNTForest\core\controllers\TableSlideBase
{
    protected function getSlideDataInfo() {
        $scope = $this->permissions->getScope('virtual_servers','general');
        $scopeQuery = 'RNTForest\ovz\models\VirtualServers.ovz_replica < 2';
        $joinQuery = NULL;
        if ($scope == 'customers'){
            $scopeQuery .= " AND customers_id = ".$this->session->get('auth')['customers_id'];
        } else if($scope == 'partners'){
            $scopeQuery .= 'AND (RNTForest\ovz\models\VirtualServers.customers_id = '.$this->session->get('auth')['customers_id'];
            $scopeQuery .= ' OR RNTForest\core\models\CustomersPartners.partners_id = '.$this->session->get('auth')['customers_id'].")";
            $joinQuery = array('model'=>'RNTForest\core\models\CustomersPartners',
                'conditions'=>'RNTForest\ovz\models\VirtualServers.customers_id = RNTForest\core\models\CustomersPartners.customers_id',
                'type'=>'LEFT');
        }

        return array(
            "type" => "slideData",
            "model" => '\RNTForest\ovz\models\VirtualServers',
            "form" => '\RNTForest\ovz\forms\VirtualServersForm',
            "controller" => "virtual_servers",
            "action" => "slidedata",
            "slidenamefield" => "name",
            "slidenamefielddescription" => "Servername",
            "scope" => $scopeQuery,
            "join" => $joinQuery,
            "order" => "name",
            "orderdir" => "ASC",
            "filters" => array(),
            "page" => 1,
            "limit" => 10,
        );
    }

    protected function prepareSlideFilters($virtualServers,$level) { 

        // put resultsets to the view
        $scope = $this->permissions->getScope("virtual_servers","filter_customers");
        $this->view->customers = Customers::generateArrayForSelectElement($scope);

        $scope = $this->permissions->getScope("virtual_servers","filter_physical_servers");
        $this->view->physicalServers = PhysicalServers::generateArrayForSelectElement($scope);

        // receive all filters
        if($this->request->has('filterAll')){
            $oldfilter = $this->slideDataInfo['filters']['filterAll'];
            $this->slideDataInfo['filters']['filterAll'] = $this->request->get("filterAll", "string");
            if($oldfilter != $this->slideDataInfo['filters']['filterAll']) $this->slideDataInfo['page'] = 1;
        }

        if($this->request->has('filterCustomers')){
            $oldfilter = $this->slideDataInfo['filters']['filterCustomers'];
            $this->slideDataInfo['filters']['filterCustomers'] = $this->request->get("filterCustomers", "int");
            if($oldfilter != $this->slideDataInfo['filters']['filterCustomers']) $this->slideDataInfo['page'] = 1;
        }

        if($this->request->has('filterPhysicalServers')){
            $oldfilter = $this->slideDataInfo['filters']['filterPhysicalServers'];
            $this->slideDataInfo['filters']['filterPhysicalServers'] = $this->request->get("filterPhysicalServers", "int");
            if($oldfilter != $this->slideDataInfo['filters']['filterPhysicalServers']) $this->slideDataInfo['page'] = 1;
        }
    }

    protected function isValidSlideFilterItem($virtualServer,$level){
        if(!empty($this->slideDataInfo['filters']['filterAll'])){ 
            if(strpos(strtolower($virtualServer->name),strtolower($this->slideDataInfo['filters']['filterAll']))===false)            
                return false;
        }
        if(!empty($this->slideDataInfo['filters']['filterCustomers'])){ 
            if($virtualServer->customers_id != $this->slideDataInfo['filters']['filterCustomers'])
                return false;
        }
        if(!empty($this->slideDataInfo['filters']['filterPhysicalServers'])){ 
            if($virtualServer->physical_servers_id != $this->slideDataInfo['filters']['filterPhysicalServers'])
                return false;
        }
        return true; 
    }

    protected function renderSlideHeader($item,$level){
        switch($level){
            case 0:
                return $item->name; 
                break;
            default:
                $message = $this->translate("virtualserver_invalid_level");
                return $message;
        }
    }

    protected function renderSlideDetail($item,$level){
        // Slidelevel ignored because there is only one level
        $content = "";
        $this->simpleview->item = $item;
        $this->simpleview->snapshots = $this->ovzSnapshotRenderList($item);
        $content .= $this->simpleview->render("partials/ovz/virtual_servers/slideDetail.volt");
        return $content;
    }

    /**
    * check if Virtual or Physical Server is OVZ enabled. Otherwise it throws an exception
    * 
    * @param server $server
    * @throws Exceptions
    */
    public static function tryCheckOvzEnabled($server) {
        // check if server is ovz enabled   
        if($server->getOvz() == 0){
            $message = self::translate("virtualserver_server_not_ovz_enabled");
            throw new \Exception($message);
        }
    }

    /**
    * updates OVZ settings and statistics
    * 
    * @param int $serverId
    */
    public function ovzUpdateInfoAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            // validating
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','general',array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_list_info job 
            $this->tryOvzListInfo($virtualServer);

            // execute ovz_statistics_info job 
            $this->tryOvzStatisticInfo($virtualServer);

            // success
            $message = $this->translate("virtualserver_info_success");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }
    
    /**
    * execute ovz_list_info job
    * 
    * @param \RNTForest\ovz\models\VirtualServers $virtualServer
    * @return {\RNTForest\core\models\Jobs|\RNTForest\core\models\JobsBase}
    * @throws \Exceptions
    */
    protected function tryOvzListInfo($virtualServer){
        
        // check if update is realy needed
        if(!empty($virtualServer->getOvzSettingsArray()['Timestamp'])){
            $lastupdate = new \DateTime($virtualServer->getOvzSettingsArray()['Timestamp']);
            if ($lastupdate->diff(new \DateTime())->format('%s') <= 10) return true;
        }
        
        // no pending needed because job reads only    
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_list_info',$params);
         
        // save settings to virtual server
        $this->virutalServerSettingsSave($job,$virtualServer);

        return $job;
    }
    
    /**
    * execute ovz_statistics_info job
    * 
    * @param \RNTForest\ovz\models\VirtualServers $virtualServer
    * @return {\RNTForest\core\models\Jobs|\RNTForest\core\models\JobsBase}
    * @throws \Exceptions
    */
    protected function tryOvzStatisticInfo($virtualServer){
        
        // check if update is realy needed
        if(!empty($virtualServer->getOvzStatisticsArray()['Timestamp'])){
            $lastupdate = new \DateTime($virtualServer->getOvzStatisticsArray()['Timestamp']);
            if ($lastupdate->diff(new \DateTime())->format('%s') <= 10) return true;
        }
                
        // no pending needed because job reads only    
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_statistics_info',$params);
         
        // save settings to virtual server
        $virtualServer->setOvzStatistics($job->getRetval());

        return $job;
    }

    /**
    * execute ovz_start_vs job
    * 
    * @param \RNTForest\ovz\models\VirtualServers $virtualServer
    * @return {\RNTForest\core\models\Jobs|\RNTForest\core\models\JobsBase}
    * @throws \Exceptions
    */
    protected function tryStartVS($virtualServer){

        // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
        $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_start_vs',$params,$pending);
        
        return $job;
    }
        
    /**
    * execute ovz_stop_vs job
    * 
    * @param \RNTForest\ovz\models\VirtualServers $virtualServer
    * @return {\RNTForest\core\models\Jobs|\RNTForest\core\models\JobsBase}
    * @throws \Exceptions
    */
    protected function tryStopVS($virtualServer){

        // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
        $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_stop_vs',$params,$pending);
        
        return $job;
    }

    /**
    * execute ovz_restart_vs job
    * 
    * @param \RNTForest\ovz\models\VirtualServers $virtualServer
    * @return {\RNTForest\core\models\Jobs|\RNTForest\core\models\JobsBase}
    * @throws \Exceptions
    */
    protected function tryRestartVS($virtualServer){

        // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
        $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_restart_vs',$params,$pending);
        
        return $job;
    }

    /**
    * start VS
    * 
    * @param int $serverId
    */
    public function startVSAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','changestate',array("item"=>$virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // try to start
            $job = $this->tryStartVS($virtualServer);

            // save new state            
            $this->virutalServerSettingsSave($job,$virtualServer);

            // success
            $message = $this->translate("virtualserver_job_start");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * stop VS
    * 
    * @param int $serverId
    */
    public function stopVSAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','changestate',array("item"=>$virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);
            
            // try to stop        
            $job = $this->tryStopVS($virtualServer);

            // save new state            
            $this->virutalServerSettingsSave($job,$virtualServer);

            // success
            $message = $this->translate("virtualserver_job_stop");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * restart VS
    * 
    * @param int $serverId
    */
    public function restartVSAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','changestate',array("item"=>$virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // try to restart
            $job = $this->tryRestartVS($virtualServer);

            // save new state            
            $this->virutalServerSettingsSave($job,$virtualServer);

            // success
            $message = self::translate("virtualserver_job_restart");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * deletes a virtual server
    * 
    * @param integer $id
    */
    public function deleteAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','delete',array("item"=>$virtualServer));
            $this->tryCheckPermission('physical_servers','general',array("item"=>$virtualServer->physicalServers));

            // execute ovz_destroy_vs job   
            if($virtualServer->getOvz()){     
                // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
                $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId();
                $params = array("UUID"=>$virtualServer->getOvzUuid());
                $job = $this->getPushService()->executeJob($virtualServer->physicalServers,'ovz_destroy_vs',$params,$pending);
                if($job->getDone() == 2){
                    $message = $this->translate("virtualserver_job_destroy_failed");
                    throw new \Exception($message);
                }elseif(!empty($job->getWarning())){
                    $this->flashSession->warning($job->getRetval());
                }
            }
            
            // delete IP Objects
            foreach($virtualServer->ipobjects as $ipobject){
                if(!$dcoipobject->delete()){
                    foreach ($dcoipobject->getMessages() as $message) {
                        $this->flashSession->error($message);
                    }
                    $message = $this->translate("virtualserver_ipobjects_destroy_failed");
                    throw new \Exception($message);
                }
            }

            // delete DB entry
            if (!$virtualServer->delete()) {
                foreach ($virtualServer->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                $message = $this->translate("virtualserver_server_destroy_failed");
                throw new \Exception($message);
            }

            // success
            $message = $this->translate("virtualserver_job_destroy");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * creates a new Container
    * 
    */
    public function newCTAction(){

        // get OS templates from server
        $push = $this->getPushService();
        $params = array();
        if(!$physicalServer = PhysicalServers::findFirst("ovz = 1")){
            $message = $this->translate("virtualserver_no_physicalserver_found");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }

        // no pending needed because job does only read
        $job = $push->executeJob($physicalServer,'ovz_get_ostemplates',$params);
        $message = $this->translate("virtualserver_job_ostemplates_failed");
        if(!$job || $job->getDone()==2) throw new \Exception($message);
        $retval = $job->getRetval(true);
        $ostemplates = array();
        foreach($retval as $template){
            $ostemplates[$template['name']] = $template['name']." (".$template['lastupdate'].")";
        }

        // store in session
        $this->session->set($this->getFormClassName(), array(
            "op" => "new",
            "vstype" => "CT",
            "ostemplates" => $ostemplates,
            "distribution" => "",
        ));

        $virtualServerForm = new VirtualServersForm(new VirtualServers());
        $this->forwardToFormAction($virtualServerForm);

    }

    /**
    * creates a new Virtual Machine
    * 
    */
    public function newVMAction(){

        // store in session
        $this->session->set($this->getFormClassName(), array(
            "op" => "new",
            "vstype" => "VM",
            //  prlctl set <VM_name> -d list  => get the list
            "distribution" => "debian",
        ));

        $virtualServerForm = new VirtualServersForm(new VirtualServers());
        $this->forwardToFormAction($virtualServerForm);

    }

    /**
    * creates a new independent Virtual Server
    * 
    */
    public function newVSAction(){

        // store in session
        $this->session->set($this->getFormClassName(), array(
            "op" => "new",
            "vstype" => "VS",
        ));

        $virtualServerForm = new VirtualServersForm(new VirtualServers());
        $this->forwardToFormAction($virtualServerForm);

    }

    /**
    * generates a new CT or VM
    * 
    * @param VirtualServers $virtualServer
    * @param VirtualServersForm $form
    */
    protected function postSave($virtualServer,$form){

        try{
            $session = $this->session->get($this->getFormClassName());
            if($session['vstype'] == 'CT' || $session['vstype'] == 'VM' ){
                $virtualServer->setOvz(true);
                $virtualServer->setOvzVstype($session['vstype']);
                $virtualServer->setOvzUuid(\RNTForest\core\libraries\Helpers::genUuid());

                $params = array(
                    "VSTYPE"=>$virtualServer->getOvzVstype(),
                    "UUID"=>$virtualServer->getOvzUuid(),
                    "NAME"=>$virtualServer->getName(),
                    "OSTEMPLATE"=>$form->getValue('ostemplate'),
                    "DISTRIBUTION"=>$session['distribution'],
                    "HOSTNAME"=>$virtualServer->getFqdn(),
                    "CPUS"=>$virtualServer->getCore(),
                    "RAM"=>$virtualServer->getMemory(),
                    "DISKSPACE"=>$virtualServer->getSpace(),
                    "ROOTPWD"=>$form->getValue('password'),
                );
                
                if(!$virtualServer->update()){
                    $message = $this->translate("virtualserver_update_server_failed");
                    throw new \Exception($message);
                }

                // validate physical server
                $physicalServer = PhysicalServers::tryFindById($form->getValue('physical_servers_id'));
                $this->tryCheckPermission('physical_servers','save',array('item' => $physicalServer));
                $this->tryCheckOvzEnabled($physicalServer);

                // execute ovz_new_vs job        
                // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
                $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId();
                $job = $this->getPushService()->executeJob($physicalServer,'ovz_new_vs',$params,$pending);
                if($job->getDone() == 2){
                    $message = $this->translate("virtualserver_job_create_failed");
                    throw new \Exception($message);
                }
            }
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            return false;
        }

        // cleanup
        $session = $this->session->remove($this->getFormClassName());
        return true; 
    }

    /*
    * List snapshots
    * 
    * @param int $serverId
    */
    public function ovzSnapshotListAction($virtualServerId){

        // sanitize parameters
        $virtualServerId = $this->filter->sanitize($virtualServerId, "int");
        
        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServerId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_list_snapshots job 
            // no pending needed because job is readonly       
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_list_snapshots',$params);

            // save snapshots
            $snapshots = $job->getRetval();
            $this->ovzSnapshotSave($virtualServer,$snapshots);

            // success
            $message = $this->translate("virtualserver_snapshot_update");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * switch to an snapshot
    * 
    * @param mixed $snapshotId
    * @param int $serverId
    */
    public function ovzSnapshotSwitchAction($snapshotId,$virtualServerId) {
        // sanitize parameters
        $virtualServerId = $this->filter->sanitize($virtualServerId, "int");
        $snapshotId = $this->filter->sanitize($snapshotId, "string");
        
        try {    
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServerId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_switch_snapshot job
            // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
            $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId();
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'SNAPSHOTID'=>$snapshotId);
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_switch_snapshot',$params,$pending);

            // save snapshots
            $snapshots = $job->getRetval();
            $this->ovzSnapshotSave($virtualServer,$snapshots);

            // success
            $message = $this->translate("virtualserver_snapshot_switched");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * show form to create a snapshot
    * 
    * @param mixed $virtualServersId
    */
    public function ovzSnapshotCreateAction($virtualServersId){

        // sanitize
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");
        
        try{
            // find virtual server
            $virtualServer = VirtualServers::tryFindById($virtualServersId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);
            
            // prepare form fields
            $snapshotFormFields = new SnapshotFormFields();
            $snapshotFormFields->virtual_servers_id = $virtualServersId;
            
            // call view
            $this->view->form = new SnapshotForm($snapshotFormFields); 
            $this->view->pick("virtual_servers/snapshotForm");
            return;
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
    }

    /**
    * create a new snapshot
    * 
    * @param int $serverId
    * @param string $name
    * @param string $description
    */
    public function ovzSnapshotCreateExecuteAction() {
        // POST request?
        if (!$this->request->isPost()) 
            return $this->redirectTo("virtual_servers/slidedata");

        // validate FORM
        $form = new SnapshotForm();
        $item = new SnapshotFormFields();
        $data = $this->request->getPost();
        if (!$form->isValid($data, $item)) {
            $this->view->form = $form; 
            $this->view->pick("virtual_servers/snapshotForm");
            return; 
        }

        try {    
            // validate
            $virtualServer = VirtualServers::tryFindById($item->virtual_servers_id);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_list_snapshots job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'NAME'=>$item->name,'DESCRIPTION'=>$item->description);
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_create_snapshot',$params,$pending);
            
            // save snapshots
            $snapshots = $job->getRetval();
            $this->ovzSnapshotSave($virtualServer,$snapshots);

            // success
            $message = $this->translate("virtualserver_snapshot_created");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * delete snapshot
    * 
    * @param mixed $snapshotId
    * @param int $serverId
    */
    public function ovzSnapshotDeleteAction($snapshotId,$virtualServerId) {

        // sanitize
        $snapshotId = $this->filter->sanitize($snapshotId, "string");
        $virtualServerId = $this->filter->sanitize($virtualServerId, "int");
        
        try {    
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServerId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_delete_snapshot job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'SNAPSHOTID'=>$snapshotId);
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_delete_snapshot',$params,$pending);

            // save snapshots
            $snapshots = $job->getRetval();
            $this->ovzSnapshotSave($virtualServer,$snapshots);

            // success
            $message = $this->translate("virtualserver_snapshot_deleted");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }
    
    /**
    * renders all snapshots to one server
    * 
    * @param mixed $item
    */
    private function ovzSnapshotRenderList($virtualServer){
        // convert the json to an array
        $snapshots = json_decode($virtualServer->getOvzSnapshots(), true);
        if(!is_array($snapshots)) $snapshots=array();

        // sort all the snapshots
        $snapshots = $this->ovzSnapshotGetChilds("",$snapshots);

        return $snapshots;
    }

    /**
    * sorts the array depending of the parent
    * 
    * @param string $parent UUID of the parent snapshot
    * @param array $snapshots array with the snapshots in it
    */
    private function ovzSnapshotGetChilds($parent,$snapshots){ 
        $sortedSnapshots = array();

        // run through all snapshots
        foreach($snapshots as $key=>$snapshot){
            if(key_exists('Parent', $snapshot) && $snapshot['Parent']===$parent) {
                // is the snapshot allowed to be deleted? not if it's mounted or if it's an replica
                $name = explode(" ",$snapshot['Name']);
                if(strcasecmp($name[0],"Replica")){
                    $snapshot['Removable'] = 1; 
                }else {
                    $snapshot['Removable'] = 0;
                }

                // convert the date
                $snapshot['Date'] = date("d.m.Y H:i:s",strtotime($snapshot['Date']));

                // get all child snapshots
                $snapshot['Childs'] = $this->ovzSnapshotGetChilds($snapshot['UUID'],$snapshots);

                // ist a childsnapshot mounted?
                foreach($snapshot['Childs'] as $childSnapshot){
                    if ($childSnapshot['Removable']==0) $snapshot['Removable'] = 0;
                }

                // convert all snapshots to one array
                $sortedSnapshots[] = $snapshot;
            }
        }
        return $sortedSnapshots;
    }
    
    /**
    * Save snapshot settings in virtual server model
    * 
    * @param mixed $virtualServer
    * @param mixed $snapshots
    * @throws /Exceptions
    */
    private function ovzSnapshotSave($virtualServer,$snapshots){
        // set snapshots
        $virtualServer->setOvzSnapshots($snapshots);
        
        // save object
        if ($virtualServer->save() === false) {
            foreach ($virtualServer->getMessages() as $message) {
                $this->flashSession->warning($message);
            }
            $message = $this->translate("virtualserver_update_failed");
            throw new Exception($message.$virtualServer->getName());
        }
    }

    /**
    * Adds an IP Object to the Server
    * 
    * @param integer $id primary key of the virtual Server
    * 
    */
    public function ipObjectAddAction($id){

        // store in session
        $this->session->set("IpObjectsForm", array(
            "op" => "new",
            "server_class" => '\RNTForest\ovz\models\VirtualServers',
            "server_id" => intval($id),
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        $ipobjectsForm = new IpObjectsForm(new IpObjects());

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'ip_objects',
            'action' => 'edit',
            'params' => [$ipobjectsForm],
        ]);
    }

    /**
    * Edits an IP Object to the Server
    * 
    * @param integer $ipobject primary key of the IP Object
    * 
    */
    public function ipObjectEditAction($ipobject){

        // store in session
        $this->session->set("IpObjectsForm", array(
            "op" => "edit",
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'ip_objects',
            'action' => 'edit',
            'params' => [$ipobject],
        ]);
    }

    /**
    * Deletes an IP Object
    * 
    * @param integer $ipobject primary key of the IP Object
    * 
    */
    public function ipObjectDeleteAction($ipobject){

        // store in session
        $this->session->set("IpObjectsForm", array(
            "op" => "delete",
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'ip_objects',
            'action' => 'delete',
            'params' => [$ipobject],
        ]);
    }

    /**
    * Make IP Object to main
    * 
    * @param integer $ipobject primary key of the IP Object
    * 
    */
    public function ipObjectMakeMainAction($ipobject){
        // store in session
        $this->session->set("IpObjectsForm", array(
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'ip_objects',
            'action' => 'makeMain',
            'params' => [$ipobject],
        ]);
    }

    /**
    * show modify form
    * 
    * @param mixed $virtualServersId
    */
    public function virtualServerModifyAction($serverId){

        // sanitize
        $serverId = $this->filter->sanitize($serverId,"int");

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','changestate',array("item"=>$virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);
        
            // call view
            $this->view->form = new VirtualServersModifyForm($virtualServer); 
            $this->view->pick("virtual_servers/virtualServersModifyForm");
            return;

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->redirectTo("virtual_servers/slidedata");
        }
    }

    /**
    * modify server
    * 
    */
    public function virtualServerModifyExecuteAction(){
            // POST request?
            if (!$this->request->isPost()) 
                return $this->redirectTo("virtual_servers/slidedata");

            // sanitize
            $virtualServersId = $this->filter->sanitize($this->request->getPost("id"),"int");

        try {
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServersId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // validate FORM
            $form = new VirtualServersModifyForm;
            $data = $this->request->getPost();
            if (!$form->isValid($data, $virtualServer)) {
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/virtualServersModifyForm");
                return; 
            }

            // update virutal server
            if ($virtualServer->update() === false) {
                // fetch all messages from model
                foreach ($virtualServer->getMessages() as $message) {
                    $form->appendMessage(new \Phalcon\Validation\Message($message->getMessage(),$message->getField()));
                }
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/virtualServersModifyForm");
                return;
            }

            // job
            $virtualServerConfig = array(
                'name' => $virtualServer->getName(),
                'hostname' => $virtualServer->getFqdn(),
                'description' => $virtualServer->getDescription()
            );

            // execute ovz_modify_vs job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array(
                'UUID'=>$virtualServer->getOvzUuid(),
                'CONFIG'=>$virtualServerConfig
            );
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_modify_vs',$params,$pending);
            $this->virutalServerSettingsSave($job,$virtualServer);

            // success message
            $message = $this->translate("virtualserver_job_modifyvs");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }

        $this->redirectToTableSlideDataAction();
    }

    /**
    * change the configuration of a virtual server
    * 
    * @param mixed $virtualServersId
    */
    public function virtualServersConfigureAction($serverId){

        // sanitize
        $serverId = $this->filter->sanitize($serverId,"int");

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','changestate',array("item"=>$virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_list_info
            // no pending needed because job is readonly
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_list_info',$params);
            $this->virutalServerSettingsSave($job,$virtualServer);

            // get OVZ Settings
            $ovzSettings = json_decode($virtualServer->getOvzSettings(),true);

            // fill form fields
            $virtualServersConfigureFormFields = new VirtualServersConfigureFormFields();
            $virtualServersConfigureFormFields->virtual_servers_id = $virtualServer->getId();
            $virtualServersConfigureFormFields->dns = $ovzSettings['DNS Servers'];
            $virtualServersConfigureFormFields->cores = $virtualServer->getCore();
            $virtualServersConfigureFormFields->memory = $virtualServer->getMemory()." MB";
            $virtualServersConfigureFormFields->diskspace = Helpers::formatBytesHelper(Helpers::convertToBytes($virtualServer->getSpace()."MB"));
            if($ovzSettings['Autostart'] == 'on'){
                $virtualServersConfigureFormFields->startOnBoot = 1;
            }elseif($ovzSettings['Autostart'] == 'off') {
                $virtualServersConfigureFormFields->startOnBoot = 0;
            }

            // call view
            $this->view->form = new VirtualServersConfigureForm($virtualServersConfigureFormFields); 
            $this->view->pick("virtual_servers/virtualServersConfigureForm");
            return;

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->redirectTo("virtual_servers/slidedata");
        }
    }

    /**
    * execute the job and safe the configuration
    * 
    */
    public function virtualServersConfigureSendAction(){
        // POST request?
        if (!$this->request->isPost())
        return $this->redirectTo("virtual_servers/slidedata");

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($this->request->getPost("virtual_servers_id", "int"));
            $this->tryCheckPermission('virtual_servers','changestate',array("item"=>$virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_list_info
            // no pending needed because job is readonly
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_list_info',$params);
            $this->virutalServerSettingsSave($job,$virtualServer);

            // validate FORM
            $form = new VirtualServersConfigureForm();
            $data = $this->request->getPost();
            if (!$form->isValid($data, $form)) {
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/virtualServersConfigureForm");
                return; 
            }

            // business logic
            // dns
            $dns = '';
            if(!empty($form->dns)){
                $dnsIPs = explode(' ',$form->dns);
                // check if every IP is valid
                foreach($dnsIPs as $dnsIP){
                    if(!empty($dnsIP)){
                        if(!IpObjects::isValidIPv4($dnsIP)){
                            $message1 = $this->translate("virtualserver_IP_not_valid");
                            $message = $dnsIP.$message1;
                            $this->redirectErrorToVirtualServersConfigure($message,'dns',$form);
                        }else{
                            // create string with all DNS IPs
                            $dns .= $dnsIP.' ';
                        }
                    }
                }
            }

            // cores
            $core = intval($form->cores);

            if($core < 1){
                $message1 = $this->translate("virtualserver_min_core");
                $message = $message1;
                return $this->redirectErrorToVirtualServersConfigure($message,'cores',$form);
            }

            if($core > $virtualServer->PhysicalServers->getCore()){
                $message1 = $this->translate("virtualserver_max_core");
                $message = $message1.$virtualServer->PhysicalServers->getCore().')';
                return $this->redirectErrorToVirtualServersConfigure($message,'cores',$form);
            }

            // memory
            $memory = Helpers::convertToBytes($form->memory);

            // check if memory is numeric
            if(!is_numeric($memory)){
                $message1 = $this->translate("virtualserver_ram_numeric");
                $message = $message1;
                return $this->redirectErrorToVirtualServersConfigure($message,'memory',$form);
            }

            // chech if memory is minmum 512 MB
            if(gmp_cmp($memory,Helpers::convertToBytes('512MB'))<0){
                $message1 = $this->translate("virtualserver_min_ram");
                $message = $message1;
                return $this->redirectErrorToVirtualServersConfigure($message,'memory',$form);
            } 

            // check if memory of host is exceeded
            $hostRam = Helpers::convertToBytes($virtualServer->PhysicalServers->getMemory().'MB');
            if(gmp_cmp($memory,$hostRam)>0){
                $message1 = $this->translate("virtualserver_max_ram");
                $message = $message1.$virtualServer->PhysicalServers->getMemory().' MB)';
                return $this->redirectErrorToVirtualServersConfigure($message,'memory',$form);
            }

            // final memory in MibiBytes
            $memory = Helpers::convertBytesToMibiBytes($memory);

            // space
            $diskspace = Helpers::convertToBytes($form->diskspace);

            // check if diskpace is numeric
            if(!is_numeric($diskspace)){
                $message1 = $this->translate("virtualserver_space_numeric");
                $message = $message1;
                return $this->redirectErrorToVirtualServersConfigure($message,'diskspace',$form);
            }

            // check if diskspace is min
            if(gmp_cmp($diskspace,Helpers::convertToBytes('20GB'))<0){
                $message1 = $this->translate("virtualserver_min_space");
                $message = $message1;
                return $this->redirectErrorToVirtualServersConfigure($message,'diskspace',$form);
            }
            // check if diskspace of host is exceeded
            $hostDiskspace = Helpers::convertToBytes($virtualServer->PhysicalServers->getSpace().'GB');
            if(gmp_cmp($diskspace,$hostDiskspace)>0){
                $message1 = $this->translate("virtualserver_max_space");
                $message = $message1.$virtualServer->PhysicalServers->getSpace().' GB)';
                return $this->redirectErrorToVirtualServersConfigure($message,'diskspace',$form);
            }

            // final diskspace in MibiBytes
            $diskspace = Helpers::convertBytesToMibiBytes($diskspace);

            // execute ovz_modify_vs job        
            $virtualServerConfig = array(
                'nameserver' => $dns,
                'cpus' => $core,
                'memsize' => $memory,
                'diskspace' => $diskspace,
                'onboot' => ($form->startOnBoot)?'yes':'no',
            );
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array(
                'UUID'=>$virtualServer->getOvzUuid(),
                'CONFIG'=>$virtualServerConfig
            );
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_modify_vs',$params,$pending);
            $this->virutalServerSettingsSave($job,$virtualServer);

            // success
            $message = $this->translate("virtualserver_job_modifyvs");
            $this->flashSession->success($message);

            // go back to slidedata view
            $this->redirectTo("virtual_servers/slidedata");
            return;

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->redirectTo("virtual_servers/slidedata");
            return;
        }
    }

    private function redirectErrorToVirtualServersConfigure($message,$field,$form){
        $form->appendMessage(new \Phalcon\Validation\Message($message,$field));
        $this->view->form = $form; 
        $this->view->pick("virtual_servers/virtualServersConfigureForm");
        return; 
    }

    public static function virutalServerSettingsSave($job,$virtualServer){
        // save settings
        $settings = $job->getRetval(true);
        $virtualServer->setOvzSettings($job->getRetval());
        self::virtualServerSettingsAssign($virtualServer,$settings);

        if ($virtualServer->save() === false) {
            $messages = $virtualServer->getMessages();
            foreach ($messages as $message) {
                $this->flashSession->warning($message);
            }
            $message = self::translate("virtualserver_update_failed");
            throw new \Exception($message.$virtualServer->getName());
        }
    }

    /**
    * assign the ovz settings to its relevant value
    * 
    * @param VirtualServers $virtualServer
    * @param mixed $settings
    */
    public static function virtualServerSettingsAssign(\RNTForest\ovz\models\VirtualServers $virtualServer,$settings){
        $virtualServer->setName($settings['Name']);
        $virtualServer->setDescription($settings['Description']);
        $virtualServer->setOvz(1);
        $virtualServer->setOvzVstype($settings['Type']);
        $virtualServer->setCore(intval($settings['Hardware']['cpu']['cpus']));
        $virtualServer->setMemory(intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['Hardware']['memory']['size'])/1024/1024));
        $virtualServer->setSpace(intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['Hardware']['hdd0']['size'])/1024/1024));
    }

    public function ovzReplicaActivateAction($virtualServersId){

        // sanitize Parameters
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");

        try{
            // validate (throws exceptions)
            $virtualServer = VirtualServers::tryFindById($virtualServersId);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // check last info Update..
            $this->tryOvzListInfo($virtualServer);

            // prepare form fields
            $replicaFormFields = new ReplicaActivateFormFields();
            $replicaFormFields->virtual_servers_id = $virtualServersId;

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }

        // call view
        $this->view->form = new ReplicaActivateForm($replicaFormFields);
        $this->view->pick("virtual_servers/replicaActivateForm");

    }

    public function ovzReplicaActivateExecuteAction(){
        // POST request?
        if (!$this->request->isPost()) 
            return $this->redirectTo("virtual_servers/slidedata");

        // validate FORM
        $form = new ReplicaActivateForm();
        $fields = new ReplicaActivateFormFields();
        $data = $this->request->getPost();
        if (!$form->isValid($data, $fields)) {
            // call view
            $this->view->form = $form;
            $this->view->pick("virtual_servers/replicaActivateForm");
            return;
        }

        try {        
            // validate (throws exceptions)
            $replicaMaster = VirtualServers::tryFindById($fields->virtual_servers_id);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $replicaMaster));
            $this->tryCheckOvzEnabled($replicaMaster);

            $replicaSlaveHost = PhysicalServers::tryFindById($fields->physical_servers_id);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $replicaSlaveHost));
            $this->tryCheckOvzEnabled($replicaSlaveHost);

        } catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }

        try {
            $replicaMasterSettings = json_decode($replicaMaster->getOvzSettings(),true);
            $replicaMasterHost = $replicaMaster->physicalServers;

            $replicaSlave = new VirtualServers;
            $replicaSlave->assign($replicaMaster->toArray());
            $replicaSlave->setId(NULL);
            $replicaSlave->setOvzUuid(\RNTForest\core\libraries\Helpers::genUuid());
            $replicaSlave->setName(substr("replica of ".$replicaMaster->getName(),0,50));
            $replicaSlave->setPhysicalServersId($replicaSlaveHost->getId());
            $replicaSlave->setOvzReplica(2);
            $replicaSlave->setOvzReplicaId($replicaMaster->getId());
            $replicaSlave->setOvzReplicaHost($replicaMasterHost->getId());
            $replicaSlave->setOvzReplicaStatus(3);

            if ($replicaSlave->create() === false){
                $allMessages = $this->translate("virtualserver_save_replica_slave_failed");
                foreach ($replicaSlave->getMessages() as $message) {
                    $allMessages .= $message->getMessage();
                }
                throw new \Exception($allMessages);
            }
            $replicaSlave->refresh();

            $params = array(
                "VSTYPE"=>$replicaSlave->getOvzVstype(),
                "UUID"=>$replicaSlave->getOvzUuid(),
                "NAME"=>$replicaSlave->getName(),
                "OSTEMPLATE"=>$this->config->replica->osTemplate,
                "DISTRIBUTION"=>"",
                "HOSTNAME"=>$replicaSlave->getFqdn(),
                "CPUS"=>$replicaSlave->getCore(),
                "RAM"=>$replicaSlave->getMemory(),
                "DISKSPACE"=>$replicaSlave->getSpace(),
                "ROOTPWD"=>"WillBeOverittenByInitalSync",
            );

            // execute ovz_new_vs job        
            // no pending needed, replica slave should be invisible
            $push = $this->getPushService();
            $job = $push->executeJob($replicaSlaveHost,'ovz_new_vs',$params);
            if($job->getDone() == 2){
                $message = $this->translate("virtualserver_job_create_failed");
                throw new \Exception($message.$job->getError());
            }
            $job1Id = $job->getId();

            // slave should not automatic boot
            $params = array(
                "UUID"=>$replicaSlave->getOvzUuid(),
                "CONFIG"=>array("onboot"=>"no"),
            );
            $job = $push->queueDependentJob($replicaSlaveHost,'ovz_modify_vs',$params,$job1Id);            
            if($job->getDone() == 2){
                $message = $this->translate("virtualserver_job_modify_failed");
                throw new \Exception($message.$job->getError());
            }
            $job2Id = $job->getId();

            // initial Sync
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            // callback to update virtualserver
            $pending = array(
                'model' => '\RNTForest\ovz\models\VirtualServers',
                'id' => $replicaMaster->getId(),
                'element' => 'replica',
                'severity' => 1,
                'params' => array(),
                'callback' => '\RNTForest\ovz\functions\Pending::updateAfterReplicaRun'
            );
            $params = array(
                "UUID"=>$replicaMaster->getOvzUuid(),
                "SLAVEHOSTFQDN"=>$replicaSlaveHost->getFqdn(),
                "SLAVEUUID"=>$replicaSlave->getOvzUuid(),
            );
            $job = $push->queueDependentJob($replicaMasterHost,'ovz_sync_replica',$params,$job2Id,$pending);            
            if($job->getDone() == 2){
                $message = $this->translate("virtualserver_job_sync_replica_failed");
                throw new \Exception($message.$job->getError());
            }

            // update replica master
            $replicaMaster->setOvzReplica(1);
            $replicaMaster->setOvzReplicaId($replicaSlave->getId());
            $replicaMaster->setOvzReplicaHost($replicaSlaveHost->getId());
            $replicaMaster->setOvzReplicaCron("");
            $replicaMaster->setOvzReplicaLastrun("");
            $replicaMaster->setOvzReplicaNextrun("");
            $replicaMaster->setOvzReplicaStatus(3);
            if ($replicaMaster->update() == false){
                $allMessages = $this->translate("virtualserver_update_replica_master_failed");
                foreach ($replicaSlave->getMessages() as $message) {
                    $allMessages .= $message->getMessage();
                }
                throw new \Exception($allMessages);
            }

            $message = self::translate("virtualserver_replica_sync_run_in_background");
            $this->flashSession->success($message);

        } catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirectToTableSlideDataAction();        
    }

    /**
    * dummy method only for auto completion purpose
    * 
    * @return \RNTForest\ovz\services\Replica
    */
    protected function getReplicaService(){
        return $this->di['replica'];
    }
    
    public function ovzReplicaRunAction($replicaMasterId) {

        // sanitize Parameters
        $replicaMasterId = $this->filter->sanitize($replicaMasterId,"int");

        try{
            // validate (throws exceptions)
            $replicaMaster = VirtualServers::tryFindById($replicaMasterId);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $replicaMaster));
            $this->tryCheckOvzEnabled($replicaMaster);

            // run replica
            $replica = $this->getReplicaService();
            $replica->run($replicaMaster);
            $this->flashSession->success('virtualserver_replica_running_in_background');

        } catch (\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        
        $this->redirectToTableSlideDataAction();        
    }

    public function ovzReplicaFailoverAction($replicaMasterId) {

        // sanitize Parameters
        $replicaMasterId = $this->filter->sanitize($replicaMasterId,"int");

        try{
            // validate (throws exceptions)
            $replicaMaster = VirtualServers::tryFindById($replicaMasterId);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $replicaMaster));
            $this->tryCheckOvzEnabled($replicaMaster);
            
            if($replicaMaster->getOvzReplica() != 1) throw new \Exception("virtualserver_isnot_replica_master");
            
            // shutdown master
            $this->tryOvzListInfo($replicaMaster);
            if($replicaMaster->getOvzState() == 'running') $this->tryStopVS($replicaMaster);
    
            // check if master and slave is stopped            
            $this->tryOvzListInfo($replicaMaster);
            $replicaMasterState = $replicaMaster->getOvzState();
            if($replicaMasterState != 'stopped'){
                throw new \Exception("virtualserver_replica_master_not_stopped");
            }
    
            $replicaSlave = new VirtualServers;
            $replicaSlave = $replicaMaster->ovzReplicaId;
            $this->tryOvzListInfo($replicaSlave);
            if($replicaSlave->getOvzState() != 'stopped'){
                throw new \Exception("virtualserver_replica_slave_not_stopped");
            }

            // run replica, don't go on until job ist fineshed
            $replica = $this->getReplicaService();
            if($job = $replica->run($replicaMaster)){
                $push = $this->getPushService();
                while($job->getDone()<1){
                    sleep(5);
                    $push->pushJobs();
                    $job->refresh();
                }
            }
            
            // Todo: update actual config (incl dcoip)

            // turn around master and slave
            $replicaMaster->refresh();
            $replicaMaster->setOvzReplica(2);
            $masterName = $replicaMaster->getName();
            $replicaMaster->setName($replicaSlave->getName());
            $replicaMaster->update();

            $replicaSlave->refresh();
            $replicaSlave->setOvzReplica(1);
            $replicaSlave->setName($masterName);
            $replicaSlave->update();
            
            if($replicaMasterState == 'running'){
                $this->tryStartVS($replicaSlave);
            }

            // update settings
            $this->tryOvzListInfo($replicaSlave);
    
            $this->flashSession->success('virtualserver_replica_failover_success');
    
        } catch (\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        
        $this->redirectToTableSlideDataAction();        
    }

    public function ovzReplicaDeleteAction($masterId) {

        // sanitize Parameters
        $masterId = $this->filter->sanitize($masterId,"int");
        
        try{
            // validate (throws exceptions)
            $replicaMaster = VirtualServers::tryFindById($masterId);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $replicaMaster));
            $this->tryCheckOvzEnabled($replicaMaster);

            if($replicaMaster->getOvzReplica() != 1){
                $message = $this->translate("virtualserver_server_not_replica_master");
                throw new \Exception($message);
            }

            $replicaSlave = VirtualServers::tryFindById($replicaMaster->getOvzReplicaId());
            if($replicaSlave->getOvzReplica() != 2){
                $message = $this->translate("virtualserver_server_not_replica_slave");
                throw new \Exception($message);
            }

            // switch off replica            
            $replicaMaster->setOvzReplica(0);
            $replicaMaster->setOvzReplicaId(0);
            $replicaMaster->setOvzReplicaHost(0);
            $replicaMaster->setOvzReplicaCron('');
            $replicaMaster->setOvzReplicaLastrun("0000-00-00 00:00:00");
            $replicaMaster->setOvzReplicaNextrun("0000-00-00 00:00:00");
            $replicaMaster->setOvzReplicaStatus(0);
            if(!$replicaMaster->update()){
                $message = $this->translate("virtualserver_replica_master_update_failed");
                throw new \Exception($message);
            }

            $replicaSlave->setOvzReplica(0);
            $replicaSlave->setOvzReplicaId(0);
            $replicaSlave->setOvzReplicaHost(0);
            $replicaSlave->setOvzReplicaCron('');
            $replicaSlave->setOvzReplicaLastrun("0000-00-00 00:00:00");
            $replicaSlave->setOvzReplicaNextrun("0000-00-00 00:00:00");
            $replicaSlave->setOvzReplicaStatus(0);
            if(!$replicaSlave->update()){
                $message = $this->translate("virtualserver_replica_slave_update_failed");
                throw new \Exception($message);
            }

            $message = self::translate("virtualserver_replica_switched_off");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
        $this->forwardToTableSlideDataAction();
    }

    /**
    * Show rootPasswordChange form
    * 
    * @param mixed $virtualServersId
    */
    public function rootPasswordChangeAction($virtualServersId) {

        // sanitize Parameters
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");

        try{
            // validate (throws exceptions)
            $virtualServer = VirtualServers::tryFindById($virtualServersId);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // check last info Update..
            $this->tryOvzListInfo($virtualServer);

            // prepare form fields
            $rootPasswordChangeFormFields = new RootPasswordChangeFormFields();
            $rootPasswordChangeFormFields->virtual_servers_id = $virtualServersId;

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }

        // call view
        $this->view->form = new RootPasswordChangeForm($rootPasswordChangeFormFields);
        $this->view->pick("virtual_servers/rootPasswordChangeForm");
    }
    
    /**
    * Change root password
    * 
    */
    public function rootPasswordChangeExecuteAction() {

        // POST request?
        if (!$this->request->isPost()) 
            return $this->redirectTo("virtual_servers/slidedata");

        // validate FORM
        $form = new RootPasswordChangeForm;
        $item = new RootPasswordChangeFormFields();
        $data = $this->request->getPost();
        if (!$form->isValid($data, $item)) {
            $this->view->form = $form; 
            $this->view->pick("virtual_servers/rootPasswordChangeForm");
            return; 
        }

        try {    
            // validate
            $virtualServer = VirtualServers::tryFindById($item->virtual_servers_id);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_set_pwd job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage
            $pending = '\RNTForest\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array(
                'UUID'=>$virtualServer->getOvzUuid(),
                'ROOTPWD'=>$data['password']
            );
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_create_snapshot',$params,$pending);

            // success message
            $message = $this->translate("virtualserver_change_root_password_successful");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirectToTableSlideDataAction();
    }
    
    /**
    * Show mon local jobs form
    * 
    * @param mixed $physicalServersId
    */
    public function monLocalJobAddAction($virtualServersId){
        // sanitize
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");

        // get virtual server object
        $virtualServer = VirtualServers::findFirstByid($virtualServersId);
        if (!$virtualServer) {
            $message = $this->translate("virtualserver_does_not_exist");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }
        
        // check permissions
        if(!$this->permissions->checkPermission('virtual_servers', 'mon_jobs', array('item' => $virtualServer))){
            return $this->forwardTo401();
        }
        
        $monLocalJob = new MonLocalJobs();
        $monLocalJob->setServerId($virtualServersId);
        $monLocalJob->setServerClass('\RNTForest\ovz\models\VirtualServers');
        
        // call view
        $this->view->form = new MonLocalJobsForm($monLocalJob); 
        $this->view->pick("virtual_servers/monLocalJobsForm");
    }
    
    /**
    * Add new Local MonJob
    * 
    */
    public function monLocalJobAddExecuteAction(){
        try{
            // POST request?
            if (!$this->request->isPost()) 
                return $this->redirectTo("virtual_servers/slidedata");

            // validate FORM
            $data = $this->request->getPost();
            $virtualServersId = $this->filter->sanitize($data['server_id'],"int");
            $monJob = new MonLocalJobs();
            $monJob->setServerId($virtualServersId);
            $monJob->setServerClass('\RNTForest\ovz\models\VirtualServers');
            $form = new MonLocalJobsForm($monJob);
            if (!$form->isValid($data, $monJob)) {
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/monLocalJobsForm");
                return; 
            }
            
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServersId);
            $this->tryCheckPermission('virtual_servers', 'mon_jobs', array('item' => $virtualServer));
            
            // business logic
            foreach($monJob->getMonContactsMessage() as $monContactMessageId){
                // throws Exception if login doesn't exist
                $login = Logins::tryFindById($monContactMessageId);
                // check if login has the same customer as the virtual server
                if($login->getCustomersId() != $virtualServer->getCustomersId()){
                    throw new \Exception(self::translate("monitoring_monjobs_login_not_from_customer"));
                }
            }
            foreach($monJob->getMonContactsAlarm() as $monContactAlarmId){
                // throws Exception if login doesn't exist
                $login = Logins::tryFindById($monContactAlarmId);
                // check if login has the same customer as the virtual server
                if($login->getCustomersId() != $virtualServer->getCustomersId()){
                    throw new \Exception(self::translate("monitoring_monjobs_login_not_from_customer"));
                }
            }
            
            // add local mon job
            $behavior = $monJob->getMonBehaviorClass();
            $period = $monJob->getPeriod();
            $alarmPeriod = $monJob->getAlarmPeriod();
            $messageContacts = $monJob->getMonContactsMessage();
            $alarmContacts = $monJob->getMonContactsAlarm();
            $virtualServer->addMonLocalJob($behavior,$period,$alarmPeriod,$messageContacts,$alarmContacts);
            
            // clean up
            $form->clear();
            $message = $this->translate("monitoring_monlocaljobs_add_successful");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $message = $this->translate("monitoring_monlocaljobs_add_failed");
            $this->flashSession->error($message.$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirectToTableSlideDataAction();
    }
    
    /**
    * Show mon remote jobs form
    * 
    * @param mixed $virtualServersId
    */
    public function monRemoteJobAddAction($virtualServersId){
        // sanitize
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");

        // get virtual server object
        $virtualServer = VirtualServers::findFirstByid($virtualServersId);
        if (!$virtualServer) {
            $message = $this->translate("virtualserver_does_not_exist");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }
        
        // check permissions
        if(!$this->permissions->checkPermission('virtual_servers', 'mon_jobs', array('item' => $virtualServer))){
            return $this->forwardTo401();
        }
        
        $monRemoteJob = new MonRemoteJobs();
        $monRemoteJob->setServerId($virtualServersId);
        $monRemoteJob->setServerClass('\RNTForest\ovz\models\VirtualServers');
        
        // call view
        $this->view->form = new MonRemoteJobsForm($monRemoteJob);
        $this->view->pick("virtual_servers/monRemoteJobsForm");
    }
    
    /**
    * Add new Remote MonJob
    * 
    */
    public function monRemoteJobAddExecuteAction(){
        try{
            // POST request?
            if (!$this->request->isPost()) 
                return $this->redirectTo("virtual_servers/slidedata");
                
            // validate FORM
            $data = $this->request->getPost();
            $virtualServersId = $this->filter->sanitize($data['server_id'],"int");
            $monJob = new MonRemoteJobs();
            $monJob->setServerId($virtualServersId);
            $monJob->setServerClass('\RNTForest\ovz\models\VirtualServers');
            $form = new MonRemoteJobsForm($monJob);
            if (!$form->isValid($data, $monJob)) {
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/monRemoteJobsForm");
                return; 
            }
            
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServersId);
            $this->tryCheckPermission('virtual_servers', 'mon_jobs', array('item' => $virtualServer));
            
            // business logic
            foreach($monJob->getMonContactsMessage() as $monContactMessageId){
                // throws Exception if login doesn't exist
                $login = Logins::tryFindById($monContactMessageId);
                // check if login has the same customer as the virtual server
                if($login->getCustomersId() != $virtualServer->getCustomersId()){
                    throw new \Exception(self::translate("monitoring_monjobs_login_not_from_customer"));
                }
            }
            foreach($monJob->getMonContactsAlarm() as $monContactAlarmId){
                // throws Exception if login doesn't exist
                $login = Logins::tryFindById($monContactAlarmId);
                // check if login has the same customer as the virtual server
                if($login->getCustomersId() != $virtualServer->getCustomersId()){
                    throw new \Exception(self::translate("monitoring_monjobs_login_not_from_customer"));
                }
            }
            
            // add remote mon job
            $behavior = $monJob->getMonBehaviorClass();
            $period = $monJob->getPeriod();
            $alarmPeriod = $monJob->getAlarmPeriod();
            $messageContacts = $monJob->getMonContactsMessage();
            $alarmContacts = $monJob->getMonContactsAlarm();
            $virtualServer->addMonRemoteJob($behavior,$period,$alarmPeriod,$messageContacts,$alarmContacts);
            
            // clean up
            $form->clear();
            $message = $this->translate("monitoring_monremotejobs_add_successful");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $message = $this->translate("monitoring_monremotejobs_add_failed");
            $this->flashSession->error($message.$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirectToTableSlideDataAction();
    }
}

/**
* helper classes
*/
class SnapshotFormFields{
    public $virtual_servers_id = 0;
    public $name = "";
    public $description = "";
}

class ReplicaActivateFormFields{
    public $virtual_servers_id = 0;
    public $physical_servers_id = 0;
}

class VirtualServersConfigureFormFields{
    public $virtual_servers_id = 0;
    public $dns = "";
    public $cores = 0;
    public $memory = "";
    public $diskspace = "";
    public $startOnBoot = 0;
    public $description = "";
}

class RootPasswordChangeFormFields{
    public $password = "";
}
