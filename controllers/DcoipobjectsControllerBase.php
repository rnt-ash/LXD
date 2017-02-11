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

class DcoipobjectsControllerBase extends \RNTForest\core\controllers\ControllerBase
{

    /**
    * helper method. Forward to tableData Action
    * 
    */
    protected function forwardToEditAction($form){
        return $this->dispatcher->forward([
            'action' => 'edit',
            'params' => [$form],
        ]);
    }

    /**
    * helper method. Forward to origin
    * 
    */
    protected function forwardToOrigin(){
        $session = $this->session->get("DcoipobjectsValidator");
        return $this->dispatcher->forward($session['origin']);
    }


    /**
    * Edits a item
    * need an id of the item or a form-object
    *
    * @param mixed $item integer or Phalcon\Forms\Form
    */
    public function editAction($item)
    {
        if(is_a($item,'DcoipobjectsForm')){
            // Get item from form
            $this->view->form = $item;
        } else {
            // Get item from Database
            $item = ($this->getAppNs().'models\Dcoipobjects')::findFirstByid($item);
            if (!$item) {
                $this->flash->error("item was not found");
                return $this->forwardToOrigin();
            }
            $this->view->form = new DcoipobjectsForm($item);
        }
    }

    public function cancelAction(){
        $this->forwardToOrigin();
    }
    
    
    /**
    * Saves a item
    * 
    */
    public function saveAction()
    {
        // POST request?
        if (!$this->request->isPost()) 
            return $this->forwardToOrigin();

        // Edit or new Record
        $id = $this->request->getPost("id", "int");
        if(empty($id)){
            $item = new $this->getAppNs().'models\Dcoipobjects';
        }else{
            $item = ($this->getAppNs().'models\Dcoipobjects')::findFirstById($id);
            if (!$item) {
                $this->flashSession->error("Item does not exist");
                return $this->forwardToOrigin();
            }
        }

        // validate FORM
        $form = new DcoipobjectsForm();

        $data = $this->request->getPost();
        if (!$form->isValid($data, $item)) {
            return $this->forwardToEditAction($form);
        }

        // save data
        if ($item->save() === false) {
            // fetch all messages from model
            $messages = array();
            foreach ($item->getMessages() as $message) {
                $form->appendMessage(new Phalcon\Validation\Message($message->getMessage(),$message->getField()));
            }            
            return $this->forwardToEditAction($form);
        }
        
        // set main IP
        if ($item->getMain())
            $this->setMainIP($item);
            
        // configure ip on virtual servers
        if(!is_null($item->getVirtualServersId()) && $item->getAllocated() >= Dcoipobjects::ALLOC_ASSIGNED){
            $error = $this->configureAllocatedIpOnVirtualServer($item, 'add');
            if(!empty($error))
                $this->flashSession->warning("Configure IP on virtual server failed: ".$error);
        }

        // clean up
        $form->clear();
        $this->flashSession->success("IP Address was updated successfully");
        $this->forwardToOrigin();
    }

