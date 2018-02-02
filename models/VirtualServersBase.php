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
use Phalcon\Validation\Validator\Confirmation as ConfirmationValidator;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Message as Message;

use RNTForest\core\interfaces\JobServerInterface;
use RNTForest\core\interfaces\PendingInterface;
use RNTForest\lxd\interfaces\MonServerInterface;
use RNTForest\lxd\interfaces\IpServerInterface;
use RNTForest\core\libraries\PendingHelpers;
use RNTForest\lxd\functions\Monitoring;
use RNTForest\core\models\Customers;


/**
* @property \RNTForest\core\models\Customers $Customer
* @property \RNTForest\lxd\models\PhysicalServers $VirtualServers
* @property \RNTForest\lxd\models\PhysicalServersHws $VirtualServersHws
* 
*/
class VirtualServersBase extends \RNTForest\core\models\ModelBase implements JobServerInterface, PendingInterface, IpServerInterface
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
    protected $lxd; 
    
    /**
    *
    * @var string
    */
    protected $lxd_settings;
    
    /**
    *
    * @var string
    */
    protected $lxd_snapshots;

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
    * Name of the virtual server
    *
    * @param string $name
    */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
    * Description
    *
    * @param string $description
    */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
    * Foreign key: Customers
    *
    * @param integer $customers_id
    */
    public function setCustomersId($customers_id)
    {
        $this->customers_id = $customers_id;
    }

    /**
    * Foreign key: PhysicalServers
    *
    * @param integer $physical_servers_id
    */
    public function setPhysicalServersId($physical_servers_id)
    {
        $this->physical_servers_id = $physical_servers_id;
    }

    /**
    * Public key (OpenSSL)
    *
    * @param string $job_public_key
    */
    public function setJobPublicKey($job_public_key)
    {
        $this->job_public_key = $job_public_key;
    }

    /**
    * Virtual server is LXD guest
    *
    * @param int $lxd
    */
    public function setLxd($lxd)
    {
        $this->lxd = $lxd;
    }
    
    /**
    * LXD settings as JSON
    *
    * @param string $lxd_settings
    */
    public function setLxdSettings($lxd_settings)
    {
        $this->lxd_settings = $lxd_settings;
    }
    
    /**
    * LXD snapshots as JSON
    *
    * @param string $lxd_snapshots
    */
    public function setLxdSnapshots($lxd_snapshots)
    {
        $this->lxd_snapshots = $lxd_snapshots;
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
    * Returns the value of field lxd
    *
    * @return integer
    */
    public function getLxd()
    {
        return $this->lxd;
    }
    
    /**
    * Returns the value of field lxd_settings
    *
    * @return integer
    */
    public function getLxdSettings()
    {
        return $this->lxd_settings;
    }
    
    /**
    * Returns the lxd_settings as Array
    * 
    * @return array
    */
    public function getLxdSettingsArray()
    {
        return json_decode($this->lxd_settings,true);
    }
    
    /**
    * Returns the value of field lxd_snapshots
    *
    * @return string
    */
    public function getLxdSnapshots()
    {
        return $this->lxd_snapshots;
    }
    
    /**
    * Return the value of field lxd_snapshots as array
    * 
    * @return array
    */
    public function getLxdSnapshotsArray()
    {
        return json_decode($this->lxd_snapshots,true);
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
    * get all IpObjects of this virtual server
    * 
    * @return \RNTForest\lxd\models\IpObjects
    *     
    */
    public function getIpObjects(){
        $server_class = addslashes('\RNTForest\lxd\models\VirtualServers');
        $resultset = IpObjects::find(["conditions"=>"server_class = '".$server_class."' AND server_id = '".$this->id."'"]);
        return $resultset;
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
        $this->belongsTo("physical_servers_id",'RNTForest\lxd\models\PhysicalServers',"id",array("alias"=>"PhysicalServers", "foreignKey"=>true));
    }
    
    public function onConstruct(){
        // inherit from parent
        parent::onConstruct();
    }

    /**
    * Validations and business logic
    *
    * @return boolean
    */
    public function validation()
    {
        // get params from session
        $session = $this->getDI()->get("session")->get("VirtualServersForm");
        $op = $session['op'];
        $vstype = $session['vstype'];

        // check if selected customer exists
        if(!Customers::findFirst($this->customers_id) OR empty($this->customers_id)){
            $message = new Message($this->translate("virtualserver_customer_not_exist"),"customers");            
            $this->appendMessage($message);
            return false;
        }
        
        $validator = $this->generateValidator($op,$vstype);
        if(!$this->validate($validator)) return false;
        
        // linebreaks are not allowed in description
        $this->description = str_replace(array("\r", "\n"), ' ', $this->description);

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
        * Link: https://docs.openvz.org/openvz_command_line_reference.webhelp/_miscellaneous_parameters.html 
        * 
        * Due to the need of points in the name and no known downside, we allow the usage of points! 
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
            'pattern' => '/^[a-zA-Z0-9\-_\s\.]*$/',
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

        return $validator;
    }
    
    public function getLxdStatus(){
        return json_decode($this->lxd_settings,true)['status'];
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
    * generate an array for an select element, considered the permission scope
    * 
    * @param string $scope
    */
    public static function generateArrayForSelectElement($scope){
        $findParameters = array("columns"=>"id, name","order"=>"name");
        $resultset = self::findFromScope($scope,$findParameters);
        $virtualServers = array(0 => self::translate("virtualserver_all_virtualservers"));
        foreach($resultset as $virtualServer){
            $virtualServers[$virtualServer->id] = $virtualServer->name;
        }
        return $virtualServers;
    }

    /**
    * Getter for parent class.
    * needed because of MonServer Interface so that monitoring can instantiate a parent object.
    * 
    */
    public function getParentClass(){
        return '\RNTForest\lxd\models\PhysicalServers';
    }
    
    /**
    * Getter for parent id.
    * needed because of MonServer Interface so that monitoring can instantiate a parent object.
    * 
    */
    public function getParentId(){
        return $this->physical_servers_id;
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
