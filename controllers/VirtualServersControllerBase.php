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
            'NAME'=>$virtualServer->getName(),
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
            $this->virtualServerSettingsSave($job,$virtualServer);

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
            // Workaround: if a Byte suffix is given convert Space to GB and Memory to MB
            /*if(!is_numeric($virtualServer->getSpace())){
                $virtualServer->setSpace(Helpers::convertBytesToGibiBytes(Helpers::convertToBytes($virtualServer->getSpace())));
            }
            if(!is_numeric($virtualServer->getMemory())){
                $virtualServer->setMemory(Helpers::convertBytesToMibiBytes(Helpers::convertToBytes($virtualServer->getMemory())));    
            }*/
            
            // validate physical server and check permissions
            $physicalServer = PhysicalServers::findFirst($virtualServer->getPhysicalServersId());
            if(!$physicalServer){
                $message = $this->translate("virtualserver_physicalserver_required");
                $form->appendMessage(new \Phalcon\Validation\Message($message,'physical_servers_id'));
                return false;
            }
            $this->tryCheckPermission('physical_servers','general',array('item' => $physicalServer));
            
            // only check if physical server is ovz enabled if a CT or VM is about to be created
            $vstype = $this->session->get("VirtualServersForm")['vstype'];
            $op = $this->session->get("VirtualServersForm")['op'];
            if($vstype == 'CT') $this->tryCheckLxdEnabled($physicalServer);
            
            // check if selected ostemplate exists on selected PS
            /*if($op == 'new' && $vstype == 'CT'){
                // get available ostemplates from physical server
                $ostemplates = json_decode($virtualServer->PhysicalServers->getOvzOstemplates(),true);
                if(empty($ostemplates)){
                    $message = new \Phalcon\Validation\Message($this->translate("virtualserver_no_ostemplates_found"),"ostemplate");            
                    $form->appendMessage($message);
                    return false;
                }else{
                    if(!array_search($virtualServer->ostemplate,array_column($ostemplates,'name'))){
                        $message = new \Phalcon\Validation\Message($this->translate("virtualserver_ostemplate_not_valid"),"ostemplate");            
                        $form->appendMessage($message);
                        return false;
                    }
                }
            }*/
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

                $params = array(
                    "NAME" => $virtualServer->getName(),
                    "CPUS" => $virtualServer->getCore(),
                    "RAM" => $virtualServer->getMemory(),
                    "DISKSPACE" => $virtualServer->getSpace(),
                    "STORAGEPOOL" => $this->config->lxd['defaultStoragePool'],
                    "IMAGEALIAS" => $this->config->lxd['defaultImage']
                );
                
                if(!$virtualServer->update()){
                    $message = $this->translate("virtualserver_update_server_failed");
                    throw new \Exception($message);
                }

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
                $this->virtualServerSettingsSave($job,$virtualServer);
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
                if(stripos($snapshot['Name'],"Replica")){
                    $snapshot['Removable'] = 0; 
                }else {
                    $snapshot['Removable'] = 1;
                }

                // convert the date
                $snapshot['Date'] = date("d.m.Y H:i:s",strtotime($snapshot['Date']));

                // get all child snapshots
                $snapshot['Childs'] = $this->ovzSnapshotGetChilds($snapshot['UUID'],$snapshots);

                // is a childsnapshot mounted?
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
            $this->virtualServerSettingsSave($job,$virtualServer);

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

            // execute ovz_all_info
            // no pending needed because job reads only
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_all_info',$params);
            $this->trySaveAllInfo($job,$virtualServer);

            // get OVZ Settings
            $ovzSettings = json_decode($virtualServer->getOvzSettings(),true);

            // fill form fields
            $virtualServersConfigureFormFields = new VirtualServersConfigureFormFields();
            $virtualServersConfigureFormFields->virtual_servers_id = $virtualServer->getId();
            $virtualServersConfigureFormFields->dns = $ovzSettings['DNS Servers'];
            $virtualServersConfigureFormFields->cores = $virtualServer->getCore();
            $virtualServersConfigureFormFields->memory = Helpers::formatBytesHelper(Helpers::convertToBytes($virtualServer->getMemory()."MB"));
            $virtualServersConfigureFormFields->diskspace = Helpers::formatBytesHelper(Helpers::convertToBytes($virtualServer->getSpace()."GB"));
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

            // execute ovz_all_info
            // no pending needed because job is reads only
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_all_info',$params);
            $this->trySaveAllInfo($job,$virtualServer);

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
            if($memory < Helpers::convertToBytes('512MB')){
                $message1 = $this->translate("virtualserver_min_ram");
                $message = $message1;
                return $this->redirectErrorToVirtualServersConfigure($message,'memory',$form);
            } 

            // check if memory of host is exceeded
            $hostRam = Helpers::convertToBytes($virtualServer->PhysicalServers->getMemory().'MB');
            if($memory > $hostRam){
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
            if($diskspace < Helpers::convertToBytes('20GB')){
                $message1 = $this->translate("virtualserver_min_space");
                $message = $message1;
                return $this->redirectErrorToVirtualServersConfigure($message,'diskspace',$form);
            }
            // check if diskspace of host is exceeded
            $hostDiskspace = Helpers::convertToBytes($virtualServer->PhysicalServers->getSpace().'GB');
            if($diskspace > $hostDiskspace){
                $message1 = $this->translate("virtualserver_max_space");
                $message = $message1.$virtualServer->PhysicalServers->getSpace().' GB)';
                return $this->redirectErrorToVirtualServersConfigure($message,'diskspace',$form);
            }

            // final diskspace in MibiBytes
            $diskspace = Helpers::convertBytesToGibiBytes($diskspace);

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

    private function redirectErrorToVirtualServersConfigure($message,$field,$form){
        $form->appendMessage(new \Phalcon\Validation\Message($message,$field));
        $this->view->form = $form; 
        $this->view->pick("virtual_servers/virtualServersConfigureForm");
        return; 
    }

    /**
    * try to save all infos from ovz_all_info job to virtual server
    * 
    * @param \RNTForest\core\models\Jobs $job
    * @param \RNTForest\ovz\models\VirtualServers $virtualServer
    * 
    * @throws \Exceptions
    */
    public static function trySaveAllInfo($job,$virtualServer){
        // save settings
        $retval = $job->getRetval(true);
        $uuid = $virtualServer->getOvzUuid();

        $virtualServer->setOvzStatistics(json_encode($retval['GuestStatistics'][$uuid]));
        $settings = $retval['GuestInfo'][$uuid];
        $virtualServer->setOvzSettings(json_encode($settings));
        self::virtualServerSettingsAssign($virtualServer,$settings);

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
    * try to save settings from jobs to virtual server
    * 
    * @param \RNTForest\core\models\Jobs $job
    * @param \RNTForest\ovz\models\VirtualServers $virtualServer
    * 
    * @throws \Exceptions
    */
    public static function virtualServerSettingsSave($job,$virtualServer){
        // save lxd settings to the database
        $virtualServer->setLxdSettings(json_encode($job->getRetval(true)['metadata']));
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
        $virtualServer->setMemory(intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['Hardware']['memory']['size_in_mb'])));
        $virtualServer->setSpace(intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['Hardware']['hdd0']['size_in_gb'])));
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
            $this->tryGetOvzAllInfo($virtualServer);
            
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
            // replace whitespaces with underscores because of weak handling of names with whitespaces in potential
            // further individual bash scripts
            $replicaSlave->setName(substr(str_replace(' ', '_', "replica of ".$replicaMaster->getName()),0,50));
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
            // sometimes there is no VSTYPE ?!
            if(empty($replicaSlave->getOvzVstype()))
                throw new \Exception("virtualserver_save_replica_has_no_type. ".json_encode($replicaSlave->toArray()));
            
            $params = array(
                "VSTYPE"=>$replicaSlave->getOvzVstype(),
                "UUID"=>$replicaSlave->getOvzUuid(),
                "NAME"=>$replicaSlave->getName(),
                "OSTEMPLATE"=>$this->config->replica->osTemplate,
                "DISTRIBUTION"=>"",
                "HOSTNAME"=>$replicaSlave->getFqdn(),
                "NAMESERVER"=>$this->config->ovz['defaultNameserver'],
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
            if ($replicaMaster->update() === false){
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

    public function ovzReplicaRunAction($replicaMasterId) {

        // sanitize Parameters
        $replicaMasterId = $this->filter->sanitize($replicaMasterId,"int");

        try{
            // validate (throws exceptions)
            $replicaMaster = VirtualServers::tryFindById($replicaMasterId);
            $this->tryCheckPermission('virtual_servers', 'replicas', array('item' => $replicaMaster));
            $this->tryCheckOvzEnabled($replicaMaster);

            // run replica
            $this->replica->run($replicaMaster);
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
            $this->tryGetOvzAllInfo($replicaMaster);
            if($replicaMaster->getOvzState() == 'running') $this->tryStopVS($replicaMaster);
    
            // check if master and slave is stopped            
            $this->tryGetOvzAllInfo($replicaMaster);
            $replicaMasterState = $replicaMaster->getOvzState();
            if($replicaMasterState != 'stopped'){
                throw new \Exception("virtualserver_replica_master_not_stopped");
            }
    
            $replicaSlave = new VirtualServers;
            $replicaSlave = $replicaMaster->ovzReplicaId;
            $this->tryGetOvzAllInfo($replicaSlave);
            if($replicaSlave->getOvzState() != 'stopped'){
                throw new \Exception("virtualserver_replica_slave_not_stopped");
            }

            // run replica, don't go on until job ist fineshed
            if($job = $this->replica->run($replicaMaster)){
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
            $this->tryGetOvzAllInfo($replicaSlave);
    
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
            $this->tryCheckPermission('virtual_servers', 'change_root_password', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // check last info Update..
            $this->tryGetOvzAllInfo($virtualServer);

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
            $this->tryCheckPermission('virtual_servers', 'change_root_password', array('item' => $virtualServer));
            $this->tryCheckOvzEnabled($virtualServer);

            // execute ovz_set_pwd job        
            // no pending needed
            $params = array(
                'UUID'=>$virtualServer->getOvzUuid(),
                'ROOTPWD'=>$data['password']
            );
            $job = $this->tryExecuteJob($virtualServer->PhysicalServers,'ovz_set_pwd',$params);

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
    * Redirect to the MonJobs Form
    * 
    * @param mixed $virtualServerId
    */
    public function monJobsAddAction($virtualServerId){
        // store in session
        $this->session->set("MonJobsForm", array(
            "op" => "new",
            "server_class" => '\RNTForest\ovz\models\VirtualServers',
            "server_id" => intval($virtualServerId),
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'mon_jobs',
            'action' => 'monJobsAdd',
        ]);
    }
    
    /**
    * Deletes a MonJob
    * 
    * @param mixed $monJobId
    */
    public function monJobsDeleteAction($monJobId){
        // store in session
        $this->session->set("MonJobsForm", array(
            "op" => "delete",
            "server_class" => '\RNTForest\ovz\models\VirtualServers',
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'mon_jobs',
            'action' => 'monJobsDelete',
            'params' => [$monJobId],
        ]);
    }
    
    /**
    * Redirect to edit Form
    * 
    * @param mixed $monJobId
    */
    public function monJobsEditAction($monJobId){
        // store in session
        $this->session->set("MonJobsForm", array(
            "op" => "edit",
            "server_class" => '\RNTForest\ovz\models\VirtualServers',
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'mon_jobs',
            'action' => 'monJobsEdit',
            'params' => [$monJobId],
        ]);
    }
    
    /**
    * Mutes or unmutes a MonJob
    * 
    * @param mixed $monJobId
    */
    public function monJobsMuteAction($monJobId){
        // store in session
        $this->session->set("MonJobsForm", array(
            "op" => "mute",
            "server_class" => '\RNTForest\ovz\models\VirtualServers',
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'mon_jobs',
            'action' => 'monJobsMute',
            'params' => [$monJobId],
        ]);
    }
    
    /**
    * Shows the details of a remote MonJob
    * 
    * @param mixed $monJobId
    */
    public function monJobsDetailsAction($monJobId){
        // store in session
        $this->session->set("MonJobsForm", array(
            "op" => "details",
            "server_class" => '\RNTForest\ovz\models\VirtualServers',
            "origin" => array(
                'controller' => 'virtual_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            "namespace"  => $this->getAppNs()."controllers",
            'controller' => 'mon_jobs',
            'action' => 'monJobsDetails',
            'params' => [$monJobId],
        ]);
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
    * Execute support job task
    * 
    */
    public function supportJobAction(){
        try{
            $this->tryCheckPermission('virtual_servers', 'support');

            $warnings = [];
            
            $virtualServers = VirtualServers::find('ovz_replica < 2');
            foreach($virtualServers as $virtualServer){
                $push = $this->getPushService();
                try{
                    $job = $push->executeJob($virtualServer,'ovz_support_task', []);
                }catch(\Exception $e){ 
                    // go on
                    $warnings[] = $e->getMessage();
                }
            }
            
            // everything Ok
            $message = "virtualserver_support_task_successful";
            $this->flashSession->success($message);
            
            if(!empty($warnings)){
                $warning = implode('; ',$warnings);
                $this->flashSession->warning($warning);
            }

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirectTo("/administration");
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
    
    /**
    * Print Replica stats as PDF
    * 
    * @param mixed $date
    * @return mixed
    * @throws Exceptions
    */
    public function ovzReplicaStatsPDFAction($date){
        try{
            // Check permissions -> only allowed for logins with scope = * on replicas
            if($this->permissions->getScope('virtual_servers', 'replicas') != '*'){
                $message = self::translate("virtualserver_replicapdf_no_permission");
                throw new \Exception($message);
            }
            
            // print the PDF
            $replica = new \RNTForest\ovz\services\Replica($this->getDI());
            $replica->replicaStatsPDFPrint($date);
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->redirectToTableSlideDataAction();
            return;
        }
    }
}