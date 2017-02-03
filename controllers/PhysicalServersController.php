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
 
class PhysicalServersController extends TableSlideBase
{
    protected function getSlideDataInfo() {
        return array(
            "controller" => "physical_servers",
            "action" => "slidedata",
            "slidenamefield" => "name",
            "slidenamefielddescription" => "Servername",
            "scope" => "",
            "order" => "name",
            "orderdir" => "ASC",
            "filters" => array(),
            "page" => 1,
            "limit" => 10,
        );
    }
    
    protected function filterSlideItems($items,$level) { 
        // Alle Filter abholen
        if($this->request->has('filterAll')){
            $oldfilter = $this->slideDataInfo['filters']['filterAll'];
            $this->slideDataInfo['filters']['filterAll'] = $this->request->get("filterAll", "string");
            if($oldfilter != $this->slideDataInfo['filters']['filterAll']) $this->slideDataInfo['page'] = 1;
        }

        // Filter anwenden        
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
        $this->simpleview->ovzSetting = json_decode($item->getOvzSettings(),true);
        $content .= $this->simpleview->render("partials/ovz/physical_servers/slideDetail.volt");
        return $content;
    }

    /**
    * dummy method only for IDE auto completion purpose
    * 
    * @return Push
    */
    protected function getPushService(){
        return $this->di['push'];
    }
    
    /**
    * Update OVZ settings
    * 
    * @param int $serverId
    */
    public function ovzHostInfoAction($serverId){
        // get VirtualServer
        try{
            // sanitize parameters
            $serverId = $this->filter->sanitize($serverId, "int");

            // find virtual server
            $physicalServer = PhysicalServers::findFirst($serverId);
            if (!$physicalServer) throw new Exception("Physical Server does not exist: " . $serverId);

            // not ovz enabled
            if(!$physicalServer->getOvz()) throw new ErrorException("Server ist not OVZ enabled!");

            // execute ovz_host_info job        
            $push = $this->getPushService();
            $job = $push->executeJob($physicalServer,'ovz_host_info',$params,'PhysicalServers:'.$physicalServer->getId());
            if(!$job || $job->getDone()==2) throw new Exception("Job (ovz_host_info) executions failed!");

            // save settings
            $settings = $job->getRetval(true);
            $physicalServer->setOvzSettings($job->getRetval());
            if ($physicalServer->save() === false) {
                $messages = $physicalServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                throw new Exception("Update Virtual Server (".$physicalServer->getName().") failed.");
            }

            // success
            $this->flashSession->success("Settings successfully updated");

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("physical_servers/slidedata");
    }
    
    /**
    * checks before delete
    * 
    * @param PhysicalServers $physicalServer
    */
    public function preDelete($physicalServer){
        // search for virtual servers
        if($physicalServer->virtualServers->count() >= 1){
            $this->flashSession->error("Please remove virtual servers first!");
            return false;
        }
        
        // delete IP Objects
        foreach($physicalServer->dcoipobjects as $dcoipobject){
            if(!$dcoipobject->delete()){
                foreach ($dcoipobject->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                return false;
            }
        }
        return true;
    }
    

    public function connectFormAction($item){
        if(is_a($item,'OvzConnectorForm')){
            // Get item from form
            $this->view->form = $item;
        } else {
            $connectorFormFields = new OvzConnectorFormFields();
            $connectorFormFields->physical_servers_id = $item;
            $this->view->form = new OvzConnectorForm($connectorFormFields); 
        }
    }

    public function connectAction(){
        try{
            // POST request?
            if (!$this->request->isPost()) 
                return $this->redirectTo("physical_servers/slidedata");

            // validate FORM
            $form = new OvzConnectorForm;
            $item = new OvzConnectorFormFields();
            $data = $this->request->getPost();
            if (!$form->isValid($data, $item)) {
                return $this->dispatcher->forward([
                    'action' => 'connectForm',
                    'params' => [$form],
                ]);
            }
            $phys = PhysicalServers::findFirstById($data['physical_servers_id']);
            if(!$phys) throw new Exception("Physical Server not found!");
            $connector = new OvzConnector($phys,$data['username'],$data['password']);
            $connector->go();
            
            $this->flashSession->success("OVZ connecting to ".$phys->getFqdn()." was successfull.");
            $this->flashSession->warning("It's strongly recommended to restart the server after connecting!");
        }catch(Exception $e){
            $this->flashSession->error("OVZ connecting failed: ".$e->getMessage());
            $this->logger->error($e->getMessage());
        }
        $this->redirecToTableSlideDataAction();
    }
    
    /**
    * Adds an IP Object to the Server
    * 
    * @param integer $id primary key of the virtual Server
    * 
    */
    public function addIpObjectAction($id){

        // store in session
        $this->session->set("DcoipobjectsValidator", array(
            "op" => "new",
            "physical_servers_id" => intval($id),
            "origin" => array(
                'controller' => 'physical_servers',
                'action' => 'slidedata',
            )
        ));

        $dcoipobjectsForm = new DcoipobjectsForm(new Dcoipobjects());
        
        return $this->dispatcher->forward([
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
        $this->session->set("DcoipobjectsValidator", array(
            "op" => "edit",
            "origin" => array(
                'controller' => 'physical_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
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
        $this->session->set("DcoipobjectsValidator", array(
            "op" => "delete",
            "origin" => array(
                'controller' => 'physical_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
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
        $this->session->set("DcoipobjectsValidator", array(
            "origin" => array(
                'controller' => 'physical_servers',
                'action' => 'slidedata',
            )
        ));

        return $this->dispatcher->forward([
            'controller' => 'dcoipobjects',
            'action' => 'makeMain',
            'params' => [$ipobject],
        ]);
    }
    
    
}

/**
* helper class
*/
class OvzConnectorFormFields{
    public $physical_servers_id = 0;
    public $username = "";
    public $password = "";
}
