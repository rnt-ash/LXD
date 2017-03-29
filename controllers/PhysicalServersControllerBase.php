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

use RNTForest\ovz\models\PhysicalServers;
use RNTForest\ovz\forms\OvzConnectorForm;
use RNTForest\ovz\services\OvzConnector;
use RNTForest\ovz\models\IpObjects;
use RNTForest\ovz\forms\IpObjectsForm;

class PhysicalServersControllerBase extends \RNTForest\core\controllers\TableSlideBase
{
    protected function getSlideDataInfo() {
        $scope = $this->permissions->getScope('physical_servers','general');
        $scopeQuery = "";
        $joinQuery = NULL;
        if ($scope == 'customers'){
            $scopeQuery = "customers_id = ".$this->session->get('auth')['customers_id'];
        } else if($scope == 'partners'){
            $scopeQuery = 'RNTForest\ovz\models\PhysicalServers.customers_id = '.$this->session->get('auth')['customers_id'];
            $scopeQuery .= ' OR RNTForest\core\models\CustomersPartners.partners_id = '.$this->session->get('auth')['customers_id'];
            $joinQuery = array('model'=>'RNTForest\core\models\CustomersPartners',
                'conditions'=>'RNTForest\ovz\models\PhysicalServers.customers_id = RNTForest\core\models\CustomersPartners.customers_id',
                'type'=>'LEFT');
        }

        return array(
            "type" => "slideData",
            "model" => '\RNTForest\ovz\models\PhysicalServers',
            "form" => '\RNTForest\ovz\forms\PhysicalServersForm',
            "controller" => "physical_servers",
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

    public function getMyCustomers(){
        $scope = $this->permissions->getScope("physical_servers","filter_customers");
        if($scope == "partners"){
            $partners = \RNTForest\core\models\CustomersPartners::find("partners_id = ".$this->session->get('auth')['customers_id']);
            $customer_ids[] = $this->session->get('auth')['customers_id'];
            foreach($partners as $partner){
                $customer_ids[] = $partner->getCustomersId();
            }
            $conditions = "id in (".implode(',',$customer_ids).")";
        } elseif($scope == "*") {
            $conditions = "";
        }else{
            // all other scopes
            return array();
        }

        $resultset = \RNTForest\core\models\Customers::find(["conditions" => $conditions, "order" => "company,lastname,firstname"]);
        $message = self::translate("physicalserver_filter_all_customers");
        $customers = array(0 => $message);
        foreach($resultset as $customer){
            $customers[$customer->id] = $customer->printAddressText();
        }
        return $customers;

    }

    public function getMyColocations(){
        $scope = $this->permissions->getScope("physical_servers","filter_colocations");
        if($scope == "partners"){
            $partners = \RNTForest\core\models\CustomersPartners::find("partners_id = ".$this->session->get('auth')['customers_id']);
            $customer_ids[] = $this->session->get('auth')['customers_id'];
            foreach($partners as $partner){
                $customer_ids[] = $partner->getCustomersId();
            }
            $conditions = "customers_id in (".implode(',',$customer_ids).")";
        } elseif($scope == "*") {
            $conditions = "";
        }else{
            // all other scopes
            return array();
        }

        $resultset = \RNTForest\ovz\models\Colocations::find(["conditions" => $conditions, "order" => "name"]);
        $message = self::translate("physicalserver_filter_all_colocations");
        $colocations = array(0 => $message);
        foreach($resultset as $colocation){
            $colocations[$colocation->id] = $colocation->name;
        }
        return $colocations;

    }

    protected function prepareSlideFilters($items,$level) { 

        // put resultsets to the view
        $this->view->customers = $this->getMyCustomers();
        $this->view->colocations = $this->getMyColocations();

        // Alle Filter abholen
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

        if($this->request->has('filterColocations')){
            $oldfilter = $this->slideDataInfo['filters']['filterColocations'];
            $this->slideDataInfo['filters']['filterColocations'] = $this->request->get("filterColocations", "int");
            if($oldfilter != $this->slideDataInfo['filters']['filterColocations']) $this->slideDataInfo['page'] = 1;
        }
    }

    protected function isValidSlideFilterItem($physicalServer,$level){
        if(!empty($this->slideDataInfo['filters']['filterAll'])){ 
            if(strpos(strtolower($physicalServer->name),strtolower($this->slideDataInfo['filters']['filterAll']))===false)
                return false;
        }
        if(!empty($this->slideDataInfo['filters']['filterCustomers'])){ 
            if($physicalServer->customers_id != $this->slideDataInfo['filters']['filterCustomers'])
                return false;
        }
        if(!empty($this->slideDataInfo['filters']['filterColocations'])){ 
            if($physicalServer->colocations_id != $this->slideDataInfo['filters']['filterColocations'])
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
    * @return \RNTForest\core\services\Push
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

            // find physical server
            $physicalServer = PhysicalServers::findFirst($serverId);
            $message = $this->translate("physicalserver_doesn_not_exist");
            if (!$physicalServer) throw new \Exception($message . $serverId);

            // not ovz enabled
            $message = $this->translate("physicalserver_not_ovz_enabled");
            if(!$physicalServer->getOvz()) throw new \Exception($message);

            // execute ovz_host_info job        
            $push = $this->getPushService();
            $job = $push->executeJob($physicalServer,'ovz_host_info',array());
            $message =  $this->translate("physicalserver_job_failed");
            if(!$job || $job->getDone()==2) throw new \Exception($message."(ovz_host_info) !");

            // save settings
            $settings = $job->getRetval(true);
            $physicalServer->setOvzSettings($job->getRetval());
            if ($physicalServer->save() === false) {
                $messages = $physicalServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                $message = $this->translate("physicalserver_update_failed");
                throw new \Exception($message . $physicalServer->getName());
            }

            // success
            $message = $this->translate("physicalserver_update_success");
            $this->flashSession->success($message);

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
        }
        // go back to slidedata view
        $this->redirectTo("physical_servers/slidedata");
    }

    /**
    * Update OVZ host statistics
    * 
    * @param int $serverId
    */
    public function ovzHostStatisticsInfoAction($serverId){
        // get VirtualServer
        try{
            // sanitize parameters
            $serverId = $this->filter->sanitize($serverId, "int");

            // find physical server
            $physicalServer = PhysicalServers::findFirst($serverId);
            $message = $this->translate("physicalserver_doesn_not_exist");
            if (!$physicalServer) throw new \Exception($message . $serverId);

            // not ovz enabled
            $message = $this->translate("physicalserver_not_ovz_enabled");
            if(!$physicalServer->getOvz()) throw new \Exception($message);

            // execute ovz_hoststatistics_info job        
            $push = $this->getPushService();
            $job = $push->executeJob($physicalServer,'ovz_hoststatistics_info',array());
            $message =  $this->translate("physicalserver_job_failed");
            if(!$job || $job->getDone()==2) throw new \Exception($message."(ovz_hoststatistics_info) !");

            // save statistics
            $settings = $job->getRetval(true);
            $physicalServer->setOvzStatistics($job->getRetval());
            if ($physicalServer->save() === false) {
                $messages = $physicalServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                $message = $this->translate("physicalserver_update_failed");
                throw new \Exception($message . $physicalServer->getName());
            }

            // success
            $message = $this->translate("physicalserver_update_success");
            $this->flashSession->success($message);

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
            $message = $this->translate("physicalserver_remove_server_first");
            $this->flashSession->error($message);
            return false;
        }

        // delete IP Objects
        foreach($physicalServer->ipobjects as $ipobject){
            if(!$ipobject->delete()){
                foreach ($ipobject->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                return false;
            }
        }
        return true;
    }

    /**
    * Show form for ovz connector    
    * 
    * @param integer $physicalServersId
    */
    public function ovzConnectorAction($physicalServersId){
        // sanitize
        $physicalServersId = $this->filter->sanitize($physicalServersId,"int");

        // get physical server object
        $physicalServer = PhysicalServers::findFirstByid($physicalServersId);
        if (!$physicalServer) {
            $message = $this->translate("physicalserver_does_not_exist");
            $this->flashSession->error($message);
            return $this->forwardToTableSlideDataAction();
        }
        
        // check permissions
        if(!$this->permissions->checkPermission('physical_servers', 'general', array('item' => $physicalServer))){
            return $this->forwardTo401();
        }   
        
        // prepare form fields
        $connectorFormFields = new OvzConnectorFormFields();
        $connectorFormFields->physical_servers_id = $physicalServersId;
        
        // call view
        $this->view->form = new OvzConnectorForm($connectorFormFields); 
        $this->view->pick("physical_servers/ovzConnectorForm");
    }

    /**
    * Connect OVZ Server
    * 
    */
    public function ovzConnectorExecuteAction(){
        try{
            // POST request?
            if (!$this->request->isPost()) 
                return $this->redirectTo("physical_servers/slidedata");

            // validate FORM
            $form = new OvzConnectorForm;
            $item = new OvzConnectorFormFields();
            $data = $this->request->getPost();
            if (!$form->isValid($data, $item)) {
                $this->view->form = $form; 
                $this->view->pick("physical_servers/ovzConnectorForm");
                return; 
            }
            
            // sanitize
            $physicalServersId = $this->filter->sanitize($data['physical_servers_id'],"int");
            
            // Business Logic
            $physicalServer = PhysicalServers::findFirstByid($physicalServersId);
            if (!$physicalServer){
                $message = $this->translate("physicalserver_does_not_exist");
                $this->flashSession->error($message);
                $this->view->form = $form; 
                $this->view->pick("physical_servers/ovzConnectorForm");
                return;
            }
            
            // check permissions
            if(!$this->permissions->checkPermission('physical_servers', 'general', array('item' => $physicalServer))){
                return $this->forwardTo401();
            }
            
            // connect
            $connector = new OvzConnector($physicalServer,$data['username'],$data['password']);
            $connector->go();

            // success message
            $message = $this->translate("physicalserver_connection_success");
            $this->flashSession->success($message.$physicalServer->getFqdn());
            
            // warning message
            $message = $this->translate("physicalserver_connection_restart");
            $this->flashSession->warning($message);
        }catch(\Exception $e){
            $message = $this->translate("physicalserver_connection_failed");
            $this->flashSession->error($message.$e->getMessage());
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
    public function ipObjectAddAction($id){

        // store in session
        $this->session->set("IpObjectsForm", array(
            "op" => "new",
            "server_class" => '\RNTForest\ovz\models\PhysicalServers',
            "server_id" => intval($id),
            "origin" => array(
                'controller' => 'physical_servers',
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
                'controller' => 'physical_servers',
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
                'controller' => 'physical_servers',
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
                'controller' => 'physical_servers',
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


}

/**
* helper class
*/
class OvzConnectorFormFields{
    public $physical_servers_id = 0;
    public $username = "";
    public $password = "";
}
