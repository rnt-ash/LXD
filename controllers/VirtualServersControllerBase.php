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

namespace RNTForest\lxd\controllers;

use Phalcon\Http\Client\Request;

use RNTForest\core\libraries\PDF;
use RNTForest\lxd\models\VirtualServers;
use RNTForest\lxd\models\PhysicalServers;
use RNTForest\lxd\models\IpObjects;
use RNTForest\core\models\Customers;
use RNTForest\core\models\Logins;
use RNTForest\lxd\forms\VirtualServersForm;
use RNTForest\lxd\forms\VirtualServersConfigureForm;
use RNTForest\lxd\forms\VirtualServersModifyForm;
use RNTForest\lxd\forms\IpObjectsForm;
use RNTForest\lxd\forms\SnapshotForm;
use RNTForest\lxd\forms\ReplicaActivateForm;
use RNTForest\lxd\forms\RootPasswordChangeForm;
use RNTForest\lxd\models\MonJobs;
use RNTForest\lxd\datastructures\ReplicaActivateFormFields;
use RNTForest\lxd\datastructures\RootPasswordChangeFormFields;
use RNTForest\lxd\datastructures\SnapshotFormFields;
use RNTForest\lxd\datastructures\VirtualServersConfigureFormFields;

use \RNTForest\core\libraries\Helpers;

class VirtualServersControllerBase extends \RNTForest\core\controllers\TableSlideBase
{
    protected function getSlideDataInfo() {
        $scope = $this->permissions->getScope('virtual_servers','general');
        $joinQuery = NULL;
        if ($scope == 'customers'){
            $scopeQuery .= " AND customers_id = ".$this->session->get('auth')['customers_id'];
        } else if($scope == 'partners'){
            $scopeQuery .= ' AND (RNTForest\lxd\models\VirtualServers.customers_id = '.$this->session->get('auth')['customers_id'];
            $scopeQuery .= ' OR RNTForest\core\models\CustomersPartners.partners_id = '.$this->session->get('auth')['customers_id'].")";
            $joinQuery = array('model'=>'RNTForest\core\models\CustomersPartners',
                'conditions'=>'RNTForest\lxd\models\VirtualServers.customers_id = RNTForest\core\models\CustomersPartners.customers_id',
                'type'=>'LEFT');
        }

        return array(
            "type" => "slideData",
            "model" => '\RNTForest\lxd\models\VirtualServers',
            "form" => '\RNTForest\lxd\forms\VirtualServersForm',
            "controller" => "virtual_servers",
            "action" => "slidedata",
            "slidenamefield" => "name",
            "slidenamefielddescription" => "Servername",
            "scope" => $scopeQuery,
            "join" => $joinQuery,
            "order" => "name",
            "orderdir" => "asc",
            "filters" => array(),
            "page" => 1,
            "limit" => 10,
        );
    }

