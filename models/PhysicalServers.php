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

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Mvc\Model\Behavior\Timestampable;

class PhysicalServers extends \Phalcon\Mvc\Model implements JobServerInterface, PendingInterface
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
    protected $public_key;

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
    * Method to set the value of field public_key
    *
    * @param string $public_key
    * @return $this
    */
    public function setPublicKey($public_key)
    {
        $this->public_key = $public_key;

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
    * Returns the value of field public_key
    *
    * @return string
    */
    public function getPublicKey()
    {
        return $this->public_key;
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
        $this->belongsTo("customers_id","Customers","id",array("foreignKey"=>true));
        $this->belongsTo("colocations_id","Colocations","id",array("foreignKey"=>true));
        $this->hasMany("id","VirtualServers","physical_servers_id",array("foreignKey"=>array("allowNulls"=>true)));
        $this->hasMany("id","Dcoipobjects","physical_servers_id",array("foreignKey"=>array("allowNulls"=>true)));
        $this->hasMany("id","Jobs","physical_servers_id",array("foreignKey"=>array("allowNulls"=>true)));

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
        $validator->add('name', new PresenceOfValidator([
            'message' => 'name is required'
        ]));        

        $validator->add('name', new StringLengthValitator([
            'max' => 50,
            'min' => 3,
            'messageMaximum' => 'name too long',
            'messageMinimum' => 'name too small',
        ]));

        $validator->add('name', new RegexValidator([
            'pattern' => '/^[a-zA-Z0-9\-_\s]*$/',
            'message' => 'Name must be alphanumeric and may contain the characters \, -, _ and space.'
        ]));        

        // fqdn
        $validator->add('fqdn', new PresenceOfValidator([
            'message' => 'fqdn is required'
        ]));        

        $validator->add('fqdn', new RegexValidator([
            'pattern' => '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/',
            'message' => 'must be a string separated by points'
        ]));        

        // core
        $validator->add('core', new PresenceOfValidator([
            'message' => 'core is required'
        ]));        

        // memory
        $validator->add('memory', new PresenceOfValidator([
            'message' => 'memory is required'
        ]));        

        // space
        $validator->add('space', new PresenceOfValidator([
            'message' => 'space is required'
        ]));        


        return $validator;
    }
    
    public function addPending($pendingToken){
        $pendingArray = json_decode($this->pending,true);
        $pendingArray[] = $pendingToken;        
        $this->pending = json_encode($pendingArray);
        $this->save();
    }
    
    public function removePending($pendingToken){
        $pendingArray = json_decode($this->pending,true);
        $this->pending = json_encode(PendingHelpers::removePendingTokenInPendingArray($pendingToken,$pendingArray));
        $this->save();
    }
    
    public function isPending($pendingToken=''){
        $pendingArray = json_decode($this->pending,true);
        return PendingHelpers::searchForPendingTokenInPendingArray($pendingToken,$pendingArray);
    }
}
