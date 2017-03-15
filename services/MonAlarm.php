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
  
namespace RNTForest\ovz\services;

use RNTForest\ovz\models\MonRemoteJobs;
use RNTForest\ovz\models\MonLocalJobs;
use RNTForest\ovz\models\MonLocalLogs;
use RNTForest\core\libraries\Helpers;

class MonAlarm extends \Phalcon\DI\Injectable
{
    /**
    * 
    * @var \Phalcon\Logger\AdapterInterface
    */
    private $logger;
 
    public function __construct(){
        $this->logger = $this->getDI()['logger'];
    }
    
    public function translate($token,$params=array()){
        return $this->getDI()->getShared('translate')->_($token,$params);
    }
    
    /**
    * Alarms to all MonContactsAlarm of the given RemoteMonJobs.
    * Checks AlarmPeriod and sends only if it's already time again.
    * 
    * @param RemoteMonJobs $monJob
    */
    public function alarmMonRemoteJobs(MonRemoteJobs $monJob){
        if($monJob->getAlarm() && !$monJob->getMuted() && $this->checkAlarmPeriod($monJob->getAlarmPeriod(), $monJob->getLastAlarm())){
            $alarmMonContacts = $monJob->getMonContactsAlarmInstances();
            foreach($alarmMonContacts as $contact){
                $subject = 'Alarm: '.$monJob->getMonBehaviorClass().' on '.$monJob->getServer()->getName();
                $contact->notify($subject,$this->genAlarmContentMonRemoteJobs($monJob));
            }
            $monJob->setLastAlarm((new \DateTime())->format('Y-m-d H:i:s'));
            $monJob->setAlarmed(1)->save();
            $this->logger->notice("RemoteMonJobs (ID: ".intval($monJob->getId()).", MonService: ".$monJob->getMonBehaviorClass().") alarmed.");
        }
    }
    
    private function checkAlarmPeriod($alarmPeriodInMinutes, $lastAlarm){
        $alarmPeriodInSeconds = 60 * $alarmPeriodInMinutes;
        // if period is 0 return true direct without further checking because it would be unnecessary
        if($alarmPeriodInSeconds == 0){
            return True;
        }
        $lastAlarmTimestamp = Helpers::createUnixTimestampFromDateTime($lastAlarm);
        $currentTimestamp = time();
        
        return $currentTimestamp > ($lastAlarmTimestamp + $alarmPeriodInSeconds);
    }
    
    private function genAlarmContentMonRemoteJobs(MonRemoteJobs $monJob){
        $content = '';
        $content .= $this->genContentMonRemoteJobsGeneralSection($monJob);
        $content .= $this->genContentUptimeSection($monJob);
        return $content;    
    }
    
    private function genContentMonRemoteJobsGeneralSection(MonRemoteJobs $monJob){
        $content = '';
        $monServer = $monJob->getServer();
        $name = $monServer->getName();  
        $ip = $monJob->getMainIp();
        if(is_null($ip)){
            $mainIp = "nohost";      
        }else{
            $mainIp = $ip;
        }
        $status = $monJob->getStatus();
        $lastStatuschange = $monJob->getLastStatuschange();
        $monService = $monJob->getMonBehaviorClass();
        $content .= 'OVZ AlarmingSystem Alarm for '.$name.' ('.$mainIp.')'."<br />";
        $content .= '==>'.$monService.'<=='." (MonJob ID: ".$monJob->getId().")<br />";
        $content .= 'Status now: '.$status.' (since '.$lastStatuschange.')'."<br />";
        return $content;
    }
    
    private function genContentUptimeSection(MonRemoteJobs $monJob){
        $content = '';
        $uptime = json_decode($monJob->getUptime(),true);
        if(is_array($uptime)){
            if(key_exists('actperioduppercentage',$uptime)){
                $content .= 'Service Uptime current Period (this and last month): '.substr(($uptime['actperioduppercentage']*100),0,6).'%'."<br />";
            }
            if(key_exists('actyearuppercentage',$uptime)){
                $content .= 'Service Uptime current Year: '.substr(($uptime['actyearuppercentage']*100),0,6).'%'."<br />";
            }
            if(key_exists('everuppercentage',$uptime)){
                $content .= 'Service Uptime forever: '.substr(($uptime['everuppercentage']*100),0,6).'%'."<br />";
            }
        }
        return $content;  
    }
    
    /**
    * Disalarms fo the given MonRemoteJobs object.
    * 
    * @param MonRemoteJobs $monJob
    * @throws \Exception
    */
    public function disalarmMonRemoteJobs(MonRemoteJobs $monJob){
        if($monJob->getAlarm() == '1' && $monJob->getMuted() == '0'){
            $alarmMonContacts = $monJob->getMonContactsAlarmInstances();
            foreach($alarmMonContacts as $contact){
                $subject = 'Disalarm: '.$monJob->getMonBehaviorClass().' on '.$monJob->getServer()->getName();
                $contact->notify($subject,$this->genAlarmContentMonRemoteJobs($monJob));
            }
            $monJob->setAlarmed(0)->save();
        }
    }
    