    protected function prepareSlideFilters($virtualServers,$level) { 
        // receive all filters
        if($this->request->has('filterAll')){
            $oldfilter = $this->slideDataInfo['filters']['filterAll'];
            $this->slideDataInfo['filters']['filterAll'] = $this->request->get("filterAll", "string");
            if($oldfilter != $this->slideDataInfo['filters']['filterAll']) $this->slideDataInfo['page'] = 1;
        }

        if($this->request->has('filterCustomers_id')){
            $oldfilter = $this->slideDataInfo['filters']['filterCustomers_id'];
            $this->slideDataInfo['filters']['filterCustomers_id'] = $this->request->get("filterCustomers_id", "int");
            $this->slideDataInfo['filters']['filterCustomers'] = $this->request->get("filterCustomers", "string");
            if($oldfilter != $this->slideDataInfo['filters']['filterCustomers_id']) $this->slideDataInfo['page'] = 1;
        }

        if($this->request->has('filterPhysicalServers')){
            $oldfilter = $this->slideDataInfo['filters']['filterPhysicalServers'];
            $this->slideDataInfo['filters']['filterPhysicalServers'] = $this->request->get("filterPhysicalServers", "int");
            if($oldfilter != $this->slideDataInfo['filters']['filterPhysicalServers']) $this->slideDataInfo['page'] = 1;
        }
        
        // put resultsets to the view
        // get physical servers from scope
        $scope = $this->permissions->getScope("virtual_servers","filter_physical_servers"); 
        $findParameters = array("order"=>"name");
        $resultset = PhysicalServers::findFromScope($scope,$findParameters);
        
        // create array
        if(!empty($resultset->toArray())){
            $physicalServers = array();
            $physicalServers[0]['name'] = PhysicalServers::translate("physicalserver_all_physicalservers");
            $physicalServers[0]['count'] = '';
            foreach($resultset as $physicalServer){
                $physicalServers[$physicalServer->id]['name'] = $physicalServer->name;
                $findParameters = array("conditions"=>"physical_servers_id = ".$physicalServer->id);
                $physicalServers[$physicalServer->id]['count'] = count(VirtualServers::findFromScope($scope,$findParameters))." VS";
                if(!empty($this->slideDataInfo['filters']['filterPhysicalServers'])){
                    if($this->slideDataInfo['filters']['filterPhysicalServers'] == $physicalServer->id){
                        $physicalServers[$physicalServer->id]['selected'] = 'selected';
                    }
                }
            }
        }
        $this->view->physicalServers = $physicalServers;
    }

    protected function isValidSlideFilterItem($virtualServer,$level){
        if(!empty($this->slideDataInfo['filters']['filterAll'])){ 
            if(strpos(strtolower($virtualServer->name),strtolower($this->slideDataInfo['filters']['filterAll']))===false)            
                return false;
        }
        if(!empty($this->slideDataInfo['filters']['filterCustomers_id'])){ 
            if($virtualServer->customers_id != $this->slideDataInfo['filters']['filterCustomers_id'])
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
        $content .= $this->simpleview->render("virtual_servers/slideDetail.volt");
        return $content;
    }

    /**
    * check if Virtual or Physical Server is LXD enabled. Otherwise it throws an exception
    * 
    * @param server $server
    * @throws Exceptions
    */
    public static function tryCheckLxdEnabled($server) {
        // check if server is lxd enabled   
        if($server->getLxd() == 0){
            $message = self::translate("virtualserver_server_not_lxd_enabled");
            throw new \Exception($message);
        }
    }
    
    /**
    * execute lxd_change_ctstate job
    * 
    * @param \RNTForest\lxd\models\VirtualServers $virtualServer
    * @return {\RNTForest\core\models\Jobs|\RNTForest\core\models\JobsBase}
    * @throws \Exceptions
    */
    protected function tryChangeCTState($virtualServer,$action){
        // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
        $pending = '\RNTForest\lxd\models\VirtualServers:'.$virtualServer->getId().':general:1';
        $params = array(
            'NAME' => $virtualServer->getName(),
            'ACTION' => $action
        );
        $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'lxd_change_ctstate',$params,$pending);
        
        return $job;
    }

