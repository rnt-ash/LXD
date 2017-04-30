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
use Phalcon\Mvc\Model\Message as Message;

use RNTForest\core\interfaces\JobServerInterface;
use RNTForest\core\interfaces\PendingInterface;
use RNTForest\ovz\interfaces\MonServerInterface;
use RNTForest\ovz\interfaces\IpServerInterface;
use RNTForest\core\libraries\PendingHelpers;
use RNTForest\ovz\functions\Monitoring;
use RNTForest\core\models\Customers;

class PhysicalServers extends \RNTForest\core\models\ModelBase implements JobServerInterface, PendingInterface, MonServerInterface, IpServerInterface
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
    * Pending
    *
    * @param string $pending
    */
    public function setPending($pending)
    {
        $this->pending = $pending;
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
        $this->setup(array('notNullValidations'=>false));
        $this->setup(array('virtualForeignKeys'=>false));

        $this->belongsTo("customers_id",'RNTForest\core\models\Customers',"id",array("alias"=>"Customers", "foreignKey"=>true));
        $this->belongsTo("colocations_id",'RNTForest\ovz\models\Colocations',"id",array("alias"=>"Colocations", "foreignKey"=>true));
        $this->hasMany("id",'RNTForest\ovz\models\VirtualServers',"physical_servers_id",array("alias"=>"VirtualServers", "foreignKey"=>array("allowNulls"=>true)));

        // Timestampable behavior
        $this->addBehavior(new Timestampable(array(
            'beforeUpdate' => array(
                'field' => 'modified',
                'format' => 'Y-m-d H:i:s'
            )
        )));   
    }

    /**
    * get all IpObjects of this physical Server
    * 
    * @return \RNTForest\ovz\models\IpObjects
    *     
    */
    public function getIpObjects(){
        $server_class = addslashes('\RNTForest\ovz\models\PhysicalServers');
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
        
        $message = self::translate("physicalserver_customer_required");
        // customers_id
        $validator->add('customers_id', new PresenceOfValidator([
            'message' => $message
        ]));
        
        $message = self::translate("physicalserver_colocation_required");
        // colocation
        $validator->add('colocations_id', new PresenceOfValidator([
            'message' => $message,
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
    
    /**
    * Updates the OvzStatistics with a job and saves it to the model.
    * 
    */
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
    
    /**
    * Get the main IpObjects of this Server.
    * 
    * @return \RNTForest\ovz\models\IpObjects
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
    
    /**
    * Get all MonRemoteJobs instances of this server.
    *
    * @return \Phalcon\Mvc\Model\ResultsetInterface 
    */
    public function getMonRemoteJobs(){
        $reflection = new \ReflectionClass($this);
        
        return MonRemoteJobs::find(
            [
                "server_class = :class: AND server_id = :id:",
                "bind" => [
                    "class" => "\\".$reflection->getName(),
                    "id" => $this->getId(),
                ],
            ]
        );
    }
    
    /**
    * Get all MonLocalJobs instances of this server.
    *
    * @return \Phalcon\Mvc\Model\ResultsetInterface 
    */
    public function getMonLocalJobs(){
        $reflection = new \ReflectionClass($this);
        
        return MonLocalJobs::find(
            [
                "server_class = :class: AND server_id = :id:",
                "bind" => [
                    "class" => "\\".$reflection->getName(),
                    "id" => $this->getId(),
                ],
            ]
        );
    }
    
        
    /**
    * Adds a new MonRemoteJobs for this server.
    * 
    * @param string $behavior
    * @param int $period
    * @param int $alarmPeriod
    * @param int[] $messageContacts
    * @param int[] $alarmContacts
    */
    public function addMonRemoteJob($behavior, $period, $alarmPeriod, $messageContacts, $alarmContacts){
        // validate and clean parameters
        if(!key_exists($behavior,Monitoring::getRemoteBehaviors())){
            throw new \Exception($this->translate("physicalservers_addmonremotejob_no_valid_behavior"));
        }
        $period = intval($period);
        $alarmPeriod = intval($alarmPeriod);
        $messageContacts = array_map('intval',$messageContacts);
        $messageContactsString = implode(',',$messageContacts);
        $alarmContacts = array_map('intval',$alarmContacts);
        $alarmContactsString = implode(',',$alarmContacts);
        
        // set healing to 1 if HttpMonBehavior
        if(strpos($behavior,'HttpMonBehavior') > 0){
            $healing = 1;
        }else{
            $healing = 0;
        }
        
        $reflection = new \ReflectionClass($this);
        
        // and save the new job
        $monJob = new MonRemoteJobs();
        $monJob->save(
            [
                "server_id" => $this->getId(),
                "server_class" => "\\".$reflection->getName(),
                "mon_behavior_class" => $behavior,
                "period" => $period,
                "alarm_period" => $alarmPeriod,
                "healing" => $healing,
                "mon_contacts_message" => $messageContactsString,
                "mon_contacts_alarm" => $alarmContactsString,
            ]
        );
    }
    
    /**
    * Adds a new MonLocalJob for this server.
    * 
    * @param string $behavior
    * @param int $period
    * @param int $alarmPeriod
    * @param int[] $messageContacts
    * @param int[] $alarmContacts
    */
    public function addMonLocalJob($behavior, $period, $alarmPeriod, $messageContacts, $alarmContacts){
        // validate and clean parameters
        if(!key_exists($behavior,Monitoring::getLocalPhysicalBehaviors())){
            throw new \Exception($this->translate("physicalservers_addmonlocaljob_no_valid_behavior"));
        }
        $period = intval($period);
        $alarmPeriod = intval($alarmPeriod);
        $messageContacts = array_map('intval',$messageContacts);
        $messageContactsString = implode(',',$messageContacts);
        $alarmContacts = array_map('intval',$alarmContacts);
        $alarmContactsString = implode(',',$alarmContacts);
        
        // gen the warn and maximal value
        $warningValue = $maximalValue = 0;
        if(strpos($behavior,'Cpu')){
            $warningValue = 50;
            $maximalValue = 80;
        }elseif(strpos($behavior,'Memoryfree')){
            // warning at a quarter, minimal 512
            $warningValue = intval($this->getMemory()*0.25);
            if($warningValue < 1024) $warningValue = 1024;
            
            // maximal at ten percent, minimal 256
            $maximalValue = intval($this->getMemory()*0.1);
            if($maximalValue < 512) $maximalValue = 512;
        }elseif(strpos($behavior,'Diskspacefree')){
            // warning at a ten percent, minimal 2
            $warningValue = intval($this->getSpace()*0.1);
            if($warningValue < 10) $warningValue = 10;
            
            // maximal at five percent, minimal 1 
            $maximalValue = intval($this->getSpace()*0.05);
            if($maximalValue < 5) $maximalValue = 5;
        }
        
        $reflection = new \ReflectionClass($this);
        
        // and save the new job
        $monJob = new MonLocalJobs();
        $monJob->save(
            [
                "server_id" => $this->getId(),
                "server_class" => "\\".$reflection->getName(),
                "mon_behavior_class" => $behavior,
                "period" => $period,
                "alarm_period" => $alarmPeriod,
                "warning_value" => $warningValue,
                "maximal_value" => $maximalValue,
                "mon_contacts_message" => $messageContactsString,
                "mon_contacts_alarm" => $alarmContactsString,
            ]
        );
    }
}
