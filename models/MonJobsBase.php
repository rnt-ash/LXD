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
use RNTForest\ovz\interfaces\MonLocalBehaviorInterface;
use RNTForest\ovz\models\MonLogs;
use RNTForest\ovz\models\MonUptimes;
use RNTForest\core\libraries\Helpers;
use RNTForest\ovz\datastructures\DowntimePeriod;
use RNTForest\ovz\utilities\MonUptimesGenerator;
use RNTForest\ovz\utilities\MonLocalDailyLogsGenerator;
use RNTForest\ovz\functions\Monitoring;

use Phalcon\Mvc\Model\Message as Message;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Validation\Validator\InclusionIn as InclusionInValidator;
use Phalcon\Validation\Validator\Date as DateValidator;
use Phalcon\Validation\Validator\Numericality as NumericalityValidator;
use Phalcon\Validation\Validator\Regex as RegexValidator;

class MonJobsBase extends \RNTForest\core\models\ModelBase
{
    public static $LOCAL_STATENORMAL = 'normal';
    public static $LOCAL_STATEMAXIMAL = 'maximal';
    public static $LOCAL_STATEWARNING = 'warning';

    public static $REMOTE_STATEUP = 'up';
    public static $REMOTE_STATEDOWN = 'down';
    public static $REMOTE_STATENOSTATE = 'nostate';

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
    protected $mon_type;

    /**
    * Used for remote monitoring to prevent getting main ip each time.
    * remote only
    * 
    * @var string
    */
    protected $main_ip;

    /**
    * Defines the behavior the job has when executing.
    * 
    * @var string
    */
    protected $mon_behavior_class;    

    /**
    * Defines params for the execute action of the behavior.          
    * local only
    * 
    * @var string
    */
    protected $mon_behavior_params;    

    /**
    * Minutes of how long the last run should be before monitoring again.
    * 
    * @var int
    */
    protected $period;

    /**
    * Status which the MonJob has.
    * local and remote have different ones. Those are defined in this 
    * class as classvariables.
    * 
    * @var string
    */
    protected $status;   

    /**
    * Timestamp of last status change (Y-m-d H:i:s) 
    * 
    * @var string
    */
    protected $last_status_change;

    /**
    * Defines threshold when a message should be sent.
    * local only
    * 
    * @var string
    */
    protected $warning_value;

    /**
    * Defines threshold when an alarm should be sent.
    * local only
    * 
    * @var string
    */
    protected $maximal_value;

    /**
    * Holds an json-object of uptimes of this job, so that those has not to be recomputed every time.
    * remote only.
    * 
    * @var string
    */
    protected $uptime;

    /**
    * Switch if active or not (1 or 0)
    * 
    * @var integer
    */
    protected $active;

    /**
    * Switch if healing enabled or not (1 or 0)
    * remote only
    * 
    * @var integer
    */
    protected $healing;

    /**
    * Switch if alarm enabled or not (1 or 0)
    * 
    * @var integer
    */
    protected $alarm;

    /**
    * Switch if already alarmed for current state or not (1 or 0)
    * 
    * @var integer
    */
    protected $alarmed;

    /**
    * Switch if muted or not (1 or 0)
    * muted means that e.g. for remote no alarm is sent AND no healing is executed
    * 
    * @var integer
    */
    protected $muted;

    /**
    * Timestamp of last alarm (Y-m-d H:i:s) 
    * 
    * @var string
    */
    protected $last_alarm;

    /**
    * Minutes of how long the last alarm should be before alarming again.
    * 
    * @var integer
    */
    protected $alarm_period;

    /**
    * Commaseparated lists of ids to logins which should be notified for messages.
    * 
    * @var string
    */
    protected $mon_contacts_message;

    /**
    * Commaseparated lists of ids to logins which should be notified for alarms.
    * 
    * @var string
    */
    protected $mon_contacts_alarm;

