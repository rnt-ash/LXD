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

use RNTForest\core\interfaces\JobServerInterface;
use RNTForest\core\interfaces\PendingInterface;
use RNTForest\lxd\interfaces\MonServerInterface;
use RNTForest\lxd\interfaces\IpServerInterface;
use RNTForest\core\libraries\PendingHelpers;
use RNTForest\lxd\functions\Monitoring;
use RNTForest\core\models\Customers;

class PhysicalServersBase extends \RNTForest\core\models\ModelBase implements JobServerInterface, PendingInterface, IpServerInterface
{

    /**
    *
    * @var string
    */
    protected $name;

    /**
    *
    * @var string
    */
    protected $description;

    /**
    *
    * @var integer
    */
    protected $customers_id;

    /**
    *
    * @var integer
    */
    protected $colocations_id;
    
    /**
    *
    * @var string
    */
    protected $root_public_key;

    /**
    *
    * @var string
    */
    protected $job_public_key;

    /**
    *
    * @var integer
    */
    protected $lxd;

    /**
    * @var text
    * 
    * @var mixed
    */
    protected $lxd_images;
    
    /**
    *
    * @var string
    */
    protected $fqdn;

    /**
    *
    * @var integer
    */
    protected $core;

    /**
    *
    * @var integer
    */
    protected $memory;

    /**
    *
    * @var integer
    */
    protected $space;

    /**
    *
    * @var string
    */
    protected $activation_date;

    /**
    * 
    * @var string
    */
    protected $pending;
    
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
    * Method to set the value of field description
    *
    * @param string $description
    */
    public function setDescription($description)
    {
        $this->description = $description;
    }

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
    * Method to set the value of field colocations_id
    *
    * @param integer $colocations_id
    */
    public function setColocationsId($colocations_id)
    {
        $this->colocations_id = $colocations_id;
    }

    /**
    * Method to set the value of field root_public_key
    *
    * @param string $root_public_key
    */
    public function setRootPublicKey($root_public_key)
    {
        $this->root_public_key = $root_public_key;
    }

    /**
    * Method to set the value of field job_public_key
    *
    * @param string $jobpublic_key
    */
    public function setJobPublicKey($job_public_key)
    {
        $this->job_public_key = $job_public_key;
    }

    /**
    * Method to set the value of field lxd
    *
    * @param integer $lxd
    */
    public function setLxd($lxd)
    {
        $this->lxd = $lxd;
    }

    /**
    * LXD images as JSON
    *
    * @param string $lxd_images
    */
    public function setLxdImages($lxd_images)
    {
        $this->lxd_images = $lxd_images;
    }

