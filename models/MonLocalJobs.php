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
    protected $server_id;
    
    /**
    *
    * @var string
    */
    protected $server_class;
    
    /**
    * 
    * @var string
    */
    protected $mon_behavior_class;    
    
    /**
    * 
    * @var string
    */
    protected $mon_behavior_params;    
    
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
    * @param integer $serverId
    */
    public function setServerId($serverId)
    {
        $this->server_id = $serverId;
    }
    
    /**
    * Namespace and Classname of the Server Object
    * 
    * @param string $serverClass
    */
    public function setServerClass($serverClass)
    {
        $this->server_class = $serverClass;
    }
        
    /**
    * Namespace and classname of the behavior class
    * 
    * @param string $monBehaviorClass
    */
    public function setMonBehaviorClass($monBehaviorClass)
    {
        $this->mon_behavior_class = $monBehaviorClass;
    }
    
    /**
    * JSON encoded Array behavior params
    * 
    * @param string $monBehaviorParams
    */
    public function setMonBehaviorParams($monBehaviorParams)
    {
        $this->mon_behavior_params = $monBehaviorParams;
    }
    
    /**
    * Period in minutes
    * 
    * @param integer $monServicesCase
    */
    public function setPeriod($period)
    {
        $this->period = $period;
    }
    
    /**
    * Status
    * 
    * @param string $status
    */
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    /**
    * Last status change
    * 
    * @param string $lastStatusChange
    */
    public function setLastStatusChange($lastStatusChange)
    {
        $this->last_status_change = $lastStatusChange;
    }
    
    /**
    * Warning value
    * 
    * @param string $warningValue
    */
    public function setWarningValue($warningValue)
    {
        $this->warning_value = $warningValue;
    }
    
    /**
    * Maximal value
    * 
    * @param string $maximalValue
    */
    public function setMaximalValue($maximalValue)
    {
        $this->maximal_value = $maximalValue;
    }
    
    /**
    * Active
    *
    * @param integer $active
    */
    public function setActive($active)
    {
        $this->active = $active;
    }
    
    /**
    * Alarm
    * 
    * @param integer $alarm
    */
    public function setAlarm($alarm)
    {
        $this->alarm = $alarm;
    }
    
    /**
    * Alarmed
    * 
    * @param integer $alarmed
    */
    public function setAlarmed($alarmed)
    {
        $this->alarmed = $alarmed;
    } 
    
    /**
    * Muted
    * 
    * @param integer $muted
    */
    public function setMuted($muted)
    {
        $this->muted = $muted;
    } 
    
    /**
    * Last alarm
    * 
    * @param string $lastAlarm
    */
    public function setLastAlarm($lastAlarm)
    {
        $this->last_alarm = $lastAlarm;
    }
    
    /**
    * Alarm period in minutes
    * 
    * @param mixed $alarmPeriodInMinutes
    */
    public function setAlarmPeriod($alarmPeriod)
    {
        $this->alarm_period = $alarmPeriod;
    }
    
    /**
    * Message Contacts
    * 
    * @param string $monContactsMessage
    */
    public function setMonContactsMessage($monContactsMessage)
    {
        $this->mon_contacts_message = $monContactsMessage;
    }
    
    /**
    * Alarm Contacts
    * 
    * @param string $monContactsAlarm
    */
    public function setMonContactsAlarm($monContactsAlarm)
    {
        $this->mon_contacts_alarm = $monContactsAlarm;
    }
    
    /**
    * Last run
    * 
    * @param string $lastRun
    */
    public function setLastRun($lastRun)
    {
        $this->last_run = $lastRun;
    }
    
    /**
    * Modified
    * 
    * @param string $modified
    */
    public function setModified($modified)
    {
        $this->modified = $modified;
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
    public function getServerId()
    {
        return $this->server_id;
    }
    
    /**
    * returns the Namespace and Classname of the Server Object
    * 
    * @return string
    */
    public function getServerClass()
    {
        return $this->server_class;
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
    * returns mon behavior params
    * 
    * @return string
    */
    public function getMonBehaviorParams()
    {
        return $this->mon_behavior_params;
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
    public function getModified()
    {
        return $this->modified;
    }
    
    /**
    * set linked server
    * 
    */
    public function setServer(\RNTForest\ovz\interfaces\MonServerInterface $server){
        $this->server_class = get_class($server);
        $this->server_id = $server->getId();
    }
    
    /**
    * returns linked server
    * 
    * @return \RNTForest\ovz\interfaces\MonServerInterface
    */
    public function getServer(){
        $server = $this->server_class::findFirst($this->server_id);
        if(!$server){
            $this->getLogger()->error('Server could not been instantiated. ServerClass: '.$this->server_class.' with ID: '.$this->server_id);
        }
        return $server;
    }
    
    /**
    * 
    * @return string[]
    */
    public function getMonContactsAlarmMailaddresses(){
        $contactIds = explode(',',$this->mon_contacts_alarm);
        $contactMailaddresses = array();
        foreach($contactIds as $contactId){
            $login = \RNTForest\core\models\Logins::findFirst(intval($contactId));
            $contactMailaddresses[] = $login->getEmail();
        }
        return $contactMailaddresses;
    }
    
    /**
    * 
    * @return string[]
    */
    public function getMonContactsMessageMailaddresses(){
        $contactIds = explode(',',$this->mon_contacts_message);
        $contactMailaddresses = array();
        foreach($contactIds as $contactId){
            $login = \RNTForest\core\models\Logins::findFirst(intval($contactId));
            $contactMailaddresses[] = $login->getEmail();
        }
        return $contactMailaddresses;
    }
    
    public function execute(){
        $statusBefore = $this->getStatus();
        
        $server = $this->getServer();
        $ovzStatistics = $server->getOvzStatistics();
        
        $decodedOvzStatistics = json_decode($ovzStatistics,true);
        $timestamp = '';
        if(is_array($decodedOvzStatistics) && key_exists('Timestamp',$decodedOvzStatistics)){
            $timestamp = $decodedOvzStatistics['Timestamp'];    
        }
         
        // if model is older than 1 minute write this to log, should not be
        if(empty($timestamp) || Helpers::createUnixTimestampFromDateTime($timestamp) < (time()-300)){
            $this->getLogger()->notice($this->translate("monitoring_monlocaljobs_statistics_timestamp_to_old").'(MonLocalJob '.$this->getId().', Timestamp: '.$timestamp.')');
        }else{
            $value = '';        
            $behavior = new $this->mon_behavior_class();
            if(!($behavior instanceof MonLocalBehaviorInterface)){
                throw new \Exception($this->translate("monitoring_mon_behavior_not_implements_interface"));    
            }    
            
            $valuestatus = $behavior->execute($ovzStatistics,$this->mon_behavior_params,$this->warning_value,$this->maximal_value);
            if($valuestatus == null){
                throw new \Exception($this->translate("monitoring_mon_behavior_could_not_instantiate_valuestatus"));
            }
            $monLog = new MonLocalLogs();
            $monLog->create(["mon_local_jobs_id" => $this->id, "value" => $valuestatus->getValue(), "modified" => date('Y-m-d H:i:s')]);
            $monLog->save();
            
            $this->status = $statusAfter = $valuestatus->getStatus();
            
            if($statusBefore != $statusAfter){
                $this->setLastStatusChange(date('Y-m-d H:i:s'));    
            }
            
            $this->setLastRun(date('Y-m-d H:i:s'));
            $this->save();    
        }
    }
    
    public function genMonLocalDailyLogs(){
        MonLocalDailyLogsGenerator::genLocalDailyLogs($this);    
    }
    
    /**
    * Get the MonLocalLogs of this week in hour interval.
    * Convenience method which wraps getMonLogs.
    * 
    * @return array 
    */
    public function getMonLogsThisWeekHourly(){
        $start = new \DateTime();
        $start->setTimestamp(strtotime("this week"));
        $end = new \DateTime();
        $end->setTimestamp(strtotime("now"));
        return $this->getMonLogs($start,$end,"hourly");
    }
    
    /**
    * Get the MonLocalLogs of this month in hour interval.
    * Convenience method which wraps getMonLogs.
    * 
    * @return array 
    */
    public function getMonLogsThisMonthHourly(){
        $start = new \DateTime();
        $start->setTimestamp(strtotime("first day of this month"));
        $end = new \DateTime();
        $end->setTimestamp(strtotime("now"));
        return $this->getMonLogs($start,$end,"hourly");
    }
    
    /**
    * Get the MonLocalLogs of this month in day interval.
    * Convenience method which wraps getMonLogs.
    * 
    * @return array 
    */
    public function getMonLogsThisMonthDaily(){
        $start = new \DateTime();
        $start->setTimestamp(strtotime("first day of this month"));
        $end = new \DateTime();
        $end->setTimestamp(strtotime("now"));
        return $this->getMonLogs($start,$end,"daily");
    }
    
    /**
    * Get the MonLocalLogs of this year in day interval.
    * Convenience method which wraps getMonLogs.
    * 
    * @return array 
    */
    public function getMonLogsThisYearDaily(){
        $start = new \DateTime();
        $start->setTimestamp(strtotime("first day of this year"));
        $end = new \DateTime();
        $end->setTimestamp(strtotime("now"));
        return $this->getMonLogs($start,$end,"daily");
    }
    
    /**
    * Get MonLocalLogs between start and end datetime in a specific interval/unit.
    * Especially for unit all and hourly it is only possible to get Logs wich are in MonLocalLogs (not older than last month).
    * For unti daily and weekly it can be further gotten from the older MonLocalDailyLogs.
    * 
    * @param \DateTime $start
    * @param \DateTime $end
    * @param mixed $unit all, hourly, daily, weekly
    * @return MonLocalLogs[]
    */
    public function getMonLogs(\DateTime $start, \DateTime $end, $unit){
        if(!($unit == 'all' || $unit == 'hourly' || $unit == 'daily')){
            throw new \Exception($this->translate("monitoring_monlocaljobs_no_valid_unit"));
        }
        if($start->getTimestamp() > $end->getTimestamp()){
            throw new \Exception($this->translate("monitoring_monlocaljobs_end_before_start"));
        }
        
        $neededMonLogs = array();
        
        // search only in MonLocalLogs if all or hourly 
        if($unit == 'all' || $unit == 'hourly'){
            // BETWEEN is equivalent to the expression (min <= expr AND expr <= max) (see mysql doc)
            $monLogs = MonLocalLogs::find(
                [
                    "mon_local_jobs_id = :id: AND modified BETWEEN :start: AND :end:",
                    "order" => "modified ASC",
                    "bind" => [
                        "id" => $this->getId(),
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
                    ],
                ]
            );

            if($unit == 'hourly'){
                $hourlyLogs = array();
                $sum = $count = 0;
                $lastDay = $thisDay = '0000-00-00 00';

                $hourStart = new \DateTime($start->format('Y-m-d H').':00:00');
                $hourEnd = new \DateTime($end->format('Y-m-d H').':00:00');

                // initialize array with all needed keys
                while($hourStart->getTimestamp() <= $hourEnd->getTimestamp()){
                    $hourlyLogs[$hourStart->format('Y-m-d H')] = null;
                    // DateInterval explained: Period Time Interval 1 Hour
                    $hourStart->add(new \DateInterval('PT1H'));
                }

                foreach($monLogs as $monLog){
                    $modified = new \DateTime($monLog->getModified());
                    $thisDay = $modified->format('Y-m-d H');    

                    if($lastDay != $thisDay){
                        if($count > 0){
                            $average = $sum/$count;
                            $hourlyLogs[$lastDay] = "$average";
                            $sum = $count = 0;
                        }
                        $lastDay = $thisDay;
                    }

                    $sum += $monLog->getValue();
                    $count ++;

                }
                // don't forget the last...
                if($count > 0){
                    $average = $sum/$count;
                    $hourlyLogs[$lastDay] = "$average";    
                }

                $neededMonLogs = $hourlyLogs;
            }elseif("all"){
                foreach($monLogs as $monLog){
                    $neededMonLogs[$monLog->getModified()] = $monLog->getValue();
                }                
            }
        }
        // search in MonLocalDailyLogs and MonLocalLogs if daily
        else{
            if($unit == 'daily'){
                $dailyLogs = array();
                
                $dayStart = new \DateTime($start->format('Y-m-d'));
                $dayEnd = new \DateTime($end->format('Y-m-d'));

                // initialize array with all needed keys
                while($dayStart->getTimestamp() <= $dayEnd->getTimestamp()){
                    $dailyLogs[$dayStart->format('Y-m-d')] = null;
                    // DateInterval explained: Period Interval 1 Day
                    $dayStart->add(new \DateInterval('P1D'));
                }
               
                // BETWEEN is equivalent to the expression (min <= expr AND expr <= max) (see mysql doc)
                $monDailyLogs = MonLocalDailyLogs::find(
                    [
                        "mon_local_jobs_id = :id: AND day BETWEEN :start: AND :end:",
                        "order" => "modified ASC",
                        "bind" => [
                            "id" => $this->getId(),
                            "start" => $start->format('Y-m-d'),
                            "end" => $end->format('Y-m-d'),
                        ],
                    ]
                );
                
                // MonLocalDailyLogs can be directly inserted to the representative keys
                foreach($monDailyLogs as $monDailyLog){
                    $dailyLogs[$monDailyLog->getDay()] = $monDailyLog->getValue();       
                }
                
                // BETWEEN is equivalent to the expression (min <= expr AND expr <= max) (see mysql doc)
                $monLogs = MonLocalLogs::find(
                    [
                        "mon_local_jobs_id = :id: AND modified BETWEEN :start: AND :end:",
                        "order" => "modified ASC",
                        "bind" => [
                            "id" => $this->getId(),
                            "start" => $start->format('Y-m-d'),
                            "end" => $end->format('Y-m-d'),
                        ],
                    ]
                );
                
                // for the rest the average of this day is calculated
                $sum = $count = 0;
                $lastDay = $thisDay = '0000-00-00';

                foreach($monLogs as $monLog){
                    $modified = new \DateTime($monLog->getModified());
                    $thisDay = $modified->format('Y-m-d');    

                    if($lastDay != $thisDay){
                        if($count > 0){
                            $average = $sum/$count;
                            $dailyLogs[$lastDay] = "$average";
                            $sum = $count = 0;
                        }
                        $lastDay = $thisDay;
                    }

                    $sum += $monLog->getValue();
                    $count ++;

                }
                // don't forget the last...
                if($count > 0){
                    $average = $sum/$count;
                    $dailyLogs[$lastDay] = "$average";    
                }

                $neededMonLogs = $dailyLogs;
            }    
        }        
        
        return $neededMonLogs;
    }
    
    /**
    * @return \Phalcon\Logger\AdapterInterface
    */
    private function getLogger(){
        return $this->getDI()['logger'];
    }
}
