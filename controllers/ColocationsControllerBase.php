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

use RNTForest\ovz\models\Colocations;
use RNTForest\ovz\models\IpObjects;
use RNTForest\ovz\forms\IpObjectsForm;
use RNTForest\core\libraries\PDF;

class ColocationsControllerBase extends \RNTForest\core\controllers\TableSlideBase
{
    protected function getSlideDataInfo() {
        $scope = $this->permissions->getScope('colocations','general');
        $scopeQuery = "";
        $joinQuery = NULL;
        if ($scope == 'customers'){
            $scopeQuery = "customers_id = ".$this->session->get('auth')['customers_id'];
        } else if($scope == 'partners'){
            $scopeQuery = 'RNTForest\ovz\models\Colocations.customers_id = '.$this->session->get('auth')['customers_id'];
            $scopeQuery .= ' OR RNTForest\core\models\CustomersPartners.partners_id = '.$this->session->get('auth')['customers_id'];
            $joinQuery = array('model'=>'RNTForest\core\models\CustomersPartners',
                                'conditions'=>'RNTForest\ovz\models\Colocations.customers_id = RNTForest\core\models\CustomersPartners.customers_id',
                                'type'=>'LEFT');
        }

        return array(
            "type" => "slideData",
            "model" => '\RNTForest\ovz\models\Colocations',
            "form" => '\RNTForest\ovz\forms\ColocationsForm',
            "controller" => "colocations",
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
    }
    
    protected function isValidSlideFilterItem($colocation,$level){
        // Filter anwenden        
        if(!empty($this->slideDataInfo['filters']['filterAll'])){ 
                if(strpos(strtolower($colocation->name),strtolower($this->slideDataInfo['filters']['filterAll']))===false)
                    return false;
        }
        if(!empty($this->slideDataInfo['filters']['filterCustomers_id'])){ 
            if($colocation->customers_id != $this->slideDataInfo['filters']['filterCustomers_id'])
                return false;
        }
        return true; 
    }
    
    protected function renderSlideHeader($item,$level){
        $message = $this->translate("colocations_invalid_level");
        switch($level){
            case 0:
                return $item->name; 
                break;
            default:
                return $message;
        }
    }

    protected function renderSlideDetail($item,$level){
        // Slidelevel ignored because there is only one level
        $content = "";

        $this->simpleview->item = $item;
        $content .= $this->simpleview->render("colocations/slideDetail.volt");
        return $content;
    }

    /**
    * Adds an IP Object to the Server
    * 
    * @param integer $id primary key of the colocation
    * 
    */
    public function ipObjectAddAction($id){

        // store in session
        $this->session->set("IpObjectsForm", array(
            "op" => "new",
            "server_class" => '\RNTForest\ovz\models\Colocations',
            "server_id" => intval($id),
            "origin" => array(
                'controller' => 'colocations',
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
    * Edits an IP Object on the colocation
    * 
    * @param integer $ipobject primary key of the IP Object
    * 
    */
    public function ipObjectEditAction($ipobject){

        // store in session
        $this->session->set("IpObjectsForm", array(
            "op" => "edit",
            "origin" => array(
                'controller' => 'colocations',
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
                'controller' => 'colocations',
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
    * kghdjfg
    * 
    * @param integer $colocationsId
    */
    public function generateIpPdfAction($colocationsId){
        // Sanitize Parameters
        $colocationsId = $this->filter->sanitize($colocationsId,"int");

        try{
            // Validate (throws exceptions)
            $colocation = Colocations::tryFindById($colocationsId);
            $this->tryCheckPermission('colocations', 'general', array('item' => $colocation));

            // Create PDF Object
            $this->PDF = new PDF();
            $this->PDF->SetAutoPageBreak(true, 40);
            $permissions = $this->config->permissionbase;

            // Author and title        
            $this->PDF->SetAuthor(BASE_PATH.$this->config->pdf['author']);
            $this->PDF->SetTitle($this->translate("colocations_ipobjects"));

            // Creating page 
            $this->PDF->AddPage();

            // Print header
            $this->PDF->printHeader($this->translate("colocations_ipobjects"),$colocation->Customer->printAddressText('box'));
            
            $this->PDF->SetFont('','B',12);
            
            $this->PDF->Cell(0,0,"Colocation: " .$colocation->getName(), 0, 2, '',false);
            $this->PDF->Ln(5);
            
            // Set two columns
            $this->PDF->resetColumns();
            $this->PDF->setEqualColumns(2);
            
            $this->PDF->SetFont('','',10);
            
            // Definition of the needed variables
            $ipAllocated = array();
            $ipReserved = array();
            $ipReservedNet = array();
            $printed = array();
            $partOfOther = false;
            
            // Checking if Colocation has IP reservations or allocations and saves them in array
            foreach($colocation->ip_objects as $ipobjectcolo){
                if($ipobjectcolo->allocated == "1" && $ipobjectcolo->value2 == "24"){
                    $ipReservedNet[] = $ipobjectcolo;
                }
                if($ipobjectcolo->allocated == "1" && $ipobjectcolo->value2 != "24"){
                    $ipReserved[] = $ipobjectcolo;
                }
                if($ipobjectcolo->allocated == "2"){
                    $ipAllocated[] = $ipobjectcolo;
                }
                
                // Goes through every Physical server and saves the allocated IPs into array
                foreach($colocation->PhysicalServers as $physicalServer){
                    foreach($physicalServer->ip_objects as $ipobjectps){
                        if($ipobjectps->allocated == "2"){
                            if(!in_array($ipobjectps, $ipAllocated))$ipAllocated[] = $ipobjectps; 
                        }
                    }
                    
                    // Goes through every Virtual server and saves the allocated IPs into array
                    foreach($physicalServer->VirtualServers as $virtualServer){
                        foreach($virtualServer->ip_objects as $ipobjectvs){
                            if($ipobjectvs->allocated == "1"){
                            }else{
                                if(!in_array($ipobjectvs, $ipAllocated))$ipAllocated[] = $ipobjectvs; 
                            }
                        } 
                    }
                }
            }
            
            // Sorting IPs in the arrays
            usort($ipReserved, array('RNTForest\ovz\models\IpObjects', 'cmp'));
            usort($ipAllocated, array('RNTForest\ovz\models\IpObjects', 'cmp'));
            usort($ipReservedNet, array('RNTForest\ovz\models\IpObjects', 'cmp'));

            // Print all the Information given in the arrays
            // If there is no reservations, then print message
            if($ipReserved == null && $ipReservedNet == null){
                $this->PDF->Cell(0,0,$this->translate("colocations_pdf_no_ipobjects"), 0, 2, '', false, '', 1);
                $this->PDF->Output('ipobjects.pdf', 'I');
                die();
            }
            
            // foreach all subnets and prints out the subnet-IP                                  
            foreach($ipReservedNet as $reservedNet){
                $this->PDF->SetFont('','B',11);
                $this->PDF->Cell(0,0,$reservedNet->value1 ." /" .$reservedNet->value2, 0, 2, '', false, '', 1);
                $this->PDF->SetFont('','',10);
                
                // Checks if there is a subreservation for the IP, so i gets just printed once
                foreach($ipAllocated as $allocated){
                    foreach($ipReserved as $reservedCheck){
                        if($allocated->isPartOf($reservedCheck)){
                            $partOfOther = true;
                        }
                    }
                    
                    // Printing the IP if its not part of any subreservation and if its part of the colocation
                    if($allocated->isPartOf($reservedNet) && $partOfOther == false){
                        $Servername = $allocated->getServerClass()::findFirstById($allocated->getServerId());
                        if(!in_array($allocated, $printed))$this->PDF->Cell(0,0,$allocated->value1 ." - " .$Servername->name, 0, 2, '', false, '', 1);
                        $printed[] = $allocated;
                    }
                    $partOfOther = false;
                }
            }
            // Foreach subreservation and printing out the subreservation range
            foreach($ipReserved as $reserved){
                $this->PDF->SetFont('','B',11);
                $this->PDF->Ln(1);
                if(!in_array($reserved, $printed) /*&& $reserved->isPartOf($reservedNet)*/){
                    $this->PDF->Cell(0,0,$reserved->value1 ."-" .$reserved->value2 ." - " .$reserved->comment, 0, 2, '', false, '', 1);
                    $printed[] = $reserved;    
                }
                    
                // Checking if the IP is part of the subreservation and prints it out
                foreach($ipAllocated as $allocated){
                    $this->PDF->SetFont('','',10);
                    if($allocated->isPartOf($reserved) /*&& $reserved->isPartOf($reservedNet)*/){
                        $Servername = $allocated->getServerClass()::findFirstById($allocated->getServerId());
                        if(!in_array($allocated, $printed)){
                            if($allocated->comment != null){
                                $this->PDF->Cell(0,0,$allocated->value1 ." - " .$allocated->comment, 0, 2, '', false, '', 1);
                            }else{
                                $this->PDF->Cell(0,0,$allocated->value1 ." - " .$Servername->name, 0, 2, '', false, '', 1);
                            }
                        }
                        $printed[] = $allocated;
                    }
                }
            }
                
            $this->PDF->Ln(3);
            // Dispaly the PDF on the monitor
            $this->PDF->Output('ipobjects.pdf', 'I');
            die();

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
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
}
