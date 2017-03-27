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

use RNTForest\core\interfaces\JobServerInterface;
use RNTForest\core\interfaces\PendingInterface;
use RNTForest\ovz\interfaces\MonServerInterface;
use RNTForest\core\libraries\PendingHelpers;

class PhysicalServers extends \RNTForest\core\models\ModelBase implements JobServerInterface, PendingInterface, MonServerInterface
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
    protected $ovz;

    /**
    *
    * @var string
    */
    protected $ovz_settings;
    
    /**
    *
    * @var string
    */
    protected $ovz_statistics;
    
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
    protected $modified;

    /**
    * 
    * @var string
    */
    protected $pending;
    
    /**
    * Method to set the value of field id
    *
    * @param integer $id
    * @return $this
    */
    public function setId($id)
    {
        $this->id = $id;

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
    * Method to set the value of field description
    *
    * @param string $description
    * @return $this
    */
    public function setDescription($description)
    {
        $this->description = $description;

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
    * Method to set the value of field colocations_id
    *
    * @param integer $colocations_id
    * @return $this
    */
    public function setColocationsId($colocations_id)
    {
        $this->colocations_id = $colocations_id;

        return $this;
    }

    /**
    * Method to set the value of field root_public_key
    *
    * @param string $root_public_key
    * @return $this
    */
    public function setRootPublicKey($root_public_key)
    {
        $this->root_public_key = $root_public_key;

        return $this;
    }

    /**
    * Method to set the value of field job_public_key
    *
    * @param string $jobpublic_key
    * @return $this
    */
    public function setJobPublicKey($job_public_key)
    {
        $this->job_public_key = $job_public_key;

        return $this;
    }

    /**
    * Method to set the value of field ovz
    *
    * @param integer $ovz
    * @return $this
    */
    public function setOvz($ovz)
    {
        $this->ovz = $ovz;

        return $this;
    }

    /**
    * Method to set the value of field ovz_settings
    *
    * @param string $ovz_settings
    * @return $this
    */
    public function setOvzSettings($ovz_settings)
    {
        $this->ovz_settings = $ovz_settings;

        return $this;
    }

    /**
    * OpenVZ statistics as JSON
    *
    * @param string $ovz_statistics
    * @return $this
    */
    public function setOvzStatistics($ovz_statistics)
    {
        $this->ovz_statistics = $ovz_statistics;
        return $this;
    }

    /**
    * Method to set the value of field fqdn
    *
    * @param string $fqdn
    * @return $this
    */
    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;

        return $this;
    }

    /**
    * Method to set the value of field core
    *
    * @param integer $core
    * @return $this
    */
    public function setCore($core)
    {
        $this->core = $core;

        return $this;
    }

    /**
    * Method to set the value of field memory
    *
    * @param integer $memory
    * @return $this
    */
    public function setMemory($memory)
    {
        $this->memory = $memory;

        return $this;
    }

    /**
    * Method to set the value of field space
    *
    * @param integer $space
    * @return $this
    */
    public function setSpace($space)
    {
        $this->space = $space;

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
    * Returns the value of field ovz
    *
    * @return integer
    */
    public function getOvz()
    {
        return $this->ovz;
    }

    /**
    * Returns the value of field ovz_settings
    *
    * @return string
    */
    public function getOvzSettings()
    {
        return $this->ovz_settings;
    }

    /**
    * Returns the value of field ovz_statistics
    *
    * @return string
    */
    public function getOvzStatistics()
    {
        return $this->ovz_statistics;
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
    * Returns the value of field modified
    *
    * @return string
    */
    public function getModified()
    {
        return $this->modified;
    }

    /**
    * helper method: returns the DCO Type
    * 1:Colocation, 2:Physical Server, 3:Virtual Server
    * 
    */
    public function getDcoType()
    {
        return 2;
    }

    /**
    * Initialize method for model.
    */
    public function initialize()
    {
        $this->setup(array('notNullValidations'=>false));
        $this->setup(array('virtualForeignKeys'=>false));

        $this->belongsTo("customers_id",'RNTForest\core\models\Customers',"id",array("alias"=>"Customers", "foreignKey"=>true));
        $this->belongsTo("colocations_id",'RNTForest\ovz\models\Colocations',"id",array("alias"=>"Colocations", "foreignKey"=>true));
        $this->hasMany("id",'RNTForest\ovz\models\VirtualServers',"physical_servers_id",array("alias"=>"VirtualServers", "foreignKey"=>array("allowNulls"=>true)));
        $this->hasMany("id",'RNTForest\ovz\models\Dcoipobjects',"physical_servers_id",array("alias"=>"Dcoipobjects", "foreignKey"=>array("allowNulls"=>true)));

        // Timestampable behavior
        $this->addBehavior(new Timestampable(array(
            'beforeUpdate' => array(
                'field' => 'modified',
                'format' => 'Y-m-d H:i:s'
            )
        )));   
    }

    /**
    * Validations and business logic
    *
    * @return boolean
    */
    public function validation()
    {
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
        /**
        * Container name that can be used to refer to said container in commands.
        * Names must be alphanumeric and may contain the characters \, -, _. Names
        * with white spaces must be enclosed in quotation marks.
        * 
        */
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
        
        $message = self::translate("physicalserver_name_valid");
        $validator->add('name', new RegexValidator([
            'pattern' => '/^[a-zA-Z0-9\-_\s]*$/',
            'message' => $message
        ]));        
        
        $message = self::translate("physicalserver_fqdn_required");
        // fqdn
        $validator->add('fqdn', new PresenceOfValidator([
            'message' => $message
        ]));        
        
        $message = self::translate("physicalserver_fqdn_valid");
        $validator->add('fqdn', new RegexValidator([
            'pattern' => '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/',
            'message' => $message
        ]));        
        
        $message = self::translate("physicalserver_core_required");
        // core
        $validator->add('core', new PresenceOfValidator([
            'message' => $message
        ]));        
        
        $message = self::translate("physicalserver_memory_required");
        // memory
        $validator->add('memory', new PresenceOfValidator([
            'message' => $message
        ]));        
        
        $message = self::translate("physicalserver_space_required");
        // space
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
        $findParameters = array("columns"=>"id, name");
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
        return '\RNTForest\ovz\models\Colocations';
    }
    
    /**
    * Getter for parent id.
    * needed because of MonServer Interface so that monitoring can instantiate a parent object.
    * 
    */
    public function getParentId(){
        return $this->colocations_id;
    }
    
    public function updateOvzStatistics(){
        if($this->ovz == '1'){
            $push = $this->getDI()['push'];
            $params = array();
            $job = $push->executeJob($this,'ovz_hoststatistics_info',$params);
            if($job->getDone()==2) throw new \Exception("Job (ovz_hoststatistics_info) executions failed: ".$job->getError());

            // save statistics
            $statistics = $job->getRetval(true);
            $this->setOvzStatistics($job->getRetval());
            $this->save();
        }
    }
    
    public function getMainIp(){
        return Dcoipobjects::findFirst(
            [
                "physical_servers_id = :id: AND main = 1",
                "bind" => [
                    "id" => $this->id,                   
                ],
            ]
        );
    }
}
