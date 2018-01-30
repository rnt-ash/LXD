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
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Validation\Validator\Date as DateValidator;


class MonLogsBase extends \RNTForest\core\models\ModelBase
{
    /**
    * 
    * @var integer
    */
    protected $mon_jobs_id;
    
    /**
    * 
    * @var string
    */
    protected $value;
    
    /**
    * 
    * @var integer
    */
    protected $heal_job;
    
    /**
    * ID of the monjob
    * 
    * @param integer $monJobsId
    */
    public function setMonJobsId($monJobsId)
    {
        $this->mon_jobs_id = $monJobsId;
    }
    
    /**
    * Value
    * 
    * @param string $value
    */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
    * ID of the healJob
    * 
    * @param integer $healJob
    */
    public function setHealJob($healJob)
    {
        $this->heal_job = $healJob;
    }
    
    /**              
    * 
    * @return integer
    */
    public function getMonJobsRemoteId()
    {
        return $this->mon_jobs_id;
    }
    
    /**
    * 
    * @return string
    */
    public function getValue()
    {
        return $this->value;
    }
    /**
    * 
    * @return integer
    */
    public function getHealJob()
    {
        return $this->heal_job;
    }
    
    public function onConstruct(){
        // Default Values
        parent::onConstruct();
    }
    
    /**
    * Initialize method for model.
    */
    public function initialize()
    {
        // do not inherit from ModelBase because of timestampable behavior ist not allowed here !!!

        // relations    
        $this->hasOne("virtual_servers_id",'RNTForest\lxd\models\VirtualServers',"id",array("alias"=>"VirtualServer", "foreignKey"=>true));
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
        
        // business logic
        // no business logic until now
        
        return true;
    }
    
    /**
    * generates validator for MonLog model
    * 
    * return \Phalcon\Validation $validator
    * 
    */
    public static function generateValidator($session){
        // validator
        $validator = new Validation();

        // mon_jobs_id
        $validator->add('mon_jobs_id', new PresenceOfValidator([
            'message' => self::translate("monlogs_monjobsid_required")
        ]));
               
        // value
        
        // heal_job
        
        // modified
        $validator->add('modified', new PresenceOfValidator([
            'message' => self::translate("monlogs_modified_required"),
        ]));
        $validator->add('modified', new DateValidator([
            'format' => "Y-m-d H:i:s",
            'message' => self::translate("monlogs_modified_format"), 
        ]));

        return $validator;
    }
}
