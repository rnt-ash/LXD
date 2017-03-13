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

class MonJobsLocal extends \RNTForest\core\models\ModelBase
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
    protected $physical_servers_id;
    
    /**
    * 
    * @var int
    */
    protected $virtual_servers_id;
    
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
    * ID of the Phsysical Server
    * 
    * @param integer $physicalServersId
    * @return $this
    */
    public function setPhysicalServerId($physicalServersId)
    {
        $this->physical_servers_id = $physicalServersId;
        return $this;
    }
    
    /**
    * ID of the Virtual Server
    * 
    * @param integer $physicalServersId
    * @return $this
    */
    public function setVirtualServerId($virtualServersId)
    {
        $this->virtual_servers_id = $virtualServersId;
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
    public function getPhysicalServersId()
    {
        return $this->physical_servers_id;
    }
    
    /**
    *
    * @return integer
    */
    public function getVirtualServersId()
    {
        return $this->virtual_servers_id;
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
}
