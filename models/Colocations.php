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

namespace RNTForest\ovz\models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Mvc\Model\Behavior\Timestampable;

use RNTForest\ovz\models\IpObjects;

class Colocations extends \RNTForest\core\models\ModelBase implements \RNTForest\ovz\interfaces\IpServerInterface
{

    /**
    *
    * @var integer
    * @Primary
    * @Identity
    */
    protected $id;

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
    *
    * @var string
    * @Column(type="string", nullable=false)
    */
    protected $modified;

    /**
    * Method to set the value of field id
    *
    * @param integer $id
    * @return $this
    */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
    * Method to set the value of field customers_id
    *
    * @param integer $customers_id
    * @return $this
    */
    public function setCustomersId($customers_id)
    {
        $this->customers_id = $customers_id;

        return $this;
    }

    /**
    * Method to set the value of field name
    *
    * @param string $name
    * @return $this
    */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
    * @param string $description
    * @return $this
    */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
    * @param string $location
    * @return $this
    */
    public function setLocation($location) {
        $this->location = $location;
        return $this;
    }

    /**
    * Method to set the value of field activation_date
    *
    * @param string $activation_date
    * @return $this
    */
    public function setActivationDate($activation_date)
    {
        $this->activation_date = $activation_date;

        return $this;
    }

    /**
    * Method to set the value of field modified
    *
    * @param string $modified
    * @return $this
    */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
    * Returns the value of field id
    *
    * @return integer
    */
    public function getId()
    {
        return $this->id;
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
    * Returns the value of field modified
    *
    * @return string
    */
    public function getModified()
    {
        return $this->modified;
    }
    
    /**
    * Initialize method for model.
    */
    public function initialize()
    {
        $this->setup(array('notNullValidations'=>false));
        $this->setup(array('virtualForeignKeys'=>false));

        $this->belongsTo("customers_id",'RNTForest\core\models\Customers',"id",array("alias"=>"Customers", "foreignKey"=>true));
        $this->hasMany("id",'RNTForest\ovz\models\PhysicalServers',"colocations_id",array("alias"=>"PhysicalServers", "foreignKey"=>array("allowNulls"=>true)));
        
        // Timestampable behavior
        $this->addBehavior(new Timestampable(array(
            'beforeUpdate' => array(
                'field' => 'modified',
                'format' => 'Y-m-d H:i:s'
            )
        )));   
    }

    /**
    * get all IpObjects of this colocation
    * 
    * @return \RNTForest\ovz\models\IpObjects
    *     
    */
    public function getIpObjects(){
        $server_class = addslashes('\RNTForest\ovz\models\Colocations');
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
        $findParameters = array("columns"=>"id, name");
        $resultset = self::findFromScope($scope,$findParameters);
        $colocations = array(0 => self::translate("colocation_all_colocations"));
        foreach($resultset as $colocation){
            $colocations[$colocation->id] = $colocation->name;
        }
        return $colocations;
    }


}
