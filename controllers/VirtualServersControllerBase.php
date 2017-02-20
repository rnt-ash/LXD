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

use RNTForest\ovz\models\VirtualServers;
use RNTForest\ovz\models\PhysicalServers;
use Phalcon\Http\Client\Request;

class VirtualServersControllerBase extends \RNTForest\core\controllers\TableSlideBase
{
    protected function getSlideDataInfo() {
        $scope = $this->session->get('auth')['permissions']['virtual_servers']['general']['scope'];
        $scopeQuery = "";
        $joinQuery = NULL;
        if ($scope == 'customers'){
            $scopeQuery = "customers_id = ".$this->session->get('auth')['customers_id'];
        } else if($scope == 'partners'){
            $scopeQuery = 'RNTForest\ovz\models\VirtualServers.customers_id = '.$this->session->get('auth')['customers_id'];
            $scopeQuery .= ' OR RNTForest\core\models\CustomersPartners.partners_id = '.$this->session->get('auth')['customers_id'];
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

    protected function filterSlideItems($items,$level) { 
        // receive all filters
        if($this->request->has('filterAll')){
            $oldfilter = $this->slideDataInfo['filters']['filterAll'];
            $this->slideDataInfo['filters']['filterAll'] = $this->request->get("filterAll", "string");
            if($oldfilter != $this->slideDataInfo['filters']['filterAll']) $this->slideDataInfo['page'] = 1;
        }

        // apply filter
        if(!empty($this->slideDataInfo['filters']['filterAll'])){ 
            $items = $items->filter(
                function($item){
                    if(strpos(strtolower($item->name),strtolower($this->slideDataInfo['filters']['filterAll']))!==false)
                        return $item;
                }
            );
        }
        return $items; 
    }

    protected function renderSlideHeader($item,$level){
        switch($level){
            case 0:
                return $item->name; 
                break;
            default:
                return "invalid level!";
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
    * updates OVZ settings
    * 
    * @param int $serverId
    */
    public function ovzListInfoAction($serverId){

        // sanitize parameters
        $serverId = $this->filter->sanitize($serverId, "int");

        try{
            // find virtual server
            $virtualServer = VirtualServers::findFirst($serverId);
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $serverId);
            
            // not ovz enalbled
            if(!$virtualServer->getOvz()) throw new ErrorException("Server ist not OVZ enabled!");

            // execute ovz_list_info job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_list_info',$params);
            if($job->getDone()==2) throw new \Exception("Job (ovz_list_info) executions failed: ".$job->getError());

            // save settings
            $settings = $job->getRetval(true);
            $virtualServer->setOvzSettings($job->getRetval());
            self::assignSettings($virtualServer,$settings);
            
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                throw new \Exception("Update Virtual Server (".$virtualServer->getName().") failed.");
            }

            // success
            $this->flashSession->success("Settings successfully updated");

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
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
        $virtualServer->setOvzState($settings['State']);
        $virtualServer->setCore(intval($settings['Hardware']['cpu']['cpus']));
        $virtualServer->setMemory(intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['Hardware']['memory']['size'])/1024/1024));
        $virtualServer->setSpace(intval(\RNTForest\core\libraries\Helpers::convertToBytes($settings['Hardware']['hdd0']['size'])/1024/1024/1024));
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
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $serverId);

            // permissions for this virtual server
            if (!$this->isAllowedItem($virtualServer,"changestate")) return $this->forwardTo401();
            
            // not ovz enalbled
            if(!$virtualServer->getOvz()) throw new \Exception("Server ist not OVZ enabled!");

            // execute ovz_start_vs job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_start_vs',$params);
            if($job->getDone()==2) throw new \Exception("Job (ovz_start_vs) executions failed: ".$job->getError());

            // success
            $this->flashSession->success("Starting VS successfully");

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
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $serverId);

            // permissions for this virtual server
            if (!$this->isAllowedItem($virtualServer,"changestate")) return $this->forwardTo401();
            
            // not ovz enalbled
            if(!$virtualServer->getOvz()) throw new \Exception("Server ist not OVZ enabled!");

            // execute ovz_stop_vs job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_stop_vs',$params);
            if($job->getDone()==2) throw new \Exception("Job (ovz_stop_vs) executions failed: ".$job->getError());