    /**
    * Change state of CT
    * 
    * @param int $serverId
    * @param string $action
    */
    public function changeCTStateAction($serverId,$action){
        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");
        $action = $this->filter->sanitize($action,["trim","striptags"]);

        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($serverId);
            $this->tryCheckPermission('virtual_servers','changestate',array("item"=>$virtualServer));
            $this->tryCheckLxdEnabled($virtualServer);

            // try to start
            $job = $this->tryChangeCTState($virtualServer,$action);

            // save new state            
            self::virtualServerSettingsSave($job,$virtualServer);

            // success
            $message = $this->translate("virtualserver_job_change_state");
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

            // hook pre delete
            if (!$this->preDelete($virtualServer))
                return $this->forwardToTableSlideDataAction();
            
            // execute lxd_delete_ct job   
            if($virtualServer->getLxd()){     
                // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
                $pending = '\RNTForest\lxd\models\VirtualServers:'.$virtualServer->getId();
                $params = array("NAME"=>$virtualServer->getName());
                $job = $this->getPushService()->executeJob($virtualServer->physicalServers,'lxd_delete_ct',$params,$pending);
                if($job->getDone() == 2){
                    $message = $this->translate("virtualserver_job_destroy_failed");
                    throw new \Exception($message.$job->getError());
                }elseif(!empty($job->getWarning())){
                    $this->flashSession->warning($job->getRetval());
                }
            }
            
            // delete IP Objects
            foreach($virtualServer->ipobjects as $ipobject){
                if(!$ipobject->delete()){
                    foreach ($ipobject->getMessages() as $message) {
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
        // store in session
        $this->session->set($this->getFormClassName(), array(
            "op" => "new",
            "vstype" => "CT",
            "distribution" => "",
        ));

        $form = $this->getFormClass();
        $virtualServerForm = new $form(new VirtualServers());
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

        $form = $this->getFormClass();
        $virtualServerForm = new $form(new VirtualServers());
        $this->forwardToFormAction($virtualServerForm);

    }

    /**
    * Does some preSave configurations like convert the bytestrings to MB for memory or GB for diskspace.
    * 
    * @param VirtualServers $virtualServer
    * @param VirtualServersForm $form
    */
    protected function preSave($virtualServer,$form){
        try{
            // validate physical server and check permissions
            $physicalServer = PhysicalServers::findFirst($virtualServer->getPhysicalServersId());
            if(!$physicalServer){
                $message = $this->translate("virtualserver_physicalserver_required");
                $form->appendMessage(new \Phalcon\Validation\Message($message,'physical_servers_id'));
                return false;
            }
            $this->tryCheckPermission('physical_servers','general',array('item' => $physicalServer));
            
            // only check if physical server is lxd enabled if a CT is about to be created
            $vstype = $this->session->get("VirtualServersForm")['vstype'];
            $op = $this->session->get("VirtualServersForm")['op'];
            if($vstype == 'CT') {
                $this->tryCheckLxdEnabled($physicalServer);
                
                // check if HW Specs are valid
                $specs = $this->checkHardwareSpecs($virtualServer->getCore(),$virtualServer->getMemory(),$virtualServer->getSpace(),$virtualServer,$form);
                
                // if an error appeard, go back to form
                if(key_exists('error',$specs)){
                    $form->appendMessage(new \Phalcon\Validation\Message($specs['error']['message'],$specs['error']['field']));
                    return false;
                }else{
                    // if everything was ok, set correct formated values
                    $virtualServer->setCore($specs['cores']);
                    $virtualServer->setMemory($specs['memory']);
                    $virtualServer->setSpace($specs['diskspace']);
                }
            }
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            return false;
        }
        return true;
    }
    
    /**
    * creates a new CT
    * 
    * @param VirtualServers $virtualServer
    * @param VirtualServersForm $form
    */
    protected function postSave($virtualServer,$form){
        try{
            $session = $this->session->get($this->getFormClassName());
            if($session['vstype'] == 'CT'){
                $virtualServer->setLxd(true);
                if(!$virtualServer->update()){
                    $message = $this->translate("virtualserver_update_server_failed");
                    throw new \Exception($message);
                }

                $params = array(
                    "NAME" => $virtualServer->getName(),
                    "CPUS" => $virtualServer->getCore(),
                    "RAM" => $virtualServer->getMemory()."MB",
                    "DISKSPACE" => $virtualServer->getSpace()."GB",
                    "STORAGEPOOL" => $this->config->lxd['defaultStoragePool'],
                    "IMAGEALIAS" => $this->config->lxd['defaultImage']
                );

                // execute lxd_create_ct job        
                // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
                $pending = '\RNTForest\lxd\models\VirtualServers:'.$virtualServer->getId().':general:2';
                $job = $this->getPushService()->executeJob($virtualServer->PhysicalServers,'lxd_create_ct',$params,$pending);
                if($job->getDone() == 2){
                    // delete virtual server if job was not successful
                    $virtualServer->delete();
                    
                    // throw exception
                    $message = $this->translate("virtualserver_job_create_failed");
                    throw new \Exception($message.$job->getError());
                }
                
                // save the settings from the CT
                self::virtualServerSettingsSave($job,$virtualServer);
            }
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            return false;
        }

        return true; 
    }

    /*
    * List snapshots
    * 
    * @param int $serverId
    */
    public function lxdSnapshotListAction($virtualServerId){

        // sanitize parameters
        $virtualServerId = $this->filter->sanitize($virtualServerId, "int");
        
        try{
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServerId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckLxdEnabled($virtualServer);

            // execute lxd_list_snapshots job 
            // no pending needed because job is readonly       
            $params = array('NAME'=>$virtualServer->getName());
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'lxd_list_snapshots',$params);

            // save snapshots
            $snapshots = $job->getRetval();
            $this->lxdSnapshotSave($virtualServer,$snapshots);

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
    * switch to a snapshot
    * 
    * @param mixed $snapshotId
    * @param int $serverId
    */
    public function lxdSnapshotSwitchAction($snapshotName,$virtualServerId) {
        // sanitize parameters
        $virtualServerId = $this->filter->sanitize($virtualServerId, "int");
        $snapshotName = $this->filter->sanitize($snapshotName, "string");
        
        try {    
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServerId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckLxdEnabled($virtualServer);

            // execute lxd_restore_snapshot job
            // pending with severity 2 so that in error state no further jobs can be executed and the entity is locked     
            $pending = '\RNTForest\lxd\models\VirtualServers:'.$virtualServer->getId();
            $params = array('CTNAME'=>$virtualServer->getName(),'SNAPSHOTNAME'=>$snapshotName);
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'lxd_restore_snapshot',$params,$pending);

            // save snapshots
            $snapshots = $job->getRetval();
            $this->lxdSnapshotSave($virtualServer,$snapshots);

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
    public function lxdSnapshotCreateAction($virtualServersId){

        // sanitize
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");
        
        try{
            // find virtual server
            $virtualServer = VirtualServers::tryFindById($virtualServersId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckLxdEnabled($virtualServer);
            
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
    public function lxdSnapshotCreateExecuteAction() {
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
            $this->tryCheckLxdEnabled($virtualServer);

            // execute lxd_create_snapshot job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = '\RNTForest\lxd\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array('CTNAME'=>$virtualServer->getName(),'SNAPSHOTNAME'=>$item->name);
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'lxd_create_snapshot',$params,$pending);
            
            // save snapshots
            $snapshots = $job->getRetval();
            $this->lxdSnapshotSave($virtualServer,$snapshots);

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
    public function lxdSnapshotDeleteAction($snapshotName,$virtualServerId) {

        // sanitize
        $snapshotName = $this->filter->sanitize($snapshotName, "string");
        $virtualServerId = $this->filter->sanitize($virtualServerId, "int");
        
        try {    
            // validate
            $virtualServer = VirtualServers::tryFindById($virtualServerId);  
            $this->tryCheckPermission('virtual_servers', 'snapshots', array('item' => $virtualServer));
            $this->tryCheckLxdEnabled($virtualServer);

            // execute lxd_delete_snapshot job        
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = '\RNTForest\lxd\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array('CTNAME'=>$virtualServer->getName(),'SNAPSHOTNAME'=>$snapshotName);
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'lxd_delete_snapshot',$params,$pending);

            // save snapshots
            $snapshots = $job->getRetval();
            $this->lxdSnapshotSave($virtualServer,$snapshots);

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
    * Save snapshot settings in virtual server model
    * 
    * @param mixed $virtualServer
    * @param mixed $snapshots
    * @throws /Exceptions
    */
    private function lxdSnapshotSave($virtualServer,$snapshots){
        // set snapshots
        $virtualServer->setLxdSnapshots($snapshots);
        
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
            "server_class" => '\RNTForest\lxd\models\VirtualServers',
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
            $this->tryCheckLxdEnabled($virtualServer);
        
            // store in session
            $this->session->set($this->getFormClassName(), array(
                "op" => "edit",
            ));
        
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
            $this->tryCheckLxdEnabled($virtualServer);

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
            $this->tryCheckLxdEnabled($virtualServer);

            // execute lxd_get_settings
            // no pending needed because job reads only
            $params = array('NAME'=>$virtualServer->getName());
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'lxd_get_settings',$params);
            
            // save the settings gotten from the job
            self::virtualServerSettingsSave($job,$virtualServer);

            // fill form fields
            $virtualServersConfigureFormFields = new VirtualServersConfigureFormFields();
            $virtualServersConfigureFormFields->virtual_servers_id = $virtualServer->getId();
            $virtualServersConfigureFormFields->core = $virtualServer->getCore();
            $virtualServersConfigureFormFields->memory = Helpers::formatBytesHelper(Helpers::convertToBytes($virtualServer->getMemory()."MB"));
            $virtualServersConfigureFormFields->space = Helpers::formatBytesHelper(Helpers::convertToBytes($virtualServer->getSpace()."GB"));

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
            $this->tryCheckLxdEnabled($virtualServer);

            // validate FORM
            $form = new VirtualServersConfigureForm();
            $data = $this->request->getPost();
            if (!$form->isValid($data, $form)) {
                $this->view->form = $form; 
                $this->view->pick("virtual_servers/virtualServersConfigureForm");
                return; 
            }

            // business logic
            $retval = $this->checkHardwareSpecs($form->core,$form->memory,$form->space,$virtualServer,$form);
            
            // if an error appeared, go back to form
            if(key_exists('error',$retval)){
                return $this->redirectErrorToVirtualServersConfigure($retval['error']['message'],$retval['error']['field'],$retval['error']['form']);
            }
            
            // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
            $pending = '\RNTForest\lxd\models\VirtualServers:'.$virtualServer->getId().':general:1';
            $params = array(
                'NAME' => $virtualServer->getName(),
                'CPUS' => $retval['cores'],
                'RAM' => $retval['memory'],
                'DISKSPACE' => $retval['diskspace'],
                'STORAGEPOOL' => $virtualServer->getLxdSettingsArray()['devices']['root']['pool']
            );
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'lxd_modify_ct',$params,$pending);
            $this->virtualServerSettingsSave($job,$virtualServer);

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
    
    /**
    * Checks if the HW Specs are valid and returns the correct formatted values
    * 
    * @param mixed $core
    * @param long $memory
    * @param long $diskspace
    * @param mixed $virtualServer
    * @param mixed $form
    */
    private function checkHardwareSpecs($core,$memory,$diskspace,$virtualServer,$form){
        // cores
        // check if cores is numeric
        if(!is_numeric($core)){
            $message = $this->translate("virtualserver_core_numeric");
            return array('error'=>array('message'=>$message,'field'=>'core','form'=>$form));
        }

        if($core < 1){
            $message = $this->translate("virtualserver_min_core");
            return array('error'=>array('message'=>$message,'field'=>'core','form'=>$form));
        }

        if($core > $virtualServer->PhysicalServers->getCore()){
            $message = $this->translate("virtualserver_max_core").$virtualServer->PhysicalServers->getCore().')';
            return array('error'=>array('message'=>$message,'field'=>'core','form'=>$form));
        }

        // memory
        $memory = Helpers::convertToBytes($memory);

        // check if memory is numeric
        if(!is_numeric($memory)){
            $message = $this->translate("virtualserver_ram_numeric");
            return array('error'=>array('message'=>$message,'field'=>'memory','form'=>$form));
        }

        // chech if memory is minmum 512 MB
        if($memory < Helpers::convertToBytes('512MB')){
            $message = $this->translate("virtualserver_min_ram");
            return array('error'=>array('message'=>$message,'field'=>'memory','form'=>$form));
        } 

        // check if memory of host is exceeded
        $hostRam = Helpers::convertToBytes($virtualServer->PhysicalServers->getMemory().'MB');
        if($memory > $hostRam){
            $message = $this->translate("virtualserver_max_ram").$virtualServer->PhysicalServers->getMemory().' MB)';
            return array('error'=>array('message'=>$message,'field'=>'memory','form'=>$form));
        }

        // final memory in MibiBytes
        $memory = Helpers::convertBytesToMibiBytes($memory);

        // space
        $diskspace = Helpers::convertToBytes($diskspace);

        // check if diskpace is numeric
        if(!is_numeric($diskspace)){
            $message = $this->translate("virtualserver_space_numeric");
            return array('error'=>array('message'=>$message,'field'=>'space','form'=>$form));
        }

        // check if diskspace is min
        if($diskspace < Helpers::convertToBytes('5GB')){
            $message = $this->translate("virtualserver_min_space");
            return array('error'=>array('message'=>$message,'field'=>'space','form'=>$form));
        }
        // check if diskspace of host is exceeded
        $hostDiskspace = Helpers::convertToBytes($virtualServer->PhysicalServers->getSpace().'GB');
        if($diskspace > $hostDiskspace){
            $message = $this->translate("virtualserver_max_space").$virtualServer->PhysicalServers->getSpace().' GB)';
            return array('error'=>array('message'=>$message,'field'=>'space','form'=>$form));
        }

        // final diskspace in MibiBytes
        $diskspace = Helpers::convertBytesToGibiBytes($diskspace);
        
        // return all formatted and validated values
        return array('cores'=>$core,'memory'=>$memory,'diskspace'=>$diskspace);
    }

    /**
    * go back to the form if an error appears
    * 
    * @param mixed $message
    * @param mixed $field
    * @param mixed $form
    * @return mixed
    */
    private function redirectErrorToVirtualServersConfigure($message,$field,$form){
        $form->appendMessage(new \Phalcon\Validation\Message($message,$field));
        $this->view->form = $form; 
        $this->view->pick("virtual_servers/virtualServersConfigureForm");
        return; 
    }

    /**
    * try to save settings from jobs to virtual server
    * 
    * @param \RNTForest\core\models\Jobs $job
    * @param \RNTForest\lxd\models\VirtualServers $virtualServer
    * 
    * @throws \Exceptions
    */
    public static function virtualServerSettingsSave($job,$virtualServer){
        // save lxd settings to the database
        $virtualServer->setLxdSettings(json_encode($job->getRetval(true)['metadata']));
        
        self::virtualServerSettingsAssign($virtualServer);
        
        if ($virtualServer->update() === false) {
            $messages = $virtualServer->getMessages();
            foreach ($messages as $message) {
                \Phalcon\Di::getDefault()->get('flashSession')->warning($message);
            }
            $message = self::translate("virtualserver_update_failed");
            throw new \Exception($message.$virtualServer->getName());
        }
    }

    /**
    * assign the lxd settings to its relevant value
    * 
    * @param VirtualServers $virtualServer
    */
    public static function virtualServerSettingsAssign(\RNTForest\lxd\models\VirtualServers $virtualServer){
        $settings = $virtualServer->getLxdSettingsArray();
        
        $virtualServer->setName($settings['name']);
        $virtualServer->setLxd(1);
        $virtualServer->setCore(intval($settings['config']['limits.cpu']));
        $memoryInBytes = intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['config']['limits.memory']));
        $virtualServer->setMemory(intval(\RNTForest\core\libraries\Helpers::convertBytesToMibiBytes($memoryInBytes)));
        $spaceInBytes = intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['devices']['root']['size']));
        $virtualServer->setSpace(intval(\RNTForest\core\libraries\Helpers::convertBytesToGibiBytes($spaceInBytes)));
    }

    /**
    * return customers according to the filter
    * 
    */
    public function getCustomersAsJsonAction(){
        // POST request?
        if (!$this->request->isPost()) 
            return $this->redirectToTableSlideDataAction();

        // get query from post and scope
        $filterString = $this->request->getPost("query", "string");
        $scope = $this->permissions->getScope('virtual_servers','filter_customers');
        $customers = \RNTForest\core\models\Customers::getCustomersAsJson($filterString,$scope);
        return $customers;
    }
    
    /**
    * Show virtual server datasheet as PDF
    * 
    * @param mixed $virtualServersId
    * @return mixed
    * @throws Exceptions
    */
    public function genPDFAction($virtualServersId){
        // Sanitize Parameters
        $virtualServersId = $this->filter->sanitize($virtualServersId,"int");

        try{
            // Validate (throws exceptions)
            $virtualServer = VirtualServers::tryFindById($virtualServersId);
            $this->tryCheckPermission('virtual_servers', 'general', array('item' => $virtualServer));
            
            // Create PDF Object
            $this->PDF = new PDF();
            $this->PDF->SetAutoPageBreak(true, 10);

            // Author and title        
            $this->PDF->SetAuthor(BASE_PATH.$this->config->pdf['author']);
            $this->PDF->SetTitle($this->translate("virtualservers_datasheet"));

            // Creating page 
            $this->PDF->AddPage();
            $this->PDF->SetFillColor(32,32,32);

            // Print header
            $this->PDF->printHeader($this->translate("virtualservers_datasheet"),$virtualServer->Customer->printAddressText('box'));

            // Print Content
            $this->PDF->Cell(0,0,$this->translate("virtualservers_servrname") .$virtualServer->name, 0, 2, '',false);
            $this->PDF->SetTextColor(255,255,255);
            $this->PDF->SetFont('','B');
            $this->PDF->Ln(2);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_general_info"), 0, 2, 'L',true);
            $this->PDF->SetTextColor(0,0,0);
            $this->PDF->SetFont('','');
            $this->PDF->Ln(1);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_fqdn") .$virtualServer->fqdn, 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_activ_date") .$virtualServer->activation_date, 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_server_type"), 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_pricepermonth"), 0, 2, '',false);
            // TODO Serverinformation output
            
            $this->PDF->SetTextColor(255,255,255);
            $this->PDF->SetFont('','B');
            $this->PDF->Ln(2);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_system_specification"), 0, 2, 'L',true);
            $this->PDF->SetTextColor(0,0,0);
            $this->PDF->SetFont('','');
            $this->PDF->Ln(1);
            
            // Setting Defaults
            $this->PDF->Cell(0,0,$this->translate("virtualservers_system") , 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_os_system"), 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_cores").$virtualServer->core, 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_memory").Helpers::formatBytesHelper($virtualServer->memory*1024*1024), 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_discspace").Helpers::formatBytesHelper($virtualServer->space*1024*1024*1024), 0, 2, '',false);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_description"), 0, 2, '',false);
            
            // Getting all IPobject for the Server
            $ipobjects = $virtualServer->getIpObjects();
            
            $this->PDF->SetTextColor(255,255,255);
            $this->PDF->SetFont('','B');
            $this->PDF->Ln(2);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_ip_adress"), 0, 2, 'L',true);
            $this->PDF->SetTextColor(0,0,0);
            $this->PDF->SetFont('','');
            $this->PDF->Ln(1);
            foreach($ipobjects as $ip){
                $this->PDF->Cell(0,0,"IP: " .$ip->value1, 0, 2, '',false);     
            }
            
            $this->PDF->SetTextColor(255,255,255);
            $this->PDF->SetFont('','B');
            $this->PDF->Ln(2);
            $this->PDF->Cell(0,0,$this->translate("virtualservers_comment"), 0, 2, 'L',true);
            $this->PDF->SetTextColor(0,0,0);
            $this->PDF->SetFont('','');
            $this->PDF->Ln(1);
            $this->PDF->MultiCell(0,0,$virtualServer->getDescription(),0,'L');
            
            // Dispaly the PDF on the monitor
            $this->PDF->Output($virtualServer->getName().'.pdf', 'I');
            die();
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->redirectToTableSlideDataAction();
            return;
        }
    }
}