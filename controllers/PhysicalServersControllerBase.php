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
use RNTForest\ovz\models\VirtualServers;
use RNTForest\ovz\models\Colocations;
use RNTForest\ovz\forms\OvzConnectorForm;
use RNTForest\ovz\connectors\OvzConnector;
use RNTForest\ovz\models\IpObjects;
use RNTForest\ovz\forms\IpObjectsForm;
use RNTForest\ovz\models\MonJobs;
use RNTForest\core\models\Logins;
use RNTForest\ovz\datastructures\OvzConnectorFormFields;
use RNTForest\core\libraries\Helpers;


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
            "orderdir" => "asc",
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
        // Alle Filter abholen
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

        if($this->request->has('filterColocations')){
            $oldfilter = $this->slideDataInfo['filters']['filterColocations'];
            $this->slideDataInfo['filters']['filterColocations'] = $this->request->get("filterColocations", "int");
            if($oldfilter != $this->slideDataInfo['filters']['filterColocations']) $this->slideDataInfo['page'] = 1;
        }
        
        // put resultsets to the view
        // get colocations from scope
        $scope = $this->permissions->getScope("physical_servers","filter_colocations"); 
        $findParameters = array("order"=>"name");
        $resultset = Colocations::findFromScope($scope,$findParameters);
        
        // create array
        if(!empty($resultset)){
            $colocations = array();
            $colocations[0]['name'] = Colocations::translate("physicalserver_filter_all_colocations");
            $colocations[0]['count'] = '';
            foreach($resultset as $colocation){
                $colocations[$colocation->id]['name'] = $colocation->name;
                $colocations[$colocation->id]['count'] = count($colocation->PhysicalServers)." PS";
                if(!empty($this->slideDataInfo['filters']['filterColocations'])){
                    if($this->slideDataInfo['filters']['filterColocations'] == $colocation->id){
                        $colocations[$colocation->id]['selected'] = 'selected';
                    }
                }
            }
        }
        $this->view->colocations = $colocations;
    }

    protected function isValidSlideFilterItem($physicalServer,$level){
        if(!empty($this->slideDataInfo['filters']['filterAll'])){ 
            if(strpos(strtolower($physicalServer->name),strtolower($this->slideDataInfo['filters']['filterAll']))===false)
                return false;
        } 
        if(!empty($this->slideDataInfo['filters']['filterCustomers_id'])){ 
            if($physicalServer->customers_id != $this->slideDataInfo['filters']['filterCustomers_id'])
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
        $content .= $this->simpleview->render("physical_servers/slideDetail.volt");
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
    * Update OVZ settings and statistics of host and all guests
    * 
    * @param int $serverId
    */
    public function ovzAllInfoAction($serverId){
        // get VirtualServer
        try{
            // sanitize parameters
            $serverId = $this->filter->sanitize($serverId, "int");
            
            // find physical server
            $physicalServer = PhysicalServers::findFirst($serverId);
            $message = $this->translate("physicalserver_does_not_exist");
            if (!$physicalServer) throw new \Exception($message . $serverId);

            // not ovz enabled
            $message = $this->translate("physicalserver_not_ovz_enabled");
            if(!$physicalServer->getOvz()) throw new \Exception($message);

            // execute ovz_all_info job        
            $push = $this->getPushService();
            $job = $push->executeJob($physicalServer,'ovz_all_info',array());
            $message =  $this->translate("physicalserver_job_failed");
            if(!$job || $job->getDone()==2) throw new \Exception($message."(ovz_all_info) !");

            // get infos as array
            $infos = $job->getRetval(true);
            $message =  $this->translate("physicalserver_info_not_valid_array");
            if(!is_array($infos)) throw new \Exception($message);

            // save host settings and statistics
            $physicalServer->setOvzSettings(json_encode($infos['HostInfo']));
            $physicalServer->setOvzStatistics(json_encode($infos['HostStatistics']));
            if ($physicalServer->save() === false) {
                $messages = $physicalServer->getMessages();
                foreach ($messages as $message) {
                    $this->flashSession->warning($message);
                }
                $message = $this->translate("physicalserver_update_failed");
                throw new \Exception($message . $physicalServer->getName());
            }

            // save guest settings and statistics
            if(isset($infos['GuestInfo'])){
                foreach($infos['GuestInfo'] as $key=>$info){
                    // find virtual server
                    $virtualServer = VirtualServers::findFirst("ovz_uuid = '".$key."'");
                    if (!$virtualServer) {
                        $message = $this->translate("virtualserver_does_not_exist");
                        $this->flashSession->warning($message . "UUID: " . $key);
                        continue;
                    }

                    $virtualServer->setOvzSettings(json_encode($infos['GuestInfo'][$key]));
                    $virtualServer->setOvzStatistics(json_encode($infos['GuestStatistics'][$key]));
                    if ($virtualServer->save() === false) {
                        $messages = $virtualServer->getMessages();
                        foreach ($messages as $message) {
                            $this->flashSession->warning($message);
                        }
                        $message = $this->translate("virtualserver_update_failed");
                        throw new \Exception($message . $virtualServer->getName());
                    }
                }
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
    * Does some preSave configurations like convert the bytestrings to MB for memory or GB for diskspace.
    * 
    * @param PhysicalServers $physicalServer
    * @param PhysicalServersForm $form
    */
    protected function preSave($physicalServer,$form){
        try{
            // Workaround: if a Byte suffix is given convert Space to GB and Memory to MB
            if(!is_numeric($physicalServer->getSpace())){
                $physicalServer->setSpace(Helpers::convertBytesToGibiBytes(Helpers::convertToBytes($physicalServer->getSpace())));
            }
            if(!is_numeric($physicalServer->getMemory())){
                $physicalServer->setMemory(Helpers::convertBytesToMibiBytes(Helpers::convertToBytes($physicalServer->getMemory())));    
            }
            
            // check permissions on colocation
            $this->tryCheckPermission("colocations","general",array("item"=>$physicalServer->Colocations));
            
        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            return false;
        }
        return true;
    }
    
    /**
    * checks before delete
    * 
    * @param PhysicalServers $physicalServer
    */
    public function preDelete($physicalServer){
        // check permissions on colocation
        $this->tryCheckPermission("colocations","general",array("item"=>$physicalServer->Colocations));
        
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
        $this->redirectToTableSlideDataAction();
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

    /**
    * Redirect to the MonJobs Form
    * 
    * @param mixed $virtualServerId
    */
    public function monJobsAddAction($physicalServerId){
        // store in session
        $this->session->set("MonJobsForm", array(
            "op" => "new",
            "server_class" => '\RNTForest\ovz\models\PhysicalServers',
            "server_id" => intval($physicalServerId),
            "origin" => array(
                'controller' => 'physical_servers',
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
            "server_class" => '\RNTForest\ovz\models\PhysicalServers',
            "origin" => array(
                'controller' => 'physical_servers',
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
            "server_class" => '\RNTForest\ovz\models\PhysicalServers',
            "origin" => array(
                'controller' => 'physical_servers',
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
            "server_class" => '\RNTForest\ovz\models\PhysicalServers',
            "origin" => array(
                'controller' => 'physical_servers',
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
            "server_class" => '\RNTForest\ovz\models\PhysicalServers',
            "origin" => array(
                'controller' => 'physical_servers',
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
        $scope = $this->permissions->getScope('physical_servers','filter_customers');
        $customers = \RNTForest\core\models\Customers::getCustomersAsJson($filterString,$scope);
        return $customers;
    }
    
    /**
    * helper method to get the ovz templates of a physicalServer in the virtual servers form
    * 
    * @param int $physicalServerId
    * @return string
    */
    public function getOvzOstemplatesAction($physicalServerId){
        // Sanitize Parameters
        $physicalServerId = $this->filter->sanitize($physicalServerId,"int");
        
        try{
            // Validate (throws exceptions)
            $physicalServer = PhysicalServers::tryFindById($physicalServerId);
            $this->tryCheckPermission('physical_servers', 'general', array('item' => $physicalServer));
            
            // create array to sort it
            $ostemplates = '';
            if(!empty($physicalServer->getOvzOstemplates())){
                $ostemplates = json_decode($physicalServer->getOvzOstemplates(),true);
                sort($ostemplates);
                $ostemplates = json_encode($ostemplates);
            }
            
            // go back to json, to pass it to the jquery
            return $ostemplates;
        }catch(\Exception $e){
            // simply return message so it can be processed in jquery
            return $e->getMessage();
        }
    }
}