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
use Phalcon\Validation\Validator\Confirmation as ConfirmationValidator;
use Phalcon\Mvc\Model\Behavior\Timestampable;

use RNTForest\core\libraries\PendingHelpers;

class VirtualServers extends \RNTForest\core\models\ModelBase implements \RNTForest\core\interfaces\JobServerInterface, \RNTForest\core\interfaces\PendingInterface
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
    protected $physical_servers_id;

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
    protected $ovz_uuid;
    
    /**
    * 
    * @var string
    */
    protected $ovz_vstype;

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
    protected $ovz_state;

    /**
    *
    * @var string
    */
    protected $ovz_snapshots;

    /**
    *
    * @var integer
    */
    protected $ovz_replica;

    /**
    *
    * @var integer
    */
    protected $ovz_replica_id;

    /**
    *
    * @var integer
    */
    protected $ovz_replica_host;

    /**
    *
    * @var string
    */
    protected $ovz_replica_cron;

    /**
    *
    * @var string
    */
    protected $ovz_replica_lastrun;

    /**
    *
    * @var string
    */
    protected $ovz_replica_nextrun;

    /**
    *
    * @var integer
    */
    protected $ovz_replica_status;

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
    *
    * @var string
    */
    protected $modified;

    /**
    * Unique ID
    *
    * @param integer $id
    * @return $this
    */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
    * Name of the virtual server
    *
    * @param string $name
    * @return $this
    */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
    * Description
    *
    * @param string $description
    * @return $this
    */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
    * Foreign key: Customers
    *
    * @param integer $customers_id
    * @return $this
    */
    public function setCustomersId($customers_id)
    {
        $this->customers_id = $customers_id;
    }

    /**
    * Foreign key: PhysicalServers
    *
    * @param integer $physical_servers_id
    * @return $this
    */
    public function setPhysicalServersId($physical_servers_id)
    {
        $this->physical_servers_id = $physical_servers_id;
    }

    /**
    * Public key (OpenSSL)
    *
    * @param string $job_public_key
    * @return $this
    */
    public function setJobPublicKey($job_public_key)
    {
        $this->job_public_key = $job_public_key;
    }

    /**
    * Virtual server is OpenVZ guest
    *
    * @param int $ovz
    * @return $this
    */
    public function setOvz($ovz)
    {
        $this->ovz = $ovz;
    }

    /**
    * UUID of the virtual server
    *
    * @param string $ovz_uuid
    * @return $this
    */
    public function setOvzUuid($ovz_uuid)
    {
        $this->ovz_uuid = $ovz_uuid;
    }
    
    /**
    * VS Type of the virtual server
    *
    * @param string $ovz_vstyp CT or VM
    * @return $this
    */
    public function setOvzVstype($ovz_vstyp)
    {
        $this->ovz_vstype = $ovz_vstyp;
    }

    /**
    * OpenVZ settings as JSON
    *
    * @param string $ovz_settings
    * @return $this
    */
    public function setOvzSettings($ovz_settings)
    {
        $this->ovz_settings = $ovz_settings;
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
    }

    /**
    * saves OVT State
    *
    * @param string $ovz_state
    * @return $this
    */
    public function setOvzState($ovz_state)
    {
        $this->ovz_state = $ovz_state;
    }

    /**
    * OpenVZ snapshots as JSON
    *
    * @param string $ovz_snapshots
    * @return $this
    */
    public function setOvzSnapshots($ovz_snapshots)
    {
        $this->ovz_snapshots = $ovz_snapshots;
    }

    /**
    * OpenVZ guest has replica
    *
    * @param integer $ovz_replica 0=off, 1=master, 2=slave
    * @return $this
    */
    public function setOvzReplica($ovz_replica)
    {
        $this->ovz_replica = $ovz_replica;
    }

    /**
    * Foreign key to replica slave/master
    *
    * @param integer $ovz_replica_id
    * @return $this
    */
    public function setOvzReplicaId($ovz_replica_id)
    {
        $this->ovz_replica_id = $ovz_replica_id;
    }

    /**
    * Foreign key to replica host
    *
    * @param integer $ovz_replica_host
    * @return $this
    */
    public function setOvzReplicaHost($ovz_replica_host)
    {
        $this->ovz_replica_host = $ovz_replica_host;
    }

    /**
    * cron entries to start teh replica preiodical
    *
    * @param string $ovz_replica_cron
    * @return $this
    */
    public function setOvzReplicaCron($ovz_replica_cron)
    {
        $this->ovz_replica_cron = $ovz_replica_cron;
    }

    /**
    * date of the replcas last run
    *
    * @param string $ovz_replica_lastrun
    * @return $this
    */
    public function setOvzReplicaLastrun($ovz_replica_lastrun)
    {
        $this->ovz_replica_lastrun = $ovz_replica_lastrun;
    }

    /**
    * date of the claculated next run of the replica
    *
    * @param string $ovz_replica_nextrun
    * @return $this
    */
    public function setOvzReplicaNextrun($ovz_replica_nextrun)
    {
        $this->ovz_replica_nextrun = $ovz_replica_nextrun;
    }

    /**
    * replica status
    *
    * @param integer $ovz_replica_status 0:off, 1:idle, 2:sync, 3:initial, 9:error
    * @return $this
    */
    public function setOvzReplicaStatus($ovz_replica_status)
    {
        $this->ovz_replica_status = $ovz_replica_status;
    }

    /**
    * FQDN
    *
    * @param string $fqdn
    * @return $this
    */
    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;
    }

    /**
    * CPU cores
    *
    * @param integer $core
    * @return $this
    */
    public function setCore($core)
    {
        $this->core = $core;
    }

    /**
    * Memory
    *
    * @param integer $memory in MB
    * @return $this
    */
    public function setMemory($memory)
    {
        $this->memory = $memory;
    }

    /**
    * Diskspace
    *
    * @param integer $space in GB
    * @return $this
    */
    public function setSpace($space)
    {
        $this->space = $space;
    }

    /**
    * Activation date
    *
    * @param string $activation_date
    * @return $this
    */
    public function setActivationDate($activation_date)
    {
        $this->activation_date = $activation_date;
    }

    /**
    * last modified time
    *
    * @param string $modified
    * @return $this
    */
    public function setModified($modified)
    {
        $this->modified = $modified;
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
    * Returns the value of field physical_servers_id
    *
    * @return integer
    */
    public function getPhysicalServersId()
    {
        return $this->physical_servers_id;
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
    * Returns the value of field ovz_uuid
    *
    * @return string
    */
    public function getOvzUuid()
    {
        return $this->ovz_uuid;
    }

    /**
    * Returns the value of field ovz_vstype
    *
    * @return string
    */
    public function getOvzVstype()
    {
        return $this->ovz_vstype;
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
    * Returns the value of field ovz_settings
    *
    * @return array
    */
    public function getOvzSettingsAsArray()
    {
        return json_decode($this->ovz_settings,true);
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
    * Returns the value of field ovz_state
    *
    * @return string
    */
    public function getOvzState()
    {
        return $this->ovz_state;
    }

    /**
    * Returns the value of field ovz_snapshots
    *
    * @return string
    */
    public function getOvzSnapshots()
    {
        return $this->ovz_snapshots;
    }

    /**
    * Returns the value of field ovz_replica
    *
    * @return integer
    */
    public function getOvzReplica()
    {
        return $this->ovz_replica;
    }

    /**
    * Returns the value of field ovz_replica_id
    *
    * @return integer
    */
    public function getOvzReplicaId()
    {
        return $this->ovz_replica_id;
    }

    /**
    * Returns the value of field ovz_replica_host
    *
    * @return integer
    */
    public function getOvzReplicaHost()
    {
        return $this->ovz_replica_host;
    }

    /**
    * Returns the value of field ovz_replica_cron
    *
    * @return string
    */
    public function getOvzReplicaCron()
    {
        return $this->ovz_replica_cron;
    }

    /**
    * Returns the value of field ovz_replica_lastrun
    *
    * @return string
    */
    public function getOvzReplicaLastrun()
    {
        return $this->ovz_replica_lastrun;
    }

    /**
    * Returns the value of field ovz_replica_nextrun
    *
    * @return string
    */
    public function getOvzReplicaNextrun()
    {
        return $this->ovz_replica_nextrun;
    }

    /**
    * Returns the value of field ovz_replica_status
    *
    * @return integer
    */
    public function getOvzReplicaStatus()
    {
        return $this->ovz_replica_status;
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
    * Initialize method for model.
    */
    public function initialize()
    {
        $this->setup(array('notNullValidations'=>false));
        $this->setup(array('virtualForeignKeys'=>false));

        $this->belongsTo("customers_id",'RNTForest\core\models\Customers',"id",array("alias"=>"Customers", "foreignKey"=>true));
        $this->belongsTo("physical_servers_id",'RNTForest\ovz\models\PhysicalServers',"id",array("alias"=>"PhysicalServers", "foreignKey"=>true));
        $this->hasMany("id",'RNTForest\ovz\models\Dcoipobjects',"virtual_servers_id",array("alias"=>"Dcoipobjects", "foreignKey"=>array("allowNulls"=>true)));

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
        // get params from session
        $session = $this->getDI()->get("session")->get("VirtualServersValidator");
        $op = $session['op'];
        $vstype = $session['vstype'];

        $validator = $this->generateValidator($op,$vstype);
        if(!$this->validate($validator)) return false;

        return true;
    }

    
    /**
    * generates validator for VirtualServer model
    * 
    * return \Phalcon\Validation $validator
    * 
    */
    public static function generateValidator($op,$vstype){
        
        // validator
        $validator = new Validation();

        // name
        /**
        * Container name that can be used to refer to said container in commands.
        * The virtual machine name must not exceed 40 characters
        * Names must be alphanumeric and may contain the characters \, -, _. Names
        * with white spaces must be enclosed in quotation marks.
        * 
        */
        $message = self::translate("virtualserver_name_required");
        $validator->add('name', new PresenceOfValidator([
            'message' => $message
        ]));        

        $messagemax = self::translate("virtualserver_namemax");
        $messagemin = self::translate("virtualserver_namemin");
        $validator->add('name', new StringLengthValitator([
            'max' => 40,
            'min' => 3,
            'messageMaximum' => $messagemax,
            'messageMinimum' => $messagemin,
        ]));

        $message = self::translate("virtualserver_name_valid");
        $validator->add('name', new RegexValidator([
            'pattern' => '/^[a-zA-Z0-9\-_\s]*$/',
            'message' => $message
        ]));        

        // fqdn
        $message = self::translate("virtualserver_fqdn_valid");
        $validator->add('fqdn', new RegexValidator([
            'pattern' => '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/',
            'message' => $message,
            'allowEmpty' => true,
        ]));        

        // customer
        $message = self::translate("virtualserver_customer_required");
        $validator->add('customers_id', new PresenceOfValidator([
            'message' => $message
        ]));        

        // physical server
        $message = self::translate("virtualserver_physicalserver_required");
        $validator->add('physical_servers_id', new PresenceOfValidator([
            'message' => $message
        ]));        

        // core
        $message = self::translate("virtualserver_core_required");
        $validator->add('core', new PresenceOfValidator([
            'message' => $message
        ]));        

        // memory
        $message = self::translate("virtualserver_memory_required");
        $validator->add('memory', new PresenceOfValidator([
            'message' => $message
        ]));        

        // space
        $message = self::translate("virtualserver_space_required");
        $validator->add('space', new PresenceOfValidator([
            'message' => $message
        ]));        

        if($op == 'new' && ($vstype == 'CT' || $vstype == 'VM')){
            // password
            $message = self::translate("virtualserver_password_required"); 
            $validator->add('password', new PresenceOfValidator([
                'message' => $message
            ]));        

            $message = self::translate("virtualserver_passwordmin");
            $validator->add('password', new StringLengthValitator([
                'min' => 8,
                'messageMinimum' => $message
            ]));        
        }

        if($op == 'new' && $vstype == 'CT'){
            // ostemplate
            $message = self::translate("virtualserver_ostemplate_required");
            $validator->add('ostemplate', new PresenceOfValidator([
                'message' => $message
            ]));        
        }        
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
        $virtualServers = array(0 => self::translate("virtualserver_all_virtualservers"));
        foreach($resultset as $virtualServer){
            $virtualServers[$virtualServer->id] = $virtualServer->name;
        }
        return $virtualServers;
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
        return PendingHelpers::searchForPendingTokenInPendingArray($pendingToken,$pendingArray);
    }
    
}
