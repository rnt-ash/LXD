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

use RNTForest\ovz\interfaces\MonLocalBehaviorInterface;
use RNTForest\ovz\utilities\MonLocalDailyLogsGenerator;
use RNTForest\ovz\models\MonLogsLocal;
use RNTForest\core\libraries\Helpers;

class MonLocalJobs extends \RNTForest\core\models\ModelBase
{
    public static $STATENORMAL = 'normal';
    public static $STATEMAXIMAL = 'maximal';
    public static $STATEWARNING = 'warning';
    
    /**
    * 
    * @var integer
    * @Primary
    * @Identity
    */
    protected $id;
    
    /**
    * 
    * @var int
    */
    protected $servers_id;
    
    /**
    *
    * @var string
    */
    protected $servers_class;
    
    /**
    * 
    * @var string
    */
    protected $mon_behavior_class;    
    
    /**
    * 
    * @var int
    */
    protected $period;
    
    /**
    * 
    * @var string
    */
    protected $status;   
    
    /**
    * 
    * @var string
    */
    protected $last_status_change;
    
    /**
    * 
    * @var string
    */
    protected $warning_value;
    
    /**
    * 
    * @var string
    */
    protected $maximal_value;
    
    /**
    * 
    * @var integer
    */
    protected $active;
    
    /**
    * 
    * @var integer
    */
    protected $alarm;
    
    /**
    * 
    * @var integer
    */
    protected $alarmed;
    
    /**
    * 
    * @var integer
    */
    protected $muted;
    
    /**
    * 
    * @var string
    */
    protected $last_alarm;
    
    /**
    * 
    * @var integer
    */
    protected $alarm_period;
    
    /**
    * 
    * @var string
    */
    protected $mon_contacts_message;
    
    /**
    * 
    * @var string
    */
    protected $mon_contacts_alarm;
    
    /**
    * 
    * @var string
    */
    protected $last_run;
    