    /**
    * Informs about the current healjob of the given MonRemoteJobs.
    * 
    * @param MonRemoteJobs $monJob
    * @throws \Exception
    */
    public function informAboutHealJob(MonRemoteJobs $monJob){
        if($monJob->getAlarm() && !$monJob->getMuted()){
            $content = '';
            $monServer = $monJob->getServersClass()::findFirst($monJob->getServersId());
            $name = $monServer->getName();
            $mainIp = $monJob->getMainIp();
            $status = $monJob->getStatus();
            $lastStatuschange = $monJob->getLastStatuschange();
            $monBehavior = $monJob->getMonBehaviorClass();
            
            $healJob = \RNTForest\core\models\Jobs::findFirst($monJob->getRecentHealJobId());
                        
            $subject = "HealJob: ".$monBehavior." on ".$name;
            $content .= 'OVZ MonAlarm HealJob for '.$name.' ('.$mainIp.')'."<br />";
            $content .= 'Comment: todo'."<br />";
            $content .= '==>'.$monBehavior.'<=='."<br />";
            $content .= 'Status now (after HealJob): '.$status.' (since '.$lastStatuschange.')'."<br />";
            $content .= 'MonJob ID: '.$monJob->getId()."<br />";
            $content .= '-------------------------------'."<br />";
            $content .= 'A HealJob of Type '.$healJob->getType().' was used to heal the system.'."<br />";
            $content .= 'HealJob ID: '.$healJob->getId()."<br />";
            $content .= 'HealJob Done: '.$healJob->getDone()."<br />";
            if($healJob->getDone() != 1){
                $content .= 'HealJob Error: '.$healJob->getError()."<br />";
            }
            $content .= 'HealJob Params: '.$healJob->getParams()."<br />";
            $content .= 'HealJob Retval: '.$healJob->getRetval()."<br />";
            $content .= 'HealJob Created: '.$healJob->getCreated()."<br />";
            $content .= 'HealJob Sent: '.$healJob->getSent()."<br />";
            $content .= 'HealJob Executed: '.$healJob->getExecuted()."<br />";
              
            $this->inform($monJob,$subject,$content);
        }
    }
    
    /**
    * Informs about a short downtime.
    * 
    * @param MonRemoteJobs $monJob
    * @throws \Exception
    */
    public function informAboutShortDowntime(MonRemoteJobs $monJob){
        if($monJob->getAlarm() && !$monJob->getMuted()){
            $content = '';
            $monServer = $monJob->getServersClass()::findFirst($monJob->getServersId());
            $name = $monServer->getName();
            $mainIp = $monJob->getMainIp();
            $status = $monJob->getStatus();
            $lastStatuschange = $monJob->getLastStatuschange();
            $monBehavior = $monJob->getMonBehaviorClass();
            $downTimePeriod = $monJob->getLastDowntimePeriod();
            
            $subject = "Short Downtime: ".$monBehavior." on ".$name;
            $content .= 'OVZ MonAlarm Short Downtime for '.$name.' ('.$mainIp.')'."<br />";
            $content .= 'Comment: todo'."<br />";
            $content .= '==>'.$monBehavior.'<=='."<br />";
            $content .= 'Status now: '.$status.' (since '.$lastStatuschange.')'."<br />";
            $content .= 'MonJob ID: '.$monJob->getId()."<br />";
            $content .= '-------------------------------'."<br />";
            $content .= 'Downtime of '.$downTimePeriod->getDurationString().' measured, from '.$downTimePeriod->getStartString().' to '.$downTimePeriod->getEndString()."<br />";
            $content .= 'No interaction was taken by MonSystem to bring the service up again.'."<br />";
            
            $this->inform($monJob,$subject,$content); 
        }
    }
    
    private function inform(MonRemoteJobs $monJob, $subject, $content){
        $messageMonContacts = $monJob->getMonContactsMessageInstances();
        foreach($messageMonContacts as $contact){
            $contact->notify($subject,$content);
        }       
    }
    
    public function notifyMonLocalJobs(MonLocalJobs $monJob){
        if($monJob->getAlarm() && $this->checkAlarmPeriod($monJob->getAlarmPeriod(), $monJob->getLastAlarm())){
            $monContacts = array();
            if($monJob->getStatus() == MonLocalJobs::$STATEMAXIMAL){
                $monContacts = $monJob->getMonContactsAlarmInstances();
            }elseif($monJob->getStatus() == MonLocalJobs::$STATEWARNING){
                $monContacts = $monJob->getMonContactsMessageInstances();
            }
            foreach($monContacts as $contact){
                $subject = 'Notification: '.$monJob->getMonBehaviorClass().' on '.$monJob->getServer()->getName();
                $contact->notify($subject,$this->genAlarmContentMonLocalJob($monJob));
            }
            $monJob->setLastAlarm((new \DateTime())->format('Y-m-d H:i:s'));
            $monJob->save();
        }
    }
    
    private function genAlarmContentMonLocalJob(MonLocalJobs $monJob){
        $content = '';
        $monServer = $monJob->getServer();
        $name = $monServer->getName();  
        $status = $monJob->getStatus();
        $lastStatuschange = $monJob->getLastStatuschange();
        $monService = $monJob->getMonBehaviorClass();
        $content .= 'OVZ AlarmingSystem Alarm for '.$name."<br />";
        $content .= '==>'.$monService.'<=='." (MonJob ID: ".$monJob->getId().")<br />";
        $content .= 'Status now: '.$status.' (since '.$lastStatuschange.')'."<br />";
        $newestMonLog = MonLocalLogs::findFirst(
            [
                "mon_local_jobs_id = :id:",
                "order" => "id DESC",
                "bind" => [
                    "id" => $monJob->getId(),
                ],
            ]
        );
        $behaviorclass = $monJob->getMonBehaviorClass();
        $behavior = new $behaviorclass();
        $content .= $behavior->genThresholdString($newestMonLog->getValue(),$monJob->getWarningValue(),$monJob->getMaximalValue());
        return $content;
    }
}