    /**
    * Timestamp of last run (Y-m-d H:i:s) 
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // methods for local
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
    * Set type (local or remote).
    * 
    * @param string $type
    */
    public function setMonType($type)
    {
        $this->mon_type = $type;
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // methods for local
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
    * returns monitoring type
    * 
    * @return string
    */
    public function getMonType()
    {
        return $this->mon_type;
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


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // methods for remote
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
    * Returns true if this MonJob is in error state.
    * 
    * remote only
    * 
    * @return boolean
    */
    public function isInErrorState(){
        if($this->mon_type != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        return $this->Status == 'down';
    }

    /**
    * Returns the downtimes periods of a MonJob
    * 
    * remote only
    * 
    * @return \EAT\monitoring\model\DownTimePeriod[] 
    */
    public function getDownTimeInformation(){
        $downTimePeriods = $this->createDownTimePeriods();
        return $downTimePeriods;
    }

    /**
    * Recomputes the uptime out of MonLogs and MonUptimes.
    * 
    * remote only
    * 
    */
    public function recomputeUptime(){
        if($this->mon_type != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));

        $monUptimes = MonUptimes::find(
            [
                "mon_jobs_id = :id:",
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


        // add information from MonLogs
        $oldestMonLog = MonLogs::findFirst(
            [
                "mon_jobs_id = :id:",
                "order" => "modified ASC",
                "bind" => [
                    "id" => $this->getId(),
                ],
            ]
        );
        $newestMonLog = MonLogs::findFirst(
            [
                "mon_jobs_id = :id:",
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

        if($this->save() !== true){
            $messages = $this->getMessages();
            $this->getLogger()->error('could not save job '.$this->id.' Messages: '.implode(' ',$messages));
        }
    }

    /**
    * Creates an array of DownTimePeriods from MonLogs.
    * 
    * remote only
    * 
    * @return \EAT\monitoring\model\DownTimePeriod[]
    */
    private function createDownTimePeriods(){
        if($this->mon_type != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        $downTimes = array();
        $modelManager = $this->getDI()['modelsManager'];
        
        $startPreSQL = microtime(true);
        $rows = $modelManager->executeQuery(
            "SELECT \\RNTForest\\ovz\\models\\MonLogs.modified, \\RNTForest\\ovz\\models\\MonLogs.value ".
            "FROM \\RNTForest\\ovz\\models\\MonLogs ".
            "WHERE \\RNTForest\\ovz\\models\\MonLogs.mon_jobs_id = :id: ".
            "ORDER BY \\RNTForest\\ovz\\models\\MonLogs.modified ASC",
            [
                "id" => $this->getId()
            ]
        );

        $timeElapsedSQLRead = microtime(true) - $startPreSQL;
        $this->getLogger()->debug('timeElapsedSQLRead: '.$timeElapsedSQLRead);


        $startPreComputation = microtime(true);

        $lastState = $curState = -1;
        $start = 0;
        $end = 0;
        $curRow = null;

        foreach($rows as $row){
            $curState = $row['value'];

            // if already down in first log
            if($lastState == -1 && $curState == 0){
                $start = Helpers::createUnixTimestampFromDateTime($row['modified']);
            }

            // negative statuschange       
            if($lastState == 1 && $curState == 0){
                $start = Helpers::createUnixTimestampFromDateTime($row['modified']);
            }

            // positive statuschange
            if($lastState == 0 && $curState == 1){
                $end = Helpers::createUnixTimestampFromDateTime($row['modified']);
                $downTimes[] = new DownTimePeriod($start,$end);    
            }

            $lastState = $curState;
            $curRow = $row;
        }

        // if downtime never ends in the logs, say end < start, the modified of the last MonLogs is taken
        if($end < $start){
            $lastRow = $curRow;
            $end = Helpers::createUnixTimestampFromDateTime($lastRow['modified']);
            $downTimes[] = new DownTimePeriod($start,$end);
        }

        $timeElapsedComputation = microtime(true) - $startPreComputation;
        $this->getLogger()->debug('timeElapsedComputation: '.$timeElapsedComputation);

        return $downTimes; 
    }

    public function getLastHealJobOfMonLogsBetween($start, $end){
        $monLogs = MonLogs::find(
            [
                "mon_jobs_id = :id: AND modified BETWEEN :start: AND :end:",
                "order" => "modified DESC",
                "bind" => [
                    "id" => $this->getId(),
                    "start" => $start,
                    "end" => $end,
                ],
            ]
        );

        foreach($monLogs as $monLog){
            if($healJobId = $monLog->getHealJob()){
                return \RNTForest\core\models\Jobs::findFirst(intval($healJobId));
            }
        }
        return null;
    }

    /**
    * Gens the MonUptimes out of old MonLogs.
    * 
    * remote only
    * 
    */
    public function genMonUptimes(){
        if($this->mon_type != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        MonUptimesGenerator::genMonUptime($this);
    }

    /**
    * Checks if there was a Healjob in the current period.
    * 
    * remote only
    * 
    */
    public function hadRecentHealJob(){
        if($this->mon_type != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        return $this->recent_healjob_id > 0;
    }

    /**
    * Gives the last DowntimePeriod.
    * 
    * remote only
    * 
    * @return DowntimePeriod
    */
    public function getLastDowntimePeriod(){
        if($this->mon_type != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        $modelManager = $this->getDI()['modelsManager'];
        $endLog = $modelManager->executeQuery(
            "SELECT * FROM \\RNTForest\\ovz\\models\\MonLogs AS m1 ".
            " WHERE m1.value = 1 ".
            " AND m1.mon_jobs_id = :monJobId: ".
            " AND m1.modified > (".
            "   SELECT MAX(m2.modified) FROM \\RNTForest\\ovz\\models\\MonLogs AS m2 ".
            "       WHERE m2.value = 0 ".
            "       AND m2.mon_jobs_id = :monJobId:".
            " )".
            " ORDER BY m1.modified ASC LIMIT 1",
            ["monJobId" => $this->getId()]
        );

        $endModified = $endLog->getFirst()->getModified();

        $startLog = $modelManager->executeQuery(
            "SELECT * FROM \\RNTForest\\ovz\\models\\MonLogs AS m1 ".
            " WHERE m1.value = 0 ".
            " AND m1.mon_jobs_id = :monJobId: ".
            " AND m1.modified > (".
            "   SELECT MAX(m2.modified) FROM \\RNTForest\\ovz\\models\\MonLogs AS m2 ".
            "       WHERE m2.value = 1 ".
            "       AND m2.mon_jobs_id = :monJobId:".
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
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // methods for local
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
    * Get
    * 
    * local only
    * 
    */
    public function genMonLocalDailyLogs(){
        if($this->mon_type != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
        MonLocalDailyLogsGenerator::genLocalDailyLogs($this);    
    }

    /**
    * Get the MonLocalLogs of this week in hour interval.
    * Convenience method which wraps getMonLogs.
    * 
    * local only
    * 
    * @return array 
    */
    public function getLocalMonLogsThisWeekHourly(){
        if($this->mon_type != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
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
    * local only
    * 
    * @return array 
    */
    public function getLocalMonLogsThisMonthHourly(){
        if($this->mon_type != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
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
    * local only
    * 
    * @return array 
    */
    public function getLocalMonLogsThisMonthDaily(){
        if($this->mon_type != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
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
    * local only
    * 
    * @return array 
    */
    public function getLocalMonLogsThisYearDaily(){
        if($this->mon_type != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
        $start = new \DateTime();
        $start->setTimestamp(strtotime("first day of this year"));
        $end = new \DateTime();
        $end->setTimestamp(strtotime("now"));
        return $this->getMonLogs($start,$end,"daily");
    }

    /**
    * Get MonLocalLogs between start and end datetime in a specific interval/unit.
    * Especially for unit all and hourly it is only possible to get Logs wich are in MonLocalLogs (not older than last month).
    * For unit daily and weekly it can be further gotten from the older MonLocalDailyLogs.
    * 
    * local only
    * 
    * @param \DateTime $start
    * @param \DateTime $end
    * @param mixed $unit all, hourly, daily, weekly
    * @return array
    */
    public function getLocalMonLogs(\DateTime $start, \DateTime $end, $unit){
        if($this->mon_type != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // other methods for both types
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

    /**
    * Helper method to return the short name of a behavior
    * 
    * @param mixed $serverType 'virtual' or 'physical'
    * @return string
    */
    public function getShortName($serverType){
        $behaviors = Monitoring::getAllBehaviors($serverType);
        // special case on diskspace behavior
        if($serverType == 'physical' && $this->mon_behavior_class == '\RNTForest\ovz\utilities\monbehaviors\DiskspacefreeMonLocalBehavior'){
            // check if the MonJob is about vz or root diskspace
            if(in_array('/vz',json_decode($this->mon_behavior_params,true))){
                return $behaviors[$this->mon_behavior_class."_vz"]['shortname'];
            }elseif(in_array('/',json_decode($this->mon_behavior_params,true))){
                return $behaviors[$this->mon_behavior_class."_root"]['shortname'];
            }
        }else{
            return $behaviors[$this->mon_behavior_class]['shortname'];
        }
    }

    public function execute(){
        $statusBefore = $this->getStatus();
        $executed = false;
        $type = '';

        $behavior = new $this->mon_behavior_class();
        if(!($behavior instanceof MonBehaviorInterface || $behavior instanceof MonLocalBehaviorInterface)){
            throw new \Exception($this->translate("monitoring_mon_behavior_not_implements_interface"));    
        }

        // update MainIp from Server Object in case it has changed since last execute
        $server = $this->getServer();

        if(!($server instanceof \RNTForest\ovz\interfaces\MonServerInterface)){
            throw new \Exception($this->translate("monitoring_mon_server_not_implements_interface"));        
        } 

        if($this->mon_type == 'remote'){
            $ipaddress = $server->getMainIp();

            if($ipaddress === false){
                $this->getLogger()->notice($this->translate("monitoring_monremotejobs_no_ip")." MonRemoteJobId: ".$this->getId());
            }else{
                $this->setMainIp($ipaddress->toString());

                $statusAfter = $behavior->execute($this->getMainIp());
                $monLog = new MonLogs();
                $monLog->create(["mon_jobs_id" => $this->id, "value" => $statusAfter, "modified" => date('Y-m-d H:i:s')]);
                $monLog->save();

                if($statusAfter == "1"){
                    $this->status = "up";
                }else{
                    $this->status = "down";
                }
                $executed = true;
            }
        }elseif($this->mon_type == 'local'){
            $ovzStatistics = $server->getOvzStatistics();

            $decodedOvzStatistics = json_decode($ovzStatistics,true);
            $timestamp = '';
            if(is_array($decodedOvzStatistics) && key_exists('Timestamp',$decodedOvzStatistics)){
                $timestamp = $decodedOvzStatistics['Timestamp'];    
            }
            // if model is older than 1 minute write this to log, should not be
            if(empty($timestamp) || Helpers::createUnixTimestampFromDateTime($timestamp) < (time()-600)){
                $this->getLogger()->notice($this->translate("monitoring_monlocaljobs_statistics_timestamp_to_old").'(MonLocalJob '.$this->getId().', Timestamp: '.$timestamp.')');
            }else{
                $value = '';        

                $valuestatus = $behavior->execute($ovzStatistics,$this->mon_behavior_params,$this->warning_value,$this->maximal_value);
                if($valuestatus == null){
                    throw new \Exception($this->translate("monitoring_mon_behavior_could_not_instantiate_valuestatus"));
                }
                $monLog = new MonLogs();
                $monLog->create(["mon_jobs_id" => $this->id, "value" => $valuestatus->getValue(), "modified" => date('Y-m-d H:i:s')]);
                $monLog->save();

                $this->status = $statusAfter = $valuestatus->getStatus();

                $executed = true;
            }
        }

        if($executed){
            if($statusBefore != $this->status){
                $this->setLastStatusChange(date('Y-m-d H:i:s'));    
            }
            $this->setLastRun(date('Y-m-d H:i:s'));
            $this->save();
        }
    }

    /**
    * @return \Phalcon\Logger\AdapterInterface
    */
    private function getLogger(){
        return $this->getDI()['logger'];
    }

    /**
    * Initialize method for model.
    */
    public function initialize()
    {
        $this->setup(array('notNullValidations'=>false));
        $this->setup(array('virtualForeignKeys'=>false));

        // Timestampable behavior
        $this->addBehavior(new Timestampable(array(
            'beforeUpdate' => array(
                'field' => 'modified',
                'format' => 'Y-m-d H:i:s'
            )
        )));
    }

    public function onConstruct()
    {
        // Set Defaults
        $this->period = 5;
        $this->alarm_period = 15;
        $this->status = 'nostate';
        $this->last_status_change = '0001-01-01 01:01:01';
        $this->active = 1;
        $this->alarm = 1;
        $this->alarmed = 0;
        $this->muted = 0;
        $this->healing = 0;
        $this->last_alarm = '0001-01-01 01:01:01';
        $this->last_run = '0001-01-01 01:01:01';
        $this->modified = date('Y-m-d H:i:s');
    }

    /**
    * Validations and business logic
    *
    * @return boolean
    */
    public function validation()
    {
        // business logic
        $config = $this->getDI()->get('config');

        // check if default contacts are set in config and no other contacts are already selected
        if(key_exists('contacts',$config->monitoring) && empty($this->mon_contacts_message) && empty($this->mon_contacts_alarm)){
            $contacts = json_decode($config->monitoring['contacts'],true);
            if(key_exists('default',$contacts)){
                // set contacts
                $this->mon_contacts_alarm = $contacts['default']['alarm'];
                $this->mon_contacts_message = $contacts['default']['message'];
            }
        }

        // if the contacts are in array format, convert to comma seperated string
        if(is_array($this->mon_contacts_alarm)){
            $this->mon_contacts_alarm = implode(',',$this->mon_contacts_alarm);
        }
        if(is_array($this->mon_contacts_message)){
            $this->mon_contacts_message = implode(',',$this->mon_contacts_message);
        }

        // local or remote monJob
        if(strpos($this->mon_behavior_class,'MonLocalBehavior')){
            $this->mon_type = 'local';
        }else{
            $this->mon_type = 'remote';
        }

        // Validator
        $validator = $this->generateValidator($this->mon_type);
        if(!$this->validate($validator)) return false;

        return true;
    }

    /**
    * generates validator for MonJobs model
    * 
    * return \Phalcon\Validation $validator
    * 
    */
    public static function generateValidator($monType){
        // validator
        $validator = new Validation();

        // server_id
        $validator->add('server_id', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_server_id_required"),
        ]));
        $validator->add('server_id', new NumericalityValidator([
            'message' => self::translate("monitoring_monjobs_server_id_numeric"), 
        ]));

        // server_class
        $validator->add('server_class', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_server_class_required"),
        ]));

        // mon_type
        if($monType == 'local'){
            $validator->add('mon_type', new InclusionInValidator([
                'domain' => ["local"],
                'message' => self::translate("monitoring_monjobs_mon_type_local_valid"),
            ]));
        }elseif($monType == 'remote'){
            $validator->add('mon_type', new InclusionInValidator([
                'domain' => ["remote"],
                'message' => self::translate("monitoring_monjobs_mon_type_remote_valid"),
            ]));
        }

        // main_ip
        if($monType == 'remote'){
            // Regex from IpObjects for value1; allows numbers and characters for IPv6 and IPv4
            $validator->add('main_ip', new RegexValidator([
                'pattern' => '/^[0-9a-f:.]*$/',
                'message' => self::translate("monitoring_monjobs_main_ip_valid"),
            ]));
        }

        // mon_behavior_class
        $validator->add('mon_behavior_class', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_mon_behavior_class_required"),
        ]));

        // mon_behavior_params
        if($monType == 'local'){
            $validator->add('mon_behavior_params', new PresenceOfValidator([
                'message' => self::translate("monitoring_monjobs_mon_behavior_params_required"),
            ]));
        }

        // period
        $validator->add('period', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_period_required"),
        ]));
        $validator->add('period', new NumericalityValidator([
            'message' => self::translate("monitoring_monjobs_period_numeric"), 
        ]));

        // status
        if($monType == 'local'){
            $validator->add('status', new InclusionInValidator([
                'domain' => ["normal", "warning", "maximal", "nostate"],
                'message' => self::translate("monitoring_monjobs_status_local_valid"),
            ]));
        }elseif($monType == 'remote'){
            $validator->add('status', new InclusionInValidator([
                'domain' => ["up", "down", "nostate"],
                'message' => self::translate("monitoring_monjobs_status_remote_valid"),
            ]));
        }

        // last_status_change
        // Checking if date is set in format Year-month-day hours:minutes:seconds
        $validator->add('last_status_change', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_last_status_change_required"),
        ]));        
        $validator->add('last_status_change', new DateValidator([
            'format' => "Y-m-d H:i:s",
            'message' => self::translate("monitoring_monjobs_last_status_change_format"),
        ]));

        // uptime
        // nothing to check

        if($monType == 'local'){
            // warning_value
            $validator->add('warning_value', new PresenceOfValidator([
                'message' => self::translate("monitoring_monjobs_warning_value_required"),
            ]));
            $validator->add('warning_value', new NumericalityValidator([
                'message' => self::translate("monitoring_monjobs_warning_value_numeric"), 
            ]));

            // maximal_value
            $validator->add('maximal_value', new PresenceOfValidator([
                'message' => self::translate("monitoring_monjobs_maximal_value_required"),
            ]));
            $validator->add('maximal_value', new NumericalityValidator([
                'message' => self::translate("monitoring_monjobs_maximal_value_numeric"), 
            ]));
        }

        // active 
        $validator->add('active', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_active_required"),
        ]));
        $validator->add('active', new RegexValidator([
            'pattern' => '/^[1 or 0]/',
            'message' => self::translate("monitoring_monjobs_active_valid"),
        ]));

        // healing
        $validator->add('healing', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_healing_required"),
        ]));
        if($monType == 'local'){
            $validator->add('healing', new RegexValidator([
                'pattern' => '/^[0]/',
                'message' => self::translate("monitoring_monjobs_healing_local_valid"),
            ]));
        }elseif($monType == 'remote'){
            $validator->add('healing', new RegexValidator([
                'pattern' => '/^[1 or 0]/',
                'message' => self::translate("monitoring_monjobs_healing_remote_valid"),
            ]));
        }

        // alarm
        $validator->add('alarm', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_alarm_required"),
        ]));
        $validator->add('alarm', new RegexValidator([
            'pattern' => '/^[1 or 0]/',
            'message' => self::translate("monitoring_monjobs_alarm_valid"),
        ]));

        // alarmed
        $validator->add('alarmed', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_alarmed_required"),
        ]));
        $validator->add('alarmed', new RegexValidator([
            'pattern' => '/^[1 or 0]/',
            'message' => self::translate("monitoring_monjobs_alarmed_valid"),
        ]));

        // muted
        $validator->add('muted', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_muted_required"),
        ]));
        $validator->add('muted', new RegexValidator([
            'pattern' => '/^[1 or 0]/',
            'message' => self::translate("monitoring_monjobs_muted_valid"),
        ]));

        // last_alarm
        // Checking if date is set in format Year-month-day hours:minutes:seconds
        $validator->add('last_alarm', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_last_alarm_required"),
        ]));        
        $validator->add('last_alarm', new DateValidator([
            'format' => "Y-m-d H:i:s",
            'message' => self::translate("monitoring_monjobs_last_alarm_format"),
        ]));

        // alarm_period
        $validator->add('alarm_period', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_alarm_period_required"),
        ]));
        $validator->add('alarm_period', new NumericalityValidator([
            'message' => self::translate("monitoring_monjobs_alarm_period_numeric"), 
        ]));

        // mon_contacts_message
        $validator->add('mon_contacts_message', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_mon_contacts_message_required"),
        ]));
        $validator->add('mon_contacts_message', new RegexValidator([
            // only numbers and commas allowed
            'pattern' => '/^[0-9\,]*/',
            'message' => self::translate("monitoring_monjobs_mon_contacts_message_valid"),
        ]));

        // mon_contacts_alarm
        $validator->add('mon_contacts_alarm', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_mon_contacts_alarm_required"),
        ]));
        $validator->add('mon_contacts_alarm', new RegexValidator([
            // only numbers and commas allowed
            'pattern' => '/^[0-9\,]*/',
            'message' => self::translate("monitoring_monjobs_mon_contacts_alarm_valid"),
        ]));

        // last_run
        // Checking if date is set in format Year-month-day hours:minutes:seconds
        $validator->add('last_run', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_last_run_required"),
        ]));        
        $validator->add('last_run', new DateValidator([
            'format' => "Y-m-d H:i:s",
            'message' => self::translate("monitoring_monjobs_last_run_format"),
        ]));

        // modified
        // Checking if date is set in format Year-month-day hours:minutes:seconds
        $validator->add('modified', new PresenceOfValidator([
            'message' => self::translate("monitoring_monjobs_modified_required"),
        ]));        
        $validator->add('modified', new DateValidator([
            'format' => "Y-m-d H:i:s",
            'message' => self::translate("monitoring_monjobs_modified_format"),
        ]));

        return $validator;
    }
}