    /**
    * 
    * @var string
    */
    protected $last_rrd_run;
    
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
        return $this;
    }

    /**
    * ID of the Server
    * 
    * @param integer $serversId
    * @return $this
    */
    public function setServersId($serversId)
    {
        $this->servers_id = $serversId;
        return $this;
    }
    
    /**
    * Namespace and Classname of the Server Object
    * 
    * @param string $serverClass
    */
    public function setServersClass($serverClass)
    {
        $this->servers_class = $serverClass;
        return $this;
    }
        
    /**
    * Namespace and classname of the behavior class
    * 
    * @param string $monBehaviorClass
    * @return $this
    */
    public function setMonBehaviorClass($monBehaviorClass)
    {
        $this->mon_behavior_class = $monBehaviorClass;
        return $this;
    }
    
    /**
    * Period in minutes
    * 
    * @param integer $monServicesCase
    * @return $this
    */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }
    
    /**
    * Status
    * 
    * @param string $status
    * @return $this
    */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    
    /**
    * Last status change
    * 
    * @param string $lastStatusChange
    * @return $this
    */
    public function setLastStatusChange($lastStatusChange)
    {
        $this->last_status_change = $lastStatusChange;
        return $this;
    }
    
    /**
    * Warning value
    * 
    * @param string $warningValue
    * @return $this
    */
    public function setWarningValue($warningValue)
    {
        $this->warning_value = $warningValue;
        return $this;
    }
    
    /**
    * Maximal value
    * 
    * @param string $maximalValue
    * @return $this
    */
    public function setMaximalValue($maximalValue)
    {
        $this->maximal_value = $maximalValue;
        return $this;
    }
    
    /**
    * Active
    *
    * @param integer $active
    * @return $this
    */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }
    
    /**
    * Alarm
    * 
    * @param integer $alarm
    * @return $this
    */
    public function setAlarm($alarm)
    {
        $this->alarm = $alarm;
        return $this;
    }
    
    /**
    * Alarmed
    * 
    * @param integer $alarmed
    * @return $this
    */
    public function setAlarmed($alarmed)
    {
        $this->alarmed = $alarmed;
        return $this;
    } 
    
    /**
    * Muted
    * 
    * @param integer $muted
    * @return $this
    */
    public function setMuted($muted)
    {
        $this->muted = $muted;
        return $this;
    } 
    
    /**
    * Last alarm
    * 
    * @param string $lastAlarm
    * @return $this
    */
    public function setLastAlarm($lastAlarm)
    {
        $this->last_alarm = $lastAlarm;
        return $this;
    }
    
    /**
    * Alarm period in minutes
    * 
    * @param mixed $alarmPeriodInMinutes
    * @return $this
    */
    public function setAlarmPeriod($alarmPeriod)
    {
        $this->alarm_period = $alarmPeriod;
        return $this;
    }
    
    /**
    * Message Contacts
    * 
    * @param string $monContactsMessage
    * @return $this
    */
    public function setMonContactsMessage($monContactsMessage)
    {
        $this->mon_contacts_message = $monContactsMessage;
        return $this;
    }
    
    /**
    * Alarm Contacts
    * 
    * @param string $monContactsAlarm
    * @return $this
    */
    public function setMonContactsAlarm($monContactsAlarm)
    {
        $this->mon_contacts_alarm = $monContactsAlarm;
        return $this;
    }
    
    /**
    * Last run
    * 
    * @param string $lastRun
    * @return $this
    */
    public function setLastRun($lastRun)
    {
        $this->last_run = $lastRun;
        return $this;
    }
    
    /**
    * Last rrd run
    * 
    * @param string $lastRrdRun
    * @return $this
    */
    public function setLastRrdRun($lastRrdRun)
    {
        $this->last_rrd_run = $lastRrdRun;
        return $this;
    }
    
    /**
    * Modified
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
    *
    * @return integer
    */
    public function getId()
    {
        return $this->id;
    }
    
    /**
    *
    * @return integer
    */
    public function getServersId()
    {
        return $this->servers_id;
    }
    
    /**
    * returns the Namespace and Classname of the Server Object
    * 
    * @return string
    */
    public function getServersClass()
    {
        return $this->servers_class;
    }
    
    /**
    * returns the Namespace and Classname of the mon behavior class
    * 
    * @return string
    */
    public function getMonBehaviorClass()
    {
        return $this->mon_behavior_class;
    }
    
    /**
    * Returns period in minutes
    * 
    * @return string
    */
    public function getPeriod()
    {
        return $this->period;
    }
    
    /**
    *
    * @return string
    */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
    *
    * @return string
    */
    public function getLastStatusChange()
    {
        return $this->last_status_change;
    }
    
    /**
    *
    * @return string
    */
    public function getWarningValue()
    {
        return $this->warning_value;
    }
    
    /**
    *
    * @return string
    */
    public function getMaximalValue()
    {
        return $this->maximal_value;
    }
    
    /**
    *
    * @return integer
    */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
    *
    * @return integer
    */
    public function getAlarm()
    {
        return $this->alarm;
    }
    
    /**
    *
    * @return integer
    */
    public function getAlarmed()
    {
        return $this->alarmed;
    }
    
    /**
    *
    * @return integer
    */
    public function getMuted()
    {
        return $this->muted;
    }
    
    /**
    *
    * @return string
    */
    public function getLastAlarm()
    {
        return $this->last_alarm;
    }
    
    /**
    * Return alarm period in minutes
    * 
    * @return integer
    */
    public function getAlarmPeriod()
    {
        return $this->alarm_period;
    }
    
    /**
    *
    * @return string
    */
    public function getMonContactsMessage()
    {
        return $this->mon_contacts_message;
    }
    
    /**
    *
    * @return string
    */
    public function getMonContactsAlarm()
    {
        return $this->mon_contacts_alarm;
    }
    
    /**
    *
    * @return string
    */
    public function getLastRun()
    {
        return $this->last_run;
    }
    
    /**
    *
    * @return string
    */
    public function getLastRrdRun()
    {
        return $this->last_rrd_run;
    }
    
    /**
    *
    * @return string
    */
    public function getModified()
    {
        return $this->modified;
    }
    
    /**
    * set linked server
    * 
    */
    public function setServer(\RNTForest\ovz\interfaces\MonServerInterface $server){
        $this->servers_class = get_class($server);
        $this->servers_id = $server->getId();
    }
    
    /**
    * returns linked server
    * 
    * @return \RNTForest\ovz\interfaces\MonServerInterface
    */
    public function getServer(){
        return $this->servers_class::findFirst($this->servers_id);
    }
    
    /**
    * 
    * @return \RNTForest\ovz\models\MonContacts[]
    */
    public function getMonContactsAlarmInstances(){
        $contactIds = explode(',',$this->mon_contacts_alarm);
        $contactInstances = array();
        foreach($contactIds as $contactId){
            $contactInstances[] = \RNTForest\ovz\models\MonContacts::findFirst(intval($contactId));
        }
        return $contactInstances;
    }
    
    /**
    * 
    * @return \RNTForest\ovz\models\MonContacts[]
    */
    public function getMonContactsMessageInstances(){
        $contactIds = explode(',',$this->mon_contacts_message);
        $contactInstances = array();
        foreach($contactIds as $contactId){
            $contactInstances[] = \RNTForest\ovz\models\MonContacts::findFirst(intval($contactId));
        }
        return $contactInstances;
    }
    
    public function execute(){
        $statusBefore = $this->getStatus();
        
        $server = $this->getServer();
        $ovzStatistics = $server->getOvzStatistics();
        
        $decodedOvzStatistics = json_decode($ovzStatistics);
        $modified = '';
        if(is_array($decodedOvzStatistics) && key_exists('modified',$decodedOvzStatistics)){
            $modified = $decodedOvzStatistics['modified'];    
        }
         
        // if model is older than 1 minute update the ovz_statistics with a job
        if(empty($modified) || Helpers::createUnixTimestampFromDateTime($modified) < (time()-60)){
            $server->updateOvzStatistics();
            $server->refresh();  
        }
        
        $value = '';        
        $behavior = new $this->mon_behavior_class();
        if(!($behavior instanceof MonLocalBehaviorInterface)){
            throw new \Exception($this->translate("monitoring_mon_behavior_not_implements_interface"));    
        }    
        
        $valuestatus = $behavior->execute($ovzStatistics,$this->warning_value,$this->maximal_value);
        $monLog = new MonLocalLogs();
        $monLog->create(["mon_local_jobs_id" => $this->id, "value" => $valuestatus->getValue()]);
        $monLog->save();
        
        
        $this->status = $statusAfter = $valuestatus->getStatus();
        
        if($statusBefore != $statusAfter){
            $this->setLastStatusChange(date('Y-m-d H:i:s'));    
        }
        
        $this->setLastRun(date('Y-m-d H:i:s'));
        
        $this->save();
    }
    
    public function genMonLocalDailyLogs(){
        MonLocalDailyLogsGenerator::genLocalDailyLogs($this);    
    }
}