    /**
    * Deletes an IP Object
    *
    * @param integer $id
    */
    public function deleteAction($id)
    {
        // find item
        $id = $this->filter->sanitize($id, "int");
        $dcoipobject = ($this->getAppNs().'models\Dcoipobjects')::findFirstByid($id);
        if (!$dcoipobject) {
            $this->flashSession->error("IP Object was not found");
            return $this->forwardToOrigin();
        }

        // configure ip on virtual servers
        if(!is_null($dcoipobject->getVirtualServersId()) && $dcoipobject->getAllocated() >= Dcoipobjects::ALLOC_ASSIGNED){
            $error = $this->configureAllocatedIpOnVirtualServer($dcoipobject, 'del');
            if(!empty($error))
                $this->flashSession->warning("Configure IP on virtual server failed: ".$error);
        }
        
        // try to delete
        if (!$dcoipobject->delete()) {
            foreach ($dcoipobject->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->forwardToOrigin();
        }

        // sucess
        $this->flashSession->success("IP Object was deleted successfully");
        return $this->forwardToOrigin();
    }

    /**
    * makes an IP Object to main
    * 
    * @param integer $id
    */
    public function makeMainAction($id){
        $id = $this->filter->sanitize($id, "int");
        $dcoipobject = ($this->getAppNs().'models\Dcoipobjects')::findFirst($id);
        if (!$dcoipobject) {
            $this->flashSession->error("IP Object was not found");
            return $this->forwardToOrigin();
        }
        
        if ($dcoipobject->getType() != Dcoipobjects::TYPE_IPADDRESS){
            $this->flashSession->error("IP Object must be an address");
            return $this->forwardToOrigin();
        }
        
        if ($dcoipobject->getAllocated() == Dcoipobjects::ALLOC_RESERVED){
            $this->flashSession->error("IP Object must be assigned");
            return $this->forwardToOrigin();
        }
        
        if ($this->setMainIP($dcoipobject))
            $this->flashSession->success("IP Object is now main");
            
        return $this->forwardToOrigin();
    }

    /**
    * marks an IP as main and all others to not main
    * 
    * @param Dcoipobjects $ip
    */
    protected function setMainIP($ip){

        // this ip to main
        $ip->setMain(1);
        if ($ip->update() === false){
            $messages = $ip->getMessages();
            foreach ($messages as $message) {
                $this->flashSession->error($message);
            }
            return false;
        }

        // all other IPs to not main  
        $coId = $ip->getColocationsId();
        $psId = $ip->getPhysicalServersId();
        $vsId = $ip->getVirtualServersId();
        $phql="UPDATE Dcoipobjects SET main = 0 ".
                "WHERE allocated >= 2 ".
                "AND id != ".$ip->getId()." ".
                "AND colocations_id ".(is_null($coId)?"IS NULL":"=".$coId)." ".
                "AND physical_servers_id ".(is_null($psId)?"IS NULL":"=".$psId)." ".
                "AND virtual_servers_id ".(is_null($vsId)?"IS NULL":"=".$vsId)." ";
        $this->modelsManager->executeQuery($phql);

        return true;
    }
    
    /**
    * Configure IP on virtual server
    *     
    * @param DCObject $dco 
    * @throws Exceptions
    */
    protected function configureAllocatedIpOnVirtualServer(Dcoipobjects $ip, $op='add'){

        // find virtual server
        $virtualServer = ($this->getAppNs().'models\VirtualServers')::findFirst($ip->getVirtualServersId());
        if (!$virtualServer) 
            return "Virtual Server does not exist: " . $item->virtual_servers_id;
        
        if($virtualServer->getOvz() != 1)
            return "Virtual Server is not OVZ integrated";
        
        // execute ovz_modify_vs job        
        $push = $this->getPushService();
        if($op == 'add')
            $config = array("ipadd"=>$ip->getValue1());
        else
            $config = array("ipdel"=>$ip->getValue1());
        
        $params = array('UUID'=>$virtualServer->getOvzUuid(),'CONFIG'=>$config,);
        $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_modify_vs',$params);
        if(!$job || $job->getDone()==2) 
            return "Job (ovz_modify_vs) executions failed! Error: ".$job->getError();

        // save new ovz settings
        $settings = $job->getRetval(true);
        $virtualServer->setOvzSettings($job->getRetval());
        VirtualServersController::assignSettings($virtualServer,$settings);

        if ($virtualServer->save() === false) {
            $messages = $virtualServer->getMessages();
            foreach ($messages as $message) {
                $this->flashSession->warning($message);
            }
            return "Update Virtual Server (".$virtualServer->getName().") failed.";
        }
        
        // change allocated
        $ip->setAllocated(Dcoipobjects::ALLOC_AUTOASSIGNED);
        if ($ip->update() === false){
            $messages = $ip->getMessages();
            foreach ($messages as $message) {
                $this->flashSession->error($message);
            }
            return "Update IP Object failed!";
        }
    }
    
}
