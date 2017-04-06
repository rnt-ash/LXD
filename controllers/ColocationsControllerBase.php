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
            "orderdir" => "ASC",
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
    }
    
    protected function isValidSlideFilterItem($colocation,$level){
        // Filter anwenden        
        if(!empty($this->slideDataInfo['filters']['filterAll'])){ 
                if(strpos(strtolower($colocation->name),strtolower($this->slideDataInfo['filters']['filterAll']))===false)
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
        $content .= $this->simpleview->render("partials/ovz/colocations/slideDetail.volt");
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
        // sanitize Parameters
        $colocationsId = $this->filter->sanitize($colocationsId,"int");

        try{
            // validate (throws exceptions)
            $colocation = Colocations::tryFindById($colocationsId);
            $this->tryCheckPermission('colocation', 'general', array('item' => $colocation));

            $reservations = array();
            $allocations = array();
            $allocationsObject = array();

            // nest reservations
            foreach(IpObjects::getSorted('\RNTForest\ovz\models\Colocations',$colocation->id) as $coloIpobject){
                if($coloIpobject->allocated != IpObjects::ALLOC_RESERVED){
                    $allocations[] = $coloIpobject->toString();
                    $allocationsObject[] = $coloIpobject;
                }else {
                    $reservations[$coloIpobject->toString()]=array();
                    foreach($coloIpobject->getSubReservations() as $psIpobject){
                        $reservations[$coloIpobject->toString()][$psIpobject->toString()]=array();
                        foreach($psIpobject->getSubReservations() as $vsIpobject){
                            $reservations[$coloIpobject->toString()][$psIpobject->toString()][$vsIpobject->toString()]=array();
                        }
                    }
                }
            }

            // find all other allocations
            foreach($colocation->physicalServers as $physicalServer){
                foreach($physicalServer->ipobjects as $psIpobject){
                    if($psIpobject->allocated != IpObjects::ALLOC_RESERVED){
                        $allocations[] = $psIpobject->toString();
                        $allocationsObject[] = $psIpobject;
                    }
                }
                foreach($physicalServer->virtualServers as $virtualServer){
                    foreach($virtualServer->ipobjects as $vsIpobject){
                        if($vsIpobject->allocated != IpObjects::ALLOC_RESERVED){
                            $allocations[] = $vsIpobject->toString();
                            $allocationsObject[] = $vsIpobject;
                        }
                    }
                }
            }
            sort($allocations);
            
            
            print_r($reservations);
            print_r($allocations);
            
            // Todo: PDF
            // generate PDF
            

        }catch(\Exception $e){
            $this->flashSession->error($e->getMessage());
            $this->logger->error($e->getMessage());
            $this->forwardToTableSlideDataAction();
            return;
        }
        die();
    }    
    
}
