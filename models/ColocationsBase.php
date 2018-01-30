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

namespace RNTForest\lxd\models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Message as Message;

use RNTForest\lxd\models\IpObjects;
use RNTForest\core\models\Customers;

class ColocationsBase extends \RNTForest\core\models\ModelBase implements \RNTForest\lxd\interfaces\IpServerInterface
{

    /**
    *
    * @var integer
    * @Column(type="integer", length=11, nullable=false)
    */
    protected $customers_id;

    /**
    *
    * @var string
    * @Column(type="string", length=50, nullable=false)
    */
    protected $name;

    /**
    *
    * @var string
    * @Column(type="string", nullable=true)
    */
    protected $description;

    /**
    *
    * @var string
    * @Column(type="string", length=50, nullable=false)
    */
    protected $location;

    /**
    *
    * @var string
    * @Column(type="string", nullable=false)
    */
    protected $activation_date;
    
    /**
    * Method to set the value of field customers_id
    *
    * @param integer $customers_id
    */
    public function setCustomersId($customers_id)
    {
        $this->customers_id = $customers_id;
    }

    /**
    * Method to set the value of field name
    *
    * @param string $name
    */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
    * @param string $description
    */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
    * @param string $location
    */
    public function setLocation($location) {
        $this->location = $location;
    }

    /**
    * Method to set the value of field activation_date
    *
    * @param string $activation_date
    */
    public function setActivationDate($activation_date)
    {
        $this->activation_date = $activation_date;
    }
    
    /**
    * Returns the value of field customers_id
    *
    * @return integer
    */
    public function getCustomersId()
    {
        return $this->customers_id;
    }

    /**
    * Returns the value of field name
    *
    * @return string
    */
    public function getName()
    {
        return $this->name;
    }

    /**
    * Returns the value of field description
    *
    * @return string
    */
    public function getDescription()
    {
        return $this->description;
    }

    /**
    * Returns the value of field location
    *
    * @return string
    */
    public function getLocation()
    {
        return $this->location;
    }

    /**
    * Returns the value of field activation_date
    *
    * @return string
    */
    public function getActivationDate()
    {
        return $this->activation_date;
    }
    
    /**
    * Initialize method for model.
    */
    public function initialize()
    {
        // inherit from parent
        parent::initialize();

        $this->belongsTo("customers_id",'RNTForest\core\models\Customers',"id",array("alias"=>"Customer", "foreignKey"=>true));
        $this->hasMany("id",'RNTForest\lxd\models\PhysicalServers',"colocations_id",array("alias"=>"PhysicalServers", "foreignKey"=>array("allowNulls"=>true)));
           
    }
    
    public function onConstruct(){
        // inherit from parent
        parent::onConstruct();
    }

    /**
    * get all IpObjects of this colocation
    * 
    * @return \RNTForest\lxd\models\IpObjects
    *     
    */
    public function getIpObjects(){
        $server_class = addslashes('\RNTForest\lxd\models\Colocations');
        $resultset = IpObjects::find(["conditions"=>"server_class = '".$server_class."' AND server_id = '".$this->id."'"]);
        return $resultset;
    }

    /**
    * Gibt die Werte pro Kolonne für das TabelData Element zurück
    * 
    * @param mixed $row
    */
    public function printTableData($row){
        switch($row){
            case "clnr":
                return $this->id;
                break;
            case "name":
                return $this->name;
                break;
            case "customer":
                return $this->getCustomers()->printAddressText('short');
                break;    
            case "location":
                return $this->location;
                break;
        }
    }
    
    /**
    * Validations and business logic
    *
    * @return boolean
    */
    public function validation()
    {
        // check if selected customer exists
        if(!Customers::findFirst($this->customers_id) OR empty($this->customers_id)){
            $message = new Message($this->translate("colocations_customer_not_exist"),"customers");            
            $this->appendMessage($message);
            return false;
        }
        
        // get params from session
        $validator = $this->generateValidator();
        if(!$this->validate($validator)) return false;
        
        return true;
    }
    
    /**
    * generates validator for Colocations model
    * 
    * return \Phalcon\Validation $validator
    * 
    */
    public static function generateValidator(){

        // validator
        $validator = new Validation();

        // name
        $message = self::translate("colocations_name_required");
        $validator->add('name', new PresenceOfValidator([
            'message' => $message
        ]));        

        $messagemax = self::translate("colocations_namemax");
        $messagemin = self::translate("colocations_namemin");
        $validator->add('name', new StringLengthValitator([
            'max' => 50,
            'min' => 3,
            'messageMaximum' => $messagemax,
            'messageMinimum' => $messagemin,
        ]));

        $message = self::translate("colocations_name_valid");
        $validator->add('name', new RegexValidator([
            'pattern' => '/^[a-zA-Z0-9\-_\s]*$/',
            'message' => $message
        ]));        
        
        // customer
        $message = self::translate("colocations_customer_required");
        $validator->add('customers_id', new PresenceOfValidator([
            'message' => $message
        ])); 
        
        // location
        $messagemax = self::translate("colocations_location_max");
        $messagemin = self::translate("colocations_locationm_in");
        $validator->add('location', new StringLengthValitator([
            'max' => 50,
            'min' => 3,
            'messageMaximum' => $messagemax,
            'messageMinimum' => $messagemin,
            'allowEmpty' => true,
        ]));

        $message = self::translate("colocations_locaton_valid");
        $validator->add('location', new RegexValidator([
            'pattern' => '/^[a-zA-ZäÄöÖüÜ0-9\-_\s]*$/',
            'message' => $message,
            'allowEmpty' => true,
        ]));        

        return $validator;
    }

    /**
    * generate an array for an select element, considered the permission scope
    * 
    * @param string $scope
    */
    public static function generateArrayForSelectElement($scope){
        $findParameters = array("columns"=>"id, name","order"=>"name");
        $resultset = self::findFromScope($scope,$findParameters);
        $colocations = array(0 => self::translate("colocation_all_colocations"));
        foreach($resultset as $colocation){
            $colocations[$colocation->id] = $colocation->name;
        }
        return $colocations;
    }


}
