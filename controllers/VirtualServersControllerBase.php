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
use RNTForest\ovz\models\Dcoipobjects;
use RNTForest\core\models\Customers;
use RNTForest\ovz\forms\VirtualServersForm;
use RNTForest\ovz\forms\ConfigureVirtualServersForm;
use RNTForest\ovz\forms\ModifyVirtualServersForm;
use RNTForest\ovz\forms\DcoipobjectsForm;
use RNTForest\ovz\forms\SnapshotForm;
use RNTForest\ovz\forms\ReplicaActivateForm;
use RNTForest\ovz\forms\ChangeRootPasswordForm;

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
        $this->simpleview->snapshots = $this->renderSnapshotList($item);
        $content .= $this->simpleview->render("partials/ovz/virtual_servers/slideDetail.volt");
        return $content;
    }

    /**
    * check if Virtual or Physical Server is OVZ enabled. Otherwise it throws an exception
    * 
    * @param server $server
    * @throws Exceptions
    */
    protected function tryCheckOvzEnabled($server) {
        // check if server is ovz enabled   
        if($server->getOvz() == 0){
            $message = $this->translate("virtualserver_server_not_ovz_enabled");
            throw new \Exception($message);
        }
    }

    /**
    * updates OVZ settings
    * 
    * @param int $serverId
    */
    public function ovzListInfoAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_list_info job 
            $this->tryOvzListInfo($virtualServer);

            // success
            $message = $this->translate("virtualserver_settings_success");
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
        
        // todo: check if update nis realy needed
        
        // no pending needed because job is readonly     
        $push = $this->getPushService();
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_list_info',$params);
        $message = $this->translate("virtualserver_job_infolist_failed");
        if($job->getDone()==2) throw new \Exception($message.$job->getError());

        // save settings to virtual server
        $this->saveVirutalServerSettings($job,$virtualServer);

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
        $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
        $push = $this->getPushService();
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_start_vs',$params,$pending);
        $message = $this->translate("virtualserver_job_start_failed");
        if($job->getDone()==2) throw new \Exception($message.$job->getError());
        
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
        $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
        $push = $this->getPushService();
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_stop_vs',$params,$pending);
        $message = $this->translate("virtualserver_job_stop_failed");
        if($job->getDone()==2) throw new \Exception($message.$job->getError());
        
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
        $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
        $push = $this->getPushService();
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_restart_vs',$params,$pending);
        $message = $this->translate("virtualserver_job_restart_failed");
        if($job->getDone()==2) throw new \Exception($message.$job->getError());
        
        return $job;
    }
        
    
    /**
    * updates OVZ statistics
    * 
    * @param int $serverId
    */
    public function ovzStatisticsInfoAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $serverId);

            // not ovz enalbled
            if(!$virtualServer->getOvz()) throw new ErrorException("Server ist not OVZ enabled!");

            // execute ovz_statistics_info job 
            // no pending needed because job is readonly     
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_statistics_info',$params);
            if($job->getDone()==2) throw new \Exception("Job (ovz_statistics_info) executions failed: ".$job->getError());

            // save statistics
            $statistics = $job->getRetval(true);
            $virtualServer->setOvzStatistics($job->getRetval());

            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                throw new \Exception("Update Virtual Server (".$virtualServer->getName().") failed.");
            }

            // success
            $this->flashSession->success("Statistics successfully updated");

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
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
            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            $message = $this->translate("virtualserver_does_not_exist");  
            if (!$virtualServer) throw new \Exception($message . $serverId);

            // permissions for this virtual server
            if (!$this->isAllowedItem($virtualServer,"changestate")) return $this->forwardTo401();

            // not ovz enalbled
            $message = $this->translate("virtualserver_not_ovz_enabled");
            if(!$virtualServer->getOvz()) throw new \Exception($message);

            // execute ovz_start_vs job 
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_start_vs',$params,$pending);
            $message = $this->translate("virtualserver_job_start_failed");
            if($job->getDone()==2) throw new \Exception($message.$job->getError());

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
            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            $message = $this->translate("virtualserver_does_not_exist");
            if (!$virtualServer) throw new \Exception($message . $serverId);

            // permissions for this virtual server
            if (!$this->isAllowedItem($virtualServer,"changestate")) return $this->forwardTo401();

            // not ovz enalbled
            $message = $this->translate("virtualserver_not_ovz_enabled");
            if(!$virtualServer->getOvz()) throw new \Exception($message);

            // execute ovz_stop_vs job        
            $this->tryStopVS($virtualServer);

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
            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            $message = $this->translate("virtualserver_does_not_exist");
            if (!$virtualServer) throw new \Exception($message . $serverId);

            // permissions for this virtual server
            if (!$this->isAllowedItem($virtualServer,"changestate")) return $this->forwardTo401();

            // not ovz enalbled
            $message = $this->translate("virtualserver_not_ovz_enabled");
            if(!$virtualServer->getOvz()) throw new \Exception($message);

            // execute ovz_restart_vs job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_restart_vs',$params,$pending);
            $message = $this->translate("virtualserver_job_restart_failed");
            if($job->getDone()==2) throw new \Exception($message.$job->getError());

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
    public function deleteAction($id){

        // find server
        $virtualServer = VirtualServers::findFirst(intval($id));
        if(!$virtualServer){
            $message = $this->translate("virtualserver_not_found");
            $this->flashSession->error($message);
            return $this->redirecToTableSlideDataAction();
        }

        // execute ovz_destroy_vs job   
        if($virtualServer->getOvz()){     
            // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId();
            $push = $this->getPushService();
            $params = array("UUID"=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->physicalServers,'ovz_destroy_vs',$params,$pending);
            if($job->getDone() == 2){
                $message = $this->translate("virtualserver_job_destroy_failed");
                $this->flashSession->error($message.$job->getError());
                return $this->redirecToTableSlideDataAction();
            }elseif(!empty($job->getRetval())){
                $this->flashSession->warning($job->getRetval());
            }
        }

        // delete IP Objects
        foreach($virtualServer->dcoipobjects as $dcoipobject){
            if(!$dcoipobject->delete()){
                foreach ($dcoipobject->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                return $this->redirecToTableSlideDataAction();
            }
        }

        // delete DB entry
        if (!$virtualServer->delete()) {
            foreach ($virtualServer->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->redirecToTableSlideDataAction();
        }
        $message = $this->translate("virtualserver_job_destroy");
        $this->flashSession->success($message);

        // redirect
        return $this->redirecToTableSlideDataAction();
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

        // no pending needed because job is readonly
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
    protected function preSave($virtualServer,$form){

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

            // get PhysicalServer
            $physicalServer = PhysicalServers::findFirst($form->getValue('physical_servers_id'));
            if (!$physicalServer) {
                $message = $this->translate("physicalserver_does_not_exist");
                $this->flashSession->error($message . $serverId);
                return false;
            }

            // permissions for this PhysicalServer
            if (!$this->isAllowedItem($physicalServer)){
                $message = $this->translate("physicalserver_permission");
                $this->flashSession->error($message);
                return false;
            }


            if (!$physicalServer->getOvz()) {
                $message = $this->translate("physicalserver_not_ovz_integrated");
                $this->flashSession->error($message);
                return false;
            }

            // execute ovz_new_vs job        
            // no pending needed because virtualserver does not yet exist in DB
            $push = $this->getPushService();
            $job = $push->executeJob($physicalServer,'ovz_new_vs',$params);
            if($job->getDone() == 2){
                $message = $this->translate("physicalserver_job_create_failed");
                $this->flashSession->error($message.$job->getError());
                return false;
            }
        }
        return true; 
    }

    /**
    * cleans up
    * 
    * @param VirtualServers $virtualServer
    * @param VirtualServersForm $form
    */
    protected function postSave($virtualServer,$form){
        $session = $this->session->remove($this->getFormClassName());
        return true;
    }

    /*
    * List snapshots
    * 
    * @param int $serverId
    */
    public function ovzListSnapshotsAction($serverId){
        // get Snapshots
        try{
            // sanitize parameters
            $serverId = $this->filter->sanitize($serverId, "int");

            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            $message = $this->translate("virtualserver_does_not_exist");
            if (!$virtualServer) throw new Exception($message . $serverId);

            // permissions
            if (!$this->isAllowedItem($virtualServer,"snapshots")) return $this->forwardTo401();

            // not ovz enalbled
            $message = $this->translate("virtualserver_not_ovz_enabled");
            if(!$virtualServer->getOvz()) throw new ErrorException($message);

            // execute ovz_list_snapshots job 
            // no pending needed because job is readonly       
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_list_snapshots',$params);
            $message = $this->translate("virtualserver_job_listsnapshots_failed");
            if(!$job || $job->getDone()==2) throw new Exception($message);

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                $message = $this->translate("virtualserver_update_failed");
                throw new Exception($message.$virtualServer->getName());
            }

            // success
            $message = $this->translate("virtualserver_snapshot_update");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * renders all snapshots to one server
    * 
    * @param mixed $item
    */
    private function renderSnapshotList($item){
        // convert the json to an array
        $snapshots = json_decode($item->ovz_snapshots, true);
        if(!is_array($snapshots)) $snapshots=array();

        // sort all the snapshots
        $snapshots = $this->getSnapshotsChilds("",$snapshots);

        return $snapshots;
    }

    /**
    * sorts the array depending of the parent
    * 
    * @param string $parent UUID of the parent snapshot
    * @param array $snapshots array with the snapshots in it
    */
    private function getSnapshotsChilds($parent,$snapshots){ 
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
                $snapshot['Childs'] = $this->getSnapshotsChilds($snapshot['UUID'],$snapshots);

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
    * switch to an snapshot
    * 
    * @param mixed $snapshotId
    * @param int $serverId
    */
    public function ovzSwitchSnapshotAction($snapshotId,$serverId) {
        // switch to snapshot
        try {    
            // sanitize parameters
            $serverId = $this->filter->sanitize($serverId, "int");
            $snapshotId = $this->filter->sanitize($snapshotId, "string");

            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            $message = $this->translate("virtualserver_does_not_exist");
            if (!$virtualServer) throw new \Exception($message . $serverId);

            // permissions
            if (!$this->isAllowedItem($virtualServer,"snapshots")) return $this->forwardTo401();

            // not ovz enalbled
            $message = $this->translate("virtualserver_not_ovz_enabled");
            if(!$virtualServer->getOvz()) throw new ErrorException($message);

            // execute ovz_switch_snapshot job
            // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId();
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'SNAPSHOTID'=>$snapshotId);
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_switch_snapshot',$params);
            $message = $this->translate("virtualserver_job_switchsnapshotexec_failed");
            if(!$job || $job->getDone()==2) throw new \Exception($message);

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                $message = $this->translate("virtualserver_job_switchsnapshot_failed");
                throw new \Exception($message.$virtualServer->getName());
            }

            // success
            $message = $this->translate("virtualserver_snapshot_update");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    public function snapshotFormAction($item){

        if(!is_a($item,'SnapshotForm')){
            $snapshotFormFields = new SnapshotFormFields();
            $snapshotFormFields->virtual_servers_id = intval($item);
            $item = new SnapshotForm($snapshotFormFields);
        }

        // permissions
        if (!$this->isAllowedItem($virtualServer,"snapshots")) return $this->forwardTo401();

        $this->view->form = $item;
    }


    /**
    * create a new snapshot
    * 
    * @param int $serverId
    * @param string $name
    * @param string $description
    */
    public function ovzCreateSnapshotAction() {
        // POST request?
        if (!$this->request->isPost()) 
            return $this->redirectTo("virtual_servers/slidedata");

        // validate FORM
        $form = new SnapshotForm();
        $item = new SnapshotFormFields();
        $data = $this->request->getPost();
        if (!$form->isValid($data, $item)) {
            return $this->dispatcher->forward([
                'action' => 'snapshotForm',
                'params' => [$form],
            ]);
        }

        // switch to snapshot
        try {    
            // find virtual server
            $virtualServer = VirtualServers::findFirst($item->virtual_servers_id);
            $message = $this->translate("virtualserver_does_not_exist");
            if (!$virtualServer) throw new \Exception($message . $item->virtual_servers_id);

            // permissions
            if (!$this->isAllowedItem($virtualServer,"snapshots")) return $this->forwardTo401();

            // execute ovz_list_snapshots job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'NAME'=>$item->name,'DESCRIPTION'=>$item->description);
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_create_snapshot',$params);
            $message = $this->translate("virtualserver_job_createsnapshotexec_failed");  
            if(!$job || $job->getDone()==2) throw new \Exception($message);

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                $message = $this->translate("virtualserver_job_createsnapshot_failed");
                throw new \Exception($message.$virtualServer->getName());
            }

            // success
            $message = $this->translate("virtualserver_snapshot_update");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
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
    public function ovzDeleteSnapshotAction($snapshotId,$serverId) {
        // switch to snapshot
        try {    
            // sanitize parameters
            $serverId = $this->filter->sanitize($serverId, "int");
            $snapshotId = $this->filter->sanitize($snapshotId, "string");

            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            $message = $this->translate("virtualserver_does_not_exist");
            if (!$virtualServer) throw new \Exception($message . $serverId);

            // permissions
            if (!$this->isAllowedItem($virtualServer,"snapshots")) return $this->forwardTo401();

            // not ovz enabled
            $message = $this->translate("virtualserver_not_ovz_enabled");
            if(!$virtualServer->getOvz()) throw new ErrorException($message);

            // execute ovz_delete_snapshot job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'SNAPSHOTID'=>$snapshotId);
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_delete_snapshot',$params);
            $message = $this->translate("virtualserver_job_deletesnapshotexec_failed");
            if(!$job || $job->getDone()==2) throw new \Exception($message);

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                $message = $this->translate("virtualserver_job_createsnapshot_failed");
                throw new \Exception($message.$virtualServer->getName());
            }

            // success
            $message = $this->translate("virtualserver_snapshot_update");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    /**
    * Adds an IP Object to the Server
    * 
    * @param integer $id primary key of the virtual Server
    * 
    */
    public function addIpObjectAction($id){

        // store in session
        $this->session->set("DcoipobjectsForm", array(
            "op" => "new",
            "virtual_servers_id" => intval($id),
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        $dcoipobjectsForm = new DcoipobjectsForm(new Dcoipobjects());

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'dcoipobjects',
            'action' => 'edit',
            'params' => [$dcoipobjectsForm],
        ]);
    }

    /**
    * Edits an IP Object to the Server
    * 
    * @param integer $ipobject primary key of the IP Object
    * 
    */
    public function editIpObjectAction($ipobject){

        // store in session
        $this->session->set("DcoipobjectsForm", array(
            "op" => "edit",
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'dcoipobjects',
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
    public function deleteIpObjectAction($ipobject){

        // store in session
        $this->session->set("DcoipobjectsForm", array(
            "op" => "delete",
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'dcoipobjects',
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
    public function makeMainIpObjectAction($ipobject){
        // store in session
        $this->session->set("DcoipobjectsForm", array(
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'dcoipobjects',
            'action' => 'makeMain',
            'params' => [$ipobject],
        ]);
    }

    /**
    * show modify form
    * 
    * @param mixed $virtualServersId
    */
    public function modifyVirtualServerAction($virtualServersId){
        // sanitize
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");

        // get physical server object
        $virtualServer = VirtualServers::findFirstByid($virtualServersId);
        if (!$virtualServer) {
            $message = $this->translate("virtualserver_does_not_exist");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }

        // check if server is ovz enabled
        if($virtualServer->getOvz() != 1){
            $message = $this->translate("virtualserver_server_not_ovz_enabled");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }

        // check permissions
        if(!$this->permissions->checkPermission('virtual_servers', 'modify', array('item' => $virtualServer))){
            return $this->forwardTo401();
        }   

        // call view
        $this->view->form = new ModifyVirtualServersForm($virtualServer); 
        $this->view->pick("virtual_servers/modifyVirtualServersForm");
    }

    /**
    * modify server
    * 
    */
    public function modifyVirtualServerExecuteAction(){
        try {
            // POST request?
            if (!$this->request->isPost()) 
                return $this->redirectTo("virtual_servers/slidedata");

            // sanitize
            $virtualServersId = $this->filter->sanitize($this->request->getPost("id"),"int");

            // get virtual server
            $virtualServer = VirtualServers::findFirstByid($virtualServersId);
            if (!$virtualServer){
                $message = $this->translate("virtualserver_does_not_exist");
                $this->flashSession->error($message);
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/modifyVirtualServersForm");
                return;
            }

            // check if server is ovz enabled
            if($virtualServer->getOvz() != 1){
                $message = $this->translate("virtualserver_server_not_ovz_enabled");
                $this->flashSession->error($message);
                return $this->forwardToTableSlideDataAction();
            }   

            // check permissions
            if(!$this->permissions->checkPermission('virtual_servers', 'modify', array('item' => $virtualServer))){
                return $this->forwardTo401();
            }

            // validate FORM
            $form = new ModifyVirtualServersForm;
            $data = $this->request->getPost();
            if (!$form->isValid($data, $virtualServer)) {
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/modifyVirtualServersForm");
                return; 
            }

            // update virutal server
            if ($virtualServer->update() === false) {
                // fetch all messages from model
                foreach ($virtualServer->getMessages() as $message) {
                    $form->appendMessage(new \Phalcon\Validation\Message($message->getMessage(),$message->getField()));
                }
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/modifyVirtualServersForm");
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
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $push = $this->getPushService();
            $params = array(
                'UUID'=>$virtualServer->getOvzUuid(),
                'CONFIG'=>$virtualServerConfig
            );
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_modify_vs',$params,$pending);
            $message = $this->translate("virtualserver_modify_job_failed");
            if($job->getDone()==2) throw new \Exception($message.$job->getError());
            $this->saveVirutalServerSettings($job,$virtualServer);

            // success message
            $message = $this->translate("virtualserver_job_modifyvs");
            $this->flashSession->success($message);
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }

        $this->redirecToTableSlideDataAction();
    }

    /**
    * change the configuration of a virtual server
    * 
    * @param mixed $virtualServersId
    */
    public function configureVirtualServersAction($virtualServersId){
        // get virtual server object
        $virtualServersId = intval($virtualServersId);
        $virtualServer = $this->getModelClass()::findFirstByid($virtualServersId);
        if (!$virtualServer) {
            $message = $this->translate("virtualserver_does_not_exist");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }

        // check permissions
        if(!$this->permissions->checkPermission('virtual_servers', 'configure', array('item' => $virtualServer)))
            return $this->forwardTo401();

        // check if server is ovz enabled    
        if($virtualServer->getOvz() == 0){
            $message = $this->translate("virtualserver_not_ovz_enabled");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }

        // execute ovz_list_info
        // no pending needed because job is readonly
        $push = $this->getPushService();
        $params = array('UUID'=>$virtualServer->getOvzUuid());
        $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_list_info',$params);
        $message = $this->translate("virtualserver_job_infolist_failed");
        if($job->getDone()==2) throw new \Exception($message.$job->getError());

        $this->saveVirutalServerSettings($job,$virtualServer);

        // get OVZ Settings
        $ovzSettings = json_decode($virtualServer->getOvzSettings(),true);

        // fill form fields
        $configureVirtualServersFormFields = new ConfigureVirtualServersFormFields();
        $configureVirtualServersFormFields->virtual_servers_id = $virtualServer->getId();
        $configureVirtualServersFormFields->dns = $ovzSettings['DNS Servers'];
        $configureVirtualServersFormFields->cores = $virtualServer->getCore();
        $configureVirtualServersFormFields->memory = $virtualServer->getMemory()." MB";
        $configureVirtualServersFormFields->diskspace = Helpers::formatBytesHelper(Helpers::convertToBytes($virtualServer->getSpace()."MB"));
        if($ovzSettings['Autostart'] == 'on'){
            $configureVirtualServersFormFields->startOnBoot = 1;
        }elseif($ovzSettings['Autostart'] == 'off') {
            $configureVirtualServersFormFields->startOnBoot = 0;
        }

        // call view
        $this->view->form = new ConfigureVirtualServersForm($configureVirtualServersFormFields); 
        $this->view->pick("virtual_servers/configureVirtualServersForm");
    }

    /**
    * execute the job and safe the configuration
    * 
    */
    public function sendConfigureVirtualServersAction(){
        // POST request?
        if (!$this->request->isPost())
            return $this->redirectTo("virtual_servers/slidedata");

        // get virtual server
        $virtualServer = VirtualServers::findFirstById($this->request->getPost("virtual_servers_id", "int"));
        if (!$virtualServer) {
            $message = $this->translate("virtualserver_does_not_exist");
            $this->flashSession->error("Virtual Server does not exist");
            return $this->redirectTo("virtual_servers/slidedata");
        }

        // check permissions
        if(!$this->permissions->checkPermission('virtual_servers', 'configure', array('item' => $virtualServer)))
            return $this->forwardTo401();

        // check if server is ovz enabled    
        if($virtualServer->getOvz() == 0){
            $message = $this->translate("virtualserver_not_ovz_enabled");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }

        // validate FORM
        $form = new ConfigureVirtualServersForm();
        $data = $this->request->getPost();
        if (!$form->isValid($data, $form)) {
            $this->view->form = $form; 
            $this->view->pick("virtual_servers/configureVirtualServersForm");
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
                    if(!Dcoipobjects::isValidIPv4($dnsIP)){
                        $message1 = $this->translate("virtualserver_IP_not_valid");
                        $message = $dnsIP.$message1;
                        $this->redirectErrorToConfigureVirtualServers($message,'dns',$form);
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
            return $this->redirectErrorToConfigureVirtualServers($message,'cores',$form);
        }

        if($core > $virtualServer->PhysicalServers->getCore()){
            $message1 = $this->translate("virtualserver_max_core");
            $message = $message1.$virtualServer->PhysicalServers->getCore().')';
            return $this->redirectErrorToConfigureVirtualServers($message,'cores',$form);
        }

        try{
            // memory
            $memory = Helpers::convertToBytes($form->memory);

            // check if memory is numeric
            if(!is_numeric($memory)){
                $message1 = $this->translate("virtualserver_ram_numeric");
                $message = $message1;
                return $this->redirectErrorToConfigureVirtualServers($message,'memory',$form);
            }

            // chech if memory is minmum 512 MB
            if(gmp_cmp($memory,Helpers::convertToBytes('512MB'))<0){
                $message1 = $this->translate("virtualserver_min_ram");
                $message = $message1;
                return $this->redirectErrorToConfigureVirtualServers($message,'memory',$form);
            } 

            // check if memory of host is exceeded
            $hostRam = Helpers::convertToBytes($virtualServer->PhysicalServers->getMemory().'MB');
            if(gmp_cmp($memory,$hostRam)>0){
                $message1 = $this->translate("virtualserver_max_ram");
                $message = $message1.$virtualServer->PhysicalServers->getMemory().' MB)';
                return $this->redirectErrorToConfigureVirtualServers($message,'memory',$form);
            }

            // final memory in MibiBytes
            $memory = Helpers::convertBytesToMibiBytes($memory);

            // space
            $diskspace = Helpers::convertToBytes($form->diskspace);

            // check if diskpace is numeric
            if(!is_numeric($diskspace)){
                $message1 = $this->translate("virtualserver_space_numeric");
                $message = $message1;
                return $this->redirectErrorToConfigureVirtualServers($message,'diskspace',$form);
            }

            // check if diskspace is min
            if(gmp_cmp($diskspace,Helpers::convertToBytes('20GB'))<0){
                $message1 = $this->translate("virtualserver_min_space");
                $message = $message1;
                return $this->redirectErrorToConfigureVirtualServers($message,'diskspace',$form);
            }
            // check if diskspace of host is exceeded
            $hostDiskspace = Helpers::convertToBytes($virtualServer->PhysicalServers->getSpace().'GB');
            if(gmp_cmp($diskspace,$hostDiskspace)>0){
                $message1 = $this->translate("virtualserver_max_space");
                $message = $message1.$virtualServer->PhysicalServers->getSpace().' GB)';
                return $this->redirectErrorToConfigureVirtualServers($message,'diskspace',$form);
            }

            // final diskspcae in MibiBytes
            $diskspace = Helpers::convertBytesToMibiBytes($diskspace);

            // job
            $virtualServerConfig = array(
                'nameserver' => $dns,
                'cpus' => $core,
                'memsize' => $memory,
                'diskspace' => $diskspace,
                'onboot' => ($form->startOnBoot)?'yes':'no',
            );

            // execute ovz_restart_vs job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $push = $this->getPushService();
            $params = array(
                'UUID'=>$virtualServer->getOvzUuid(),
                'CONFIG'=>$virtualServerConfig
            );
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_modify_vs',$params,$pending);
            $message = $this->translate("virtualserver_job_modifysnapshotexec_failed");
            if($job->getDone()==2) throw new \Exception($message.$job->getError());

            $this->saveVirutalServerSettings($job,$virtualServer);

            // success
            $message = $this->translate("virtualserver_job_modifyvs");
            $this->flashSession->success($message);

            // go back to slidedata view
            $this->redirectTo("virtual_servers/slidedata");
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
    }

    private function redirectErrorToConfigureVirtualServers($message,$field,$form){
        $form->appendMessage(new \Phalcon\Validation\Message($message,$field));
        $this->view->form = $form; 
        $this->view->pick("virtual_servers/configureVirtualServersForm");
        return; 
    }

    private function saveVirutalServerSettings($job,$virtualServer){
        // save settings
        $settings = $job->getRetval(true);
        $virtualServer->setOvzSettings($job->getRetval());
        self::assignSettings($virtualServer,$settings);

        if ($virtualServer->save() === false) {
            $messages = $virtualServer->getMessages();
            foreach ($messages as $message) {
                $this->flashSession->warning($message);
            }
            $message = $this->translate("virtualserver_update_failed");
            throw new \Exception($message.$virtualServer->getName());
        }
    }

    /**
    * assign the ovz settings to its relevant value
    * 
    * @param VirtualServers $virtualServer
    * @param mixed $settings
    */
    public static function assignSettings(\RNTForest\ovz\models\VirtualServers $virtualServer,$settings){
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

            // ToDo: check last info Update..

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
                $allMessages = $this->translate("virtualservers_save_replica_slave_failed");
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
                $message = $this->translate("virtualservers_job_create_failed");
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
                $message = $this->translate("virtualservers_job_modify_failed");
                throw new \Exception($message.$job->getError());
            }
            $job2Id = $job->getId();

            // initial Sync
            $params = array(
                "UUID"=>$replicaMaster->getOvzUuid(),
                "SLAVEHOSTFQDN"=>$replicaSlaveHost->getFqdn(),
                "SLAVEUUID"=>$replicaSlave->getOvzUuid(),
            );
            $job = $push->queueDependentJob($replicaMasterHost,'ovz_sync_replica',$params,$job2Id);            
            if($job->getDone() == 2){
                $message = $this->translate("virtualservers_job_sync_replica_failed");
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
                $allMessages = $this->translate("virtualservers_update_replica_master_failed");
                foreach ($replicaSlave->getMessages() as $message) {
                    $allMessages .= $message->getMessage();
                }
                throw new \Exception($allMessages);
            }

            $message = self::translate("virtualservers_replica_sync_run_in_background");
            $this->flashSession->success($message);

        } catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirecToTableSlideDataAction();        
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
            $this->flashSession->success("replica_running_in_background");

        } catch (\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        
        $this->redirecToTableSlideDataAction();        
    }

    public function ovzReplicaFailoverAction($replicaMasterId) {

        // sanitize Parameters
        $replicaMasterId = $this->filter->sanitize($replicaMasterId,"int");

        try{
            // validate (throws exceptions)
            $replicaMaster = VirtualServers::tryFindById($replicaMasterId);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $replicaMaster));
            $this->tryCheckOvzEnabled($replicaMaster);
            
            if($replicaMaster->getOvzReplica() != 1) throw new \Exception("Virtual Server is not replica master!");
            
            // shutdown master
            $this->tryOvzListInfo($replicaMaster);
            if($replicaMaster->getOvzState() == 'running') $this->tryStopVS($replicaMaster);
    
            // check if master and slave is stopped            
            $this->tryOvzListInfo($replicaMaster);
            $replicaMasterState = $replicaMaster->getOvzState();
            if($replicaMasterState != 'stopped'){
                throw new \Exception("virtualservers_replica_master_not_stopped");
            }
    
            $replicaSlave = new VirtualServers;
            $replicaSlave = $replicaMaster->ovzReplicaId;
            $this->tryOvzListInfo($replicaSlave);
            if($replicaSlave->getOvzState() != 'stopped'){
                throw new \Exception("virtualservers_replica_slave_not_stopped");
            }

            // run replica, don't go on until job ist fineshed
            $replica = $this->getReplicaService();
            if($job = $replica->run($replicaMaster)){
                $push = $this->getPushService();
                while($job->getDone()>0){
                    sleep(5);
                    $push->pushJobs();
                }
            }
            
            // Todo: update actual config (incl dcoip)

            // turn around master and slave
            $replicaMaster->setOvzReplica(2);
            $masterName = $replicaMaster->getName();
            $replicaMaster->setName($replicaSlave->getName());
            $replicaMaster->update();

            $replicaSlave->setOvzReplica(1);
            $replicaSlave->setName($masterName);
            $replicaSlave->update();
            
            if($replicaMasterState == 'running'){
                $this->tryStartVS($replicaSlave);
            }

            // update settings
            $this->tryOvzListInfo($replicaSlave);
    
            $this->flashSession->success('virtualservers_replica_failover_success');
    
        } catch (\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        
        $this->redirecToTableSlideDataAction();        
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

            $message = self::translate("virtualservers_replica_switched_off");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
        $this->forwardToTableSlideDataAction();
    }

    private function getCurrentOVZSettings($id){
        $currentData = $this->getAllData($id);
        $this->updateOVZSettingsInDB($id,$currentData);
        return $currentData;
    }

    /**
    * Show changeRootPassword form
    * 
    * @param mixed $virtualServersId
    */
    public function changeRootPasswordAction($virtualServersId) {
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
        if(!$this->permissions->checkPermission('virtual_servers', 'change_root_password', array('item' => $virtualServer))){
            return $this->forwardTo401();
        }   
        
        // check if server is ovz integrated
        if($virtualServer->getOvz() != 1){
            $message = $this->translate("virtualserver_not_ovz_integrated");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }
        
        // prepare form fields
        $changeRootPasswordFormFields = new ChangeRootPasswordFormFields();
        $changeRootPasswordFormFields->virtual_servers_id = $virtualServersId;
        
        // call view
        $this->view->form = new ChangeRootPasswordForm($changeRootPasswordFormFields); 
        $this->view->pick("virtual_servers/changeRootPasswordForm");
    }
    
    /**
    * Change root password
    * 
    */
    public function changeRootPasswordExecuteAction() {
        try{
            // POST request?
            if (!$this->request->isPost()) 
                return $this->redirectTo("virtual_servers/slidedata");

            // validate FORM
            $form = new ChangeRootPasswordForm;
            $item = new ChangeRootPasswordFormFields();
            $data = $this->request->getPost();
            if (!$form->isValid($data, $item)) {
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/changeRootPasswordForm");
                return; 
            }
            
            // sanitize
            $virtualServersId = $this->filter->sanitize($data['virtual_servers_id'],"int");
            
            // check if virutal server exists
            $virtualServer = VirtualServers::findFirstByid($virtualServersId);
            if (!$virtualServer){
                $message = $this->translate("virtualserver_does_not_exist");
                $this->flashSession->error($message);
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/changeRootPasswordForm");
                return;
            }
            
            // check permissions
            if(!$this->permissions->checkPermission('virtual_servers', 'change_root_password', array('item' => $virtualServer))){
                return $this->forwardTo401();
            }
            
            // check if server is ovz integrated
            if($virtualServer->getOvz() != 1){
                $message = $this->translate("virtualserver_not_ovz_integrated");
                $this->flashSession->error($message);
                return $this->forwardToTableSlideDataAction();
            }
            
            // execute ovz_set_pwd job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage
            $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $push = $this->getPushService();
            $params = array(
                'UUID'=>$virtualServer->getOvzUuid(),
                'ROOTPWD'=>$data['password']
            );
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_set_pwd',$params,$pending);
            $message = $this->translate("virtualserver_change_root_password_failed");
            if($job->getDone()==2) throw new \Exception($message.$job->getError());
            
            // success message
            $message = $this->translate("virtualserver_change_root_password_successful");
            $this->flashSession->success($message);
            
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirecToTableSlideDataAction();
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

class ConfigureVirtualServersFormFields{
    public $virtual_servers_id = 0;
    public $dns = "";
    public $cores = 0;
    public $memory = "";
    public $diskspace = "";
    public $startOnBoot = 0;
<<<<<<< HEAD
    public $description = "";
}
=======
}

class ChangeRootPasswordFormFields{
    public $password = "";
}
>>>>>>> develop
