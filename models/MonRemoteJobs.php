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

use RNTForest\ovz\interfaces\MonBehaviorInterface;
use RNTForest\ovz\models\MonLogsRemote;
use RNTForest\ovz\models\MonUptimes;
use RNTForest\core\libraries\Helpers;
use RNTForest\ovz\datastructures\DowntimePeriod;
use RNTForest\ovz\utilities\MonUptimesGenerator;

class MonRemoteJobs extends \RNTForest\core\models\ModelBase
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
    protected $main_ip;
    
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
    * Transient, will not be persisted.
    * 
    * @var integer
    */
    protected $recent_healjob_id = 0;
    
    /**
    * Unique ID
    *
    * @param integer $id
    */
    public function setId($id)
    {
        $this->id = $id;
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
    * Main IP
    * 
    * @param string $mainIp
    */
    public function setMainIp($mainIp)
    {
        $this->main_ip = $mainIp;
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
    * Uptime
    * 
    * @param string $uptime
    */
    public function setUptime($uptime)
    {
        $this->uptime = $uptime;
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
    * Healing
    *
    * @param integer $healing
    */
    public function setHealing($healing)
    {
        $this->healing = $healing;
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
    * Set the transient recent healjob id
    * 
    * @param integer $healJobId
    */
    public function setRecentHealJobId($healJobId)
    {
        $this->recent_healjob_id = $healJobId;
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
    *
    * @return string
    */
    public function getMainIp()
    {
        return $this->main_ip;
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
    * get the transient attribute recent heal job
    * 
    * @return integer
    */
    public function getRecentHealJobId()
    {
        return $this->recent_healjob_id;
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
        return $this->server_class::findFirst($this->server_id);
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

        $behavior = new $this->mon_behavior_class();
        if(!($behavior instanceof MonBehaviorInterface)){
            throw new \Exception($this->translate("monitoring_mon_behavior_not_implements_interface"));    
        }

        // update MainIp from Server Object in case it has changed since last execute
        $server = $this->getServer(); 
        $ipaddress = $server->getMainIp();

        if($ipaddress === false){
            $this->getLogger()->notice($this->translate("monitoring_monremotejobs_no_ip")." MonRemoteJobId: ".$this->getId());
        }else{

            $this->setMainIp($ipaddress->toString());

            $statusAfter = $behavior->execute($this->getMainIp());
            $monLog = new MonRemoteLogs();
            $monLog->create(["mon_remote_jobs_id" => $this->id, "value" => $statusAfter, "modified" => date('Y-m-d H:i:s')]);
            $monLog->save();

            if($statusAfter == "1"){
                $this->status = "up";
            }else{
                $this->status = "down";
            }

            if($statusBefore != $statusAfter){
                $this->setLastStatusChange(date('Y-m-d H:i:s'));    
            }

            $this->setLastRun(date('Y-m-d H:i:s'));

            $this->save();
        }
    }
    
    /**
    * Returns true if this MonJob is in error state.
    * 
    * @return boolean
    */
    public function isInErrorState(){
        return $this->Status == 'down';
    }
    
    public function recomputeUptime(){
        $monUptimes = MonUptimes::find(
            [
                "mon_remote_jobs_id = :id:",
                "bind" => [
                    "id" => $this->getId(),
                ],
            ]
        );
        
        $actYear = date('Y');
        $firstDayOfActYear = strtotime('first day of January '.$actYear);
        $everUpSeconds = $everMaxSeconds = $everUpPercentage = 0;
        $actYearUpSeconds = $actYearMaxSeconds = $actYearUpPercentage = 0;
        $logTimeUpSeconds = $logTimeMaxSeconds = $logTimeUpPercentage = 0;

        foreach($monUptimes as $monUptime){
            $everUpSeconds += $monUptime->getUpSeconds();
            $everMaxSeconds += $monUptime->getMaxSeconds(); 
            
            if($monUptime->getYear() == $actYear){
                $actYearUpSeconds += $monUptime->getUpSeconds();
                $actYearMaxSeconds += $monUptime->getMaxSeconds();
            }           
        }
        
       
        // add information from MonRemoteLogs
        $oldestMonLog = MonRemoteLogs::findFirst(
            [
                "mon_remote_jobs_id = :id:",
                "order" => "modified ASC",
                "bind" => [
                    "id" => $this->getId(),
                ],
            ]
        );
        $newestMonLog = MonRemoteLogs::findFirst(
            [
                "mon_remote_jobs_id = :id:",
                "order" => "modified DESC",
                "bind" => [
                    "id" => $this->getId(),
                ],
            ]
        );
        
        // add up downtime periods
        $downTimePeriods = $this->createDownTimePeriods();
        $logDownTimeInSeconds = 0;
        foreach($downTimePeriods as $downTimePeriod){
            $logDownTimeInSeconds += $downTimePeriod->getDurationInSeconds();         
        }
        
        if($newestMonLog != null && $oldestMonLog != null){
            $logTimeMaxSeconds = Helpers::createUnixTimestampFromDateTime($newestMonLog->getModified()) - Helpers::createUnixTimestampFromDateTime($oldestMonLog->getModified());    
        }
        $logTimeUpSeconds = $logTimeMaxSeconds - $logDownTimeInSeconds;        
        if($logTimeMaxSeconds > 0){
            $logTimeUpPercentage = $logTimeUpSeconds / $logTimeMaxSeconds;    
        }
        
        // add log times to ever and actYear
        $everMaxSeconds += $logTimeMaxSeconds;
        $everUpSeconds += $logTimeUpSeconds;
        $actYearMaxSeconds += $logTimeMaxSeconds;
        $actYearUpSeconds += $logTimeUpSeconds;
        
        if($everMaxSeconds > 0){
            $everUpPercentage = $everUpSeconds / $everMaxSeconds;    
        }
        if($actYearMaxSeconds > 0){
            $actYearUpPercentage = $actYearUpSeconds / $actYearMaxSeconds;    
        }
        
        $uptime = [
        'actperioduppercentage' => $logTimeUpPercentage,
        'actperiodmaxseconds' => $logTimeMaxSeconds,
        'actperiodupseconds' => $logTimeUpSeconds,
        'actyearuppercentage' => $actYearUpPercentage,
        'actyearmaxseconds' => $actYearMaxSeconds,
        'actyearupseconds' => $actYearUpSeconds,
        'everuppercentage' => $everUpPercentage,
        'evermaxseconds' => $everMaxSeconds,
        'everupseconds' => $everUpSeconds
        ];
        
        $this->setUptime(json_encode($uptime));
        
        $this->save();
    }
    
    /**
    * Creates an array of DownTimePeriods from MonRemoteLogs.
    * 
    * @return \EAT\monitoring\model\DownTimePeriod[]
    */
    private function createDownTimePeriods(){
        $downTimes = array();
        $monLogs = MonRemoteLogs::find(
            [
                "mon_remote_jobs_id = :id:",
                "order" => "modified ASC",
                "bind" => [
                    "id" => $this->getId(),
                ],
            ]
        );
        
        $lastState = $curState = -1;
        $start = 0;
        $end = 0;
        $curMonLog = null;
        
        foreach($monLogs as $monLog){
            $curState = $monLog->getValue();
            
            // if already down in first log
            if($lastState == -1 && $curState == 0){
                $start = Helpers::createUnixTimestampFromDateTime($monLog->getModified());
            }
            
            // negative statuschange       
            if($lastState == 1 && $curState == 0){
                $start = Helpers::createUnixTimestampFromDateTime($monLog->getModified());
            }
            
            // positive statuschange
            if($lastState == 0 && $curState == 1){
                $end = Helpers::createUnixTimestampFromDateTime($monLog->getModified());
                $downTimes[] = new DownTimePeriod($start,$end);    
            }
            
            $lastState = $curState;
            $curMonLog = $monLog;
        }
        
        // if downtime never ends in the logs, say end < start, the modified of the last MonRemoteLogs is taken
        if($end < $start){
            $lastMonLog = $curMonLog;
            $end = Helpers::createUnixTimestampFromDateTime($lastMonLog->getModified());
            $downTimes[] = new DownTimePeriod($start,$end);
        }
        
        return $downTimes; 
    }
    
    public function genMonUptimes(){
        MonUptimesGenerator::genMonUptime($this);
    }
    
    public function hadRecentHealJob(){
        return $this->recent_healjob_id > 0;
    }
    
    public function getLastDowntimePeriod(){
        $modelManager = $this->getDI()['modelsManager'];
        $endLog = $modelManager->executeQuery(
            "SELECT * FROM \\RNTForest\\ovz\\models\\MonRemoteLogs AS m1 ".
                " WHERE m1.value = 1 ".
                " AND m1.mon_remote_jobs_id = :monJobId: ".
                " AND m1.modified > (".
                "   SELECT MAX(m2.modified) FROM \\RNTForest\\ovz\\models\\MonRemoteLogs AS m2 ".
                "       WHERE m2.value = 0 ".
                "       AND m2.mon_remote_jobs_id = :monJobId:".
                " )".
                " ORDER BY m1.modified ASC LIMIT 1",
            ["monJobId" => $this->getId()]
        );
        
        $endModified = $endLog->getFirst()->getModified();
        
        $startLog = $modelManager->executeQuery(
            "SELECT * FROM \\RNTForest\\ovz\\models\\MonRemoteLogs AS m1 ".
                " WHERE m1.value = 0 ".
                " AND m1.mon_remote_jobs_id = :monJobId: ".
                " AND m1.modified > (".
                "   SELECT MAX(m2.modified) FROM \\RNTForest\\ovz\\models\\MonRemoteLogs AS m2 ".
                "       WHERE m2.value = 1 ".
                "       AND m2.mon_remote_jobs_id = :monJobId:".
                "       AND m2.modified < :endModified: ".
                " )".
                " ORDER BY m1.modified ASC LIMIT 1",
            ["monJobId" => $this->getId(),"endModified" => $endModified]
        );                                                             
        $startModified = $startLog->getFirst()->getModified();
        
        $start = Helpers::createUnixTimestampFromDateTime($startModified);
        $end = Helpers::createUnixTimestampFromDateTime($endModified);
        return new DowntimePeriod($start,$end);   
    }
    
    /**
    * @return \Phalcon\Logger\AdapterInterface
    */
    private function getLogger(){
        return $this->getDI()['logger'];
    }
}
