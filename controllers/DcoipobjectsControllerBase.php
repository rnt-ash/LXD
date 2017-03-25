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

use RNTForest\ovz\models\Dcoipobjects;
use RNTForest\ovz\forms\DcoipobjectsForm;
use RNTForest\ovz\models\VirtualServers;

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
        $session = $this->session->get("DcoipobjectsForm");
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
        if(is_a($item,'\RNTForest\ovz\forms\DcoipobjectsForm')){
            // Get item from form
            $this->view->form = $item;
        } else {
            // Get item from Database
            $item = Dcoipobjects::findFirstByid($item);
            if (!$item) {
                $message = $this->translate("ipobjects_item_not_found");
                $this->flash->error($message);
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
            $item = new \RNTForest\ovz\models\Dcoipobjects;
        }else{
            $item = Dcoipobjects::findFirstById($id);
            if (!$item) {
                $message = $this->translate("ipobjects_item_not_exist");
                $this->flashSession->error($message);
                return $this->forwardToOrigin();
            }
        }

        // validate FORM
        $form = new \RNTForest\ovz\forms\DcoipobjectsForm();

        $data = $this->request->getPost();
        if (!$form->isValid($data, $item)) {
            return $this->forwardToEditAction($form);
        }

        // save data
        if ($item->save() === false) {
            // fetch all messages from model
            $messages = array();
            foreach ($item->getMessages() as $message) {
                $form->appendMessage(new \Phalcon\Validation\Message($message->getMessage(),$message->getField()));
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
                $message = $this->translate("ipobjects_ip_conf_failed");
                $this->flashSession->warning($message.$error);
        }

        // clean up
        $form->clear();
        $message = $this->translate("ipobjects_ip_success");
        $this->flashSession->success($message);
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
        $dcoipobject = Dcoipobjects::findFirstByid($id);
        if (!$dcoipobject) {
            $message = $this->translate("ipobjects_ip_not_found");
            $this->flashSession->error($message);
            return $this->forwardToOrigin();
        }

        // configure ip on virtual servers
        if(!is_null($dcoipobject->getVirtualServersId()) && $dcoipobject->getAllocated() >= Dcoipobjects::ALLOC_ASSIGNED){
            $error = $this->configureAllocatedIpOnVirtualServer($dcoipobject, 'del');
            if(!empty($error))
                $message = $this->translate("ipobjects_ip_conf_failed");
                $this->flashSession->warning($message.$error);
        }
        
        // try to delete
        if (!$dcoipobject->delete()) {
            foreach ($dcoipobject->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->forwardToOrigin();
        }

        // sucess
        $message = $this->translate("ipobjects_ip_delete_success");
        $this->flashSession->success($message);
        return $this->forwardToOrigin();
    }

    /**
    * makes an IP Object to main
    * 
    * @param integer $id
    */
    public function makeMainAction($id){
        $id = $this->filter->sanitize($id, "int");
        $dcoipobject = Dcoipobjects::findFirst($id);
        if (!$dcoipobject) {
            $message = $this->translate("ipobjects_ip_not_found");
            $this->flashSession->error($message);
            return $this->forwardToOrigin();
        }
        
        if ($dcoipobject->getType() != Dcoipobjects::TYPE_IPADDRESS){
            $message = $this->translate("ipobjects_ip_adress");
            $this->flashSession->error($message);
            return $this->forwardToOrigin();
        }
        
        if ($dcoipobject->getAllocated() == Dcoipobjects::ALLOC_RESERVED){
            $message = $this->translate("ipobjects_ip_assigned");
            $this->flashSession->error($message);
            return $this->forwardToOrigin();
        }
        
        if ($this->setMainIP($dcoipobject))
            $message = $this->translate("ipobjects_address_is_now_main",array("address"=>$dcoipobject->toString()));
            $this->flashSession->success($message);
            
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
        $phql="UPDATE \\RNTForest\\ovz\\models\\Dcoipobjects SET main = 0 ".
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
        $virtualServer = VirtualServers::findFirst($ip->getVirtualServersId());
        if (!$virtualServer){
            $message = $this->translate("virtualserver_does_not_exist");
            return $message . $item->virtual_servers_id;
        }
        
        if($virtualServer->getOvz() != 1){
            $message = $this->translate("virtualserver_not_ovz_integrated");
            return $message;
        }
        
        // execute ovz_modify_vs job        
        $push = $this->getPushService();
        if($op == 'add'){
            $config = array("ipadd"=>$ip->getValue1());
        }else{
            $config = array("ipdel"=>$ip->getValue1());
        }
        
        $params = array('UUID'=>$virtualServer->getOvzUuid(),'CONFIG'=>$config,);
        $job = $push->executeJob($virtualServer->PhysicalServers,'ovz_modify_vs',$params);
        if(!$job || $job->getDone()==2){
            $message = $this->translate("virtualserver_job_failed"); 
            return $message.$job->getError();
        }

        // save new ovz settings
        $settings = $job->getRetval(true);
        $virtualServer->setOvzSettings($job->getRetval());
        VirtualServersControllerBase::virtualServerSettingsAssign($virtualServer,$settings);

        if ($virtualServer->save() === false) {
            $messages = $virtualServer->getMessages();
            foreach ($messages as $message) {
                $this->flashSession->warning($message);
            }
            $message = $this->translate("virtualserver_update_failed");
            return $message .$virtualServer->getName();
        }
        
        // change allocated
        $ip->setAllocated(Dcoipobjects::ALLOC_AUTOASSIGNED);
        if ($ip->update() === false){
            $messages = $ip->getMessages();
            foreach ($messages as $message) {
                $this->flashSession->error($message);
            }
           $message = $this->translate("ipobjects_ip_update_failed");
            return $message;
        }
    }
    
}