    /**
    * Method to set the value of field fqdn
    *
    * @param string $fqdn
    */
    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;
    }

    /**
    * Method to set the value of field core
    *
    * @param integer $core
    */
    public function setCore($core)
    {
        $this->core = $core;
    }

    /**
    * Method to set the value of field memory
    *
    * @param integer $memory
    */
    public function setMemory($memory)
    {
        $this->memory = $memory;
    }

    /**
    * Method to set the value of field space
    *
    * @param integer $space
    */
    public function setSpace($space)
    {
        $this->space = $space;
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
    * Pending
    *
    * @param string $pending
    */
    public function setPending($pending)
    {
        $this->pending = $pending;
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
    * Returns the value of field customers_id
    *
    * @return integer
    */
    public function getCustomersId()
    {
        return $this->customers_id;
    }

    /**
    * Returns the value of field colocations_id
    *
    * @return integer
    */
    public function getColocationsId()
    {
        return $this->colocations_id;
    }

    /**
    * Returns the value of field root_public_key
    *
    * @return string
    */
    public function getRootPublicKey()
    {
        return $this->root_public_key;
    }

    /**
    * Returns the value of field job_public_key
    *
    * @return string
    */
    public function getJobPublicKey()
    {
        return $this->job_public_key;
    }

    /**
    * Returns the value of field lxd
    *
    * @return integer
    */
    public function getLxd()
    {
        return $this->lxd;
    }

    /**
    * Returns the value of field lxd_images
    *
    * @return string
    */
    public function getLxdImages()
    {
        return $this->lxd_images;
    }

    /**
    * Returns the value of field fqdn
    *
    * @return string
    */
    public function getFqdn()
    {
        return $this->fqdn;
    }

    /**
    * Returns the value of field core
    *
    * @return integer
    */
    public function getCore()
    {
        return $this->core;
    }

    /**
    * Returns the value of field memory
    *
    * @return integer
    */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
    * Returns the value of field space
    *
    * @return integer
    */
    public function getSpace()
    {
        return $this->space;
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
    * Returns the value of field pending
    *
    * @return string
    */
    public function getPending()
    {
        return $this->pending;
    }
    
    /**
    * Initialize method for model.
    */
    public function initialize()
    {
        // inherit from parent
        parent::initialize();
        
        // relations
        $this->belongsTo("customers_id",'RNTForest\core\models\Customers',"id",array("alias"=>"Customer", "foreignKey"=>true));
        $this->belongsTo("colocations_id",'RNTForest\lxd\models\Colocations',"id",array("alias"=>"Colocations", "foreignKey"=>true));
        $this->hasMany("id",'RNTForest\lxd\models\VirtualServers',"physical_servers_id",array("alias"=>"VirtualServers", "foreignKey"=>array("allowNulls"=>true)));
    }
    
    public function onConstruct(){
        // inherit from parent
        parent::onConstruct();
    }

    /**
    * get all IpObjects of this physical Server
    * 
    * @return \RNTForest\lxd\models\IpObjects
    *     
    */
    public function getIpObjects(){
        $server_class = addslashes('\RNTForest\lxd\models\PhysicalServers');
        $resultset = IpObjects::find(["conditions"=>"server_class = '".$server_class."' AND server_id = '".$this->id."'"]);
        return $resultset;
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
            $message = new Message($this->translate("physicalserver_customer_not_exist"),"customers");            
            $this->appendMessage($message);
            return false;
        }
        
        // check if selected colocation exists
        if(!Colocations::findFirst($this->colocations_id)){
            $message = new Message($this->translate("physicalserver_colocation_not_exist"),"colocations_id");
            $this->appendMessage($message);
            return false;
        }
        
        $validator = $this->generateValidator();
        if(!$this->validate($validator)) return false;

        return true;
    }

    /**
    * generates validator for PhysicalServer model
    * 
    * return \Phalcon\Validation $validator
    * 
    */
    public function generateValidator(){

        // validator
        $validator = new Validation();

        // name
        $message = self::translate("physicalserver_name_required");
        $validator->add('name', new PresenceOfValidator([
            'message' => $message
        ]));        
        
        $messagemax = self::translate("physicalserver_messagemax");
        $messagemin = self::translate("physicalserver_messagemin");
        $validator->add('name', new StringLengthValitator([
            'max' => 50,
            'min' => 3,
            'messageMaximum' => $messagemax,
            'messageMinimum' => $messagemin,
        ]));

        // fqdn
        $message = self::translate("physicalserver_fqdn_required");
        $validator->add('fqdn', new PresenceOfValidator([
            'message' => $message
        ]));        
        
        $message = self::translate("physicalserver_fqdn_valid");
        $validator->add('fqdn', new RegexValidator([
            'pattern' => '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/',
            'message' => $message
        ]));        
        
        // customers_id
        $message = self::translate("physicalserver_customer_required");
        $validator->add('customers_id', new PresenceOfValidator([
            'message' => $message
        ]));
        
        // colocation
        $message = self::translate("physicalserver_colocation_required");
        $validator->add('colocations_id', new PresenceOfValidator([
            'message' => $message,
        ]));
        
        // core
        $message = self::translate("physicalserver_core_required");
        $validator->add('core', new PresenceOfValidator([
            'message' => $message
        ]));        
        
        // memory
        $message = self::translate("physicalserver_memory_required");
        $validator->add('memory', new PresenceOfValidator([
            'message' => $message
        ]));        
        
        // space
        $message = self::translate("physicalserver_space_required");
        $validator->add('space', new PresenceOfValidator([
            'message' => $message
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
        $physicalServers = array(0 => self::translate("physicalserver_all_physicalservers"));
        foreach($resultset as $physicalServer){
            $physicalServers[$physicalServer->id] = $physicalServer->name;
        }
        return $physicalServers;
    }
    
    /**
    * Add a PendingToken to the PendingEntity.
    * For conversion of a PendingString to a PendingToken use PendingHelpers::convert Method.
    * 
    * @param array $pendingToken a valid PendingToken
    */
    public function addPending($pendingToken){
        $pendingArray = json_decode($this->pending,true);
        $pendingArray[] = $pendingToken;        
        $this->pending = json_encode($pendingArray);
        $this->save();
    }
    
    /**
    * Remove a PendingToken from the PendingEntity.
    * For conversion of a PendingString to a PendingToken use PendingHelpers::convert Method.
    * 
    * @param array $pendingToken a valid PendingToken
    */
    public function removePending($pendingToken){
        $pendingArray = json_decode($this->pending,true);
        $this->pending = json_encode(PendingHelpers::removePendingTokenInPendingArray($pendingToken,$pendingArray));
        $this->save();
    }
    
    /**
    * Checks if a PendingEntity is pending representative to the given PendingToken.
    * If no PendingToken is given it will return true if any PendingToken is in the PendingEntity. 
    * 
    * @param array $pendingToken (optional) a valid PendingToken 
    * @return boolean 
    */
    public function isPending($pendingToken=''){
        $pendingArray = json_decode($this->pending,true);
        return PendingHelpers::checkForPendingTokenInPendingArray($pendingToken,$pendingArray);
    }

    /**
    * Getter for parent class.
    * needed because of MonServer Interface so that monitoring can instantiate a parent object.
    * 
    */
    public function getParentClass(){
        return '\RNTForest\lxd\models\Colocations';
    }
    
    /**
    * Getter for parent id.
    * needed because of MonServer Interface so that monitoring can instantiate a parent object.
    * 
    */
    public function getParentId(){
        return $this->colocations_id;
    }
    
    /**
    * Get the main IpObjects of this Server.
    * 
    * @return \RNTForest\lxd\models\IpObjects
    */
    public function getMainIp(){
        $reflection = new \ReflectionClass($this);
        
        return IpObjects::findFirst(
            [
                "server_class = :class: AND server_id = :id: AND main = 1",
                "bind" => [
                    "class" => "\\".$reflection->getName(),
                    "id" => $this->id,                   
                ],
            ]
        );
    }
}
