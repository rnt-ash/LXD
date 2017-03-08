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

class RemoteMonJobs extends \RNTForest\core\models\ModelBase
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
    * @var int
    */
    protected $colocations_id;
    
    /**
    * 
    * @var string
    */
    protected $main_ip;
    
    /**
    * 
    * @var integer
    */
    protected $mon_services_id;
    
    /**
    * 
    * @var int
    */
    protected $mon_services_case;
    
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
    protected $uptime;
    
    /**
    * 
    * @var integer
    */
    protected $active;
    
    /**
    * 
    * @var integer
    */
    protected $healing;
    
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
    * ID of the Colocation
    * 
    * @param integer $colocationsId
    * @return $this
    */
    public function setColocationsId($colocationsId)
    {
        $this->colocations_id = $colocationsId;
        return $this;
    }
    
    /**
    * Main IP
    * 
    * @param integer $mainIp
    * @return $this
    */
    public function setMainIp($mainIp)
    {
        $this->main_ip = $mainIp;
        return $this;
    }
    
    /**
    * ID of the mon service
    * 
    * @param integer $monServicesId
    * @return $this
    */
    public function setMonServicesId($monServicesId)
    {
        $this->mon_services_id = $monServicesId;
        return $this;
    }
    
    /**
    * Mon service case
    * 
    * @param integer $monServicesCase
    * @return $this
    */
    public function setMonServicesCase($monServicesCase)
    {
        $this->mon_services_case = $monServicesCase;
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
    * Uptime
    * 
    * @param string $uptime
    * @return $this
    */
    public function setUptime($uptime)
    {
        $this->uptime = $uptime;
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
    * Healing
    *
    * @param integer $healing
    * @return $this
    */
    public function setHealing($healing)
    {
        $this->healing = $healing;
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
    *
    * @return integer
    */
    public function getColocationsId()
    {
        return $this->colocations_id;
    }
    
    /**
    *
    * @return string
    */
    public function getMainIp()
    {
        return $this->main_ip;
    }
    
    /**
    *
    * @return integer
    */
    public function getMonServicesId()
    {
        return $this->mon_services_id;
    }
    
    /**
    *
    * @return integer
    */
    public function getMonServicesCase()
    {
        return $this->mon_services_case;
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
    public function getUptime()
    {
        return $this->uptime;
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
    public function getHealing()
    {
        return $this->healing;
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
        return $this->alarmed;
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
    * @return string commaseparated
    */
    public function getMonContactsMessage()
    {
        return $this->mon_contacts_message;
    }
    
    /**
    *
    * @return string commaseparated
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
    public function getModified()
    {
        return $this->modified;
    }
    
    /**
    * Initialize method for model.
    */
    public function initialize(){
        $this->hasMany("id",'RNTForest\ovz\models\MonUptimes',"remote_mon_jobs_id",array("alias"=>"MonUptimes", "foreignKey"=>array("allowNulls"=>true)));
        $this->hasMany("id",'RNTForest\ovz\models\RemoteMonLogs',"remote_mon_jobs_id",array("alias"=>"RemoteMonLogs", "foreignKey"=>array("allowNulls"=>true)));
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
}