            // success
            $this->flashSession->success("Stopping VS successfully");

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
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $serverId);

            // permissions for this virtual server
            if (!$this->isAllowedItem($virtualServer,"changestate")) return $this->forwardTo401();
            
            // not ovz enalbled
            if(!$virtualServer->getOvz()) throw new \Exception("Server ist not OVZ enabled!");

            // execute ovz_restart_vs job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_restart_vs',$params);
            if($job->getDone()==2) throw new \Exception("Job (ovz_restart_vs) executions failed: ".$job->getError());

            // success
            $this->flashSession->success("Restarting VS successfully");

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
            $this->flashSession->error("Virtual server not found.");
            return $this->redirecToTableSlideDataAction();
        }

        // execute ovz_destroy_vs job   
        if($virtualServer->getOvz()){     
            $push = $this->getPushService();
            $params = array("UUID"=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->physicalServers,'ovz_destroy_vs',$params);
            if($job->getDone() == 2){
                $this->flashSession->error("Virtual server destroy job failed: ".$job->getError());
                return $this->redirecToTableSlideDataAction();
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
        $this->flashSession->success("Virtual server destroyed sucessfully.");
        
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
        $physicalServer = PhysicalServers::findFirst("ovz = 1");
        $job = $push->executeJob($physicalServer,'ovz_get_ostemplates',$params);
        if(!$job || $job->getDone()==2) throw new \Exception("Job (ovz_get_ostemplates) executions failed!");
        $retval = $job->getRetval(true);
        $ostemplates = array();
        foreach($retval as $template){
            $ostemplates[$template['name']] = $template['name']." (".$template['lastupdate'].")";
        }
        
        // store in session
        $this->session->set($this->getFormClassName(), array(
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
                $this->flashSession->error("Physical Server does not exist: " . $serverId);
                return false;
            }
            
            // permissions for this PhysicalServer
            if (!$this->isAllowedItem($physicalServer)){
                $this->flashSession->error("Not allowed for this Physical Server");
                return false;
            }
            

            if (!$physicalServer->getOvz()) {
                $this->flashSession->error("Physical Server is not OVZ integrated. ");
                return false;
            }

            // execute ovz_new_vs job        
            $push = $this->getPushService();
            $job = $push->executeJob($physicalServer,'ovz_new_vs',$params);
            if($job->getDone() == 2){
                $this->flashSession->error("Physical Server create job failed: ".$job->getError());
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
            if (!$virtualServer) throw new Exception("Virtual Server does not exist: " . $serverId);
            
            // not ovz enalbled
            if(!$virtualServer->getOvz()) throw new ErrorException("Server ist not OVZ enabled!");

            // execute ovz_list_snapshots job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid());
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_list_snapshots',$params);
            if(!$job || $job->getDone()==2) throw new Exception("Job (ovz_list_snapshots) executions failed!");

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                throw new Exception("Update Virtual Server (".$virtualServer->getName().") failed.");
            }
            
            // success
            $this->flashSession->success("Snapshots successfully updated");

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
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $serverId);
            
            // not ovz enalbled
            if(!$virtualServer->getOvz()) throw new ErrorException("Server ist not OVZ enabled!");
            
            // execute ovz_switch_snapshot job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'SNAPSHOTID'=>$snapshotId);
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_switch_snapshot',$params);
            if(!$job || $job->getDone()==2) throw new \Exception("Job (ovz_switch_snapshot) executions failed!");

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                throw new \Exception("Switch snapshot on server (".$virtualServer->getName().") failed.");
            }
                        
            // success
            $this->flashSession->success("Snapshots successfully updated");
            
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("virtual_servers/slidedata");
    }

    public function snapshotFormAction($item){

        if(is_a($item,'SnapshotForm')){
            // Get item from form
            $this->view->form = $item;
        } else {
            $snapshotFormFields = new SnapshotFormFields();
            $snapshotFormFields->virtual_servers_id = $item;
            $this->view->form = new SnapshotForm($snapshotFormFields);
        }
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
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $item->virtual_servers_id);
            
            // execute ovz_list_snapshots job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'NAME'=>$item->name,'DESCRIPTION'=>$item->description);
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_create_snapshot',$params);
            if(!$job || $job->getDone()==2) throw new \Exception("Job (ovz_create_snapshot) executions failed!");

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                throw new \Exception("Create snapshot on server (".$virtualServer->getName().") failed.");
            }
            
            // success
            $this->flashSession->success("Snapshots successfully updated");
            
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
            if (!$virtualServer) throw new \Exception("Virtual Server does not exist: " . $serverId);
            
            // not ovz enabled
            if(!$virtualServer->getOvz()) throw new ErrorException("Server ist not OVZ enabled!");
            
            // execute ovz_delete_snapshot job        
            $push = $this->getPushService();
            $params = array('UUID'=>$virtualServer->getOvzUuid(),'SNAPSHOTID'=>$snapshotId);
            $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_delete_snapshot',$params);
            if(!$job || $job->getDone()==2) throw new \Exception("Job (ovz_delete_snapshot) executions failed!");

            // save snapshots
            $snapshots = $job->getRetval();
            $virtualServer->setOvzSnapshots($snapshots);
            if ($virtualServer->save() === false) {
                $messages = $virtualServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                throw new \Exception("Deleting snapshot on server (".$virtualServer->getName().") failed.");
            }
            
            // success
            $this->flashSession->success("Snapshots successfully updated");
            
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
            "namespace"  => "RNTForest\\ovz\\controllers",
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
            "namespace"  => "RNTForest\\ovz\\controllers",
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
            "namespace"  => "RNTForest\\ovz\\controllers",
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
            "namespace"  => "RNTForest\\ovz\\controllers",
            'controller' => 'dcoipobjects',
            'action' => 'makeMain',
            'params' => [$ipobject],
        ]);
    }
    
}

/**
* helper class
*/
class SnapshotFormFields{
    public $virtual_servers_id = 0;
    public $name = "";
    public $description = "";
}