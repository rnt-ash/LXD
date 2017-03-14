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
    public function alarmRemoteMonJob(MonRemoteJobs $monJob){
        if($monJob->getAlarm() && !$monJob->getMuted() && $this->checkAlarmPeriod($monJob->getAlarmPeriod(), $monJob->getLastAlarm())){
            $alarmMonContacts = $monJob->getMonContactsAlarmInstances();
            foreach($alarmMonContacts as $contact){
                $subject = 'Alarm: '.$monJob->getMonBehaviorClass().' on '.$monJob->getServer()->getName();
                $contact->notify($subject,$this->genAlarmContentRemoteMonJob($monJob));
            }
            $monJob->setLastAlarm((new \DateTime())->format('Y-m-d H:i:s'));
            $monJob->setAlarmed(True);
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
    
    private function genAlarmContentRemoteMonJob(MonRemoteJobs $monJob){
        $content = '';
        $content .= $this->genContentRemoteMonJobsGeneralSection($monJob);
        $content .= $this->genContentUptimeSection($monJob);
        return $content;    
    }
    
    private function genContentRemoteMonJobsGeneralSection(MonRemoteJobs $monJob){
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
    public function disalarmRemoteMonJob(MonRemoteJobs $monJob){
        if($monJob->getAlarm() && !$monJob->getMuted()){
            $alarmMonContactsString = $monJob->getAlarmMonContacts();
            $alarmMonContactIds = explode(',',$alarmMonContactsString);
            foreach($alarmMonContactIds as $contactId){
                $contact = \RNTForest\ovz\models\MonContacts::findFirst($contactId);
                $subject = 'Disalarm: '.$monJob->getServiceCase().' on '.$monJob->getDco()->getName();
                $contact->notify($subject,$this->genAlarmContentRemoteMonJob($monJob));
            
            }
            $monJob->setAlarmed(False);
            $this->Logger->notice("Für RemoteMonJob (ID: ".intval($monJob->getId()).", MonService: ".$monJob->getServiceCase().") wurde entwarnt.",
                ['module'=>'monsystem','type'=>'disalarm','eventid'=>1475580561]
            );
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
            $content .= 'EAT AlarmingSystem HealJob for '.$name.' ('.$mainIp.')'."<br />";
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
            // todo $downTimePeriod = $monJob->getLastDowntimePeriod();
            
            $subject = "Short Downtime: ".$monBehavior." on ".$name;
            $content .= 'EAT AlarmingSystem Short Downtime for '.$name.' ('.$mainIp.')'."<br />";
            $content .= 'Comment: todo'."<br />";
            $content .= '==>'.$monBehavior.'<=='."<br />";
            $content .= 'Status now: '.$status.' (since '.$lastStatuschange.')'."<br />";
            $content .= 'MonJob ID: '.$monJob->getId()."<br />";
            $content .= '-------------------------------'."<br />";
            //$content .= 'Downtime of '.$downTimePeriod->getDurationString().' measured, from '.$downTimePeriod->getStartString().' to '.$downTimePeriod->getEndString()."<br />";
            $content .= 'No interaction was taken by MonSystem to bring the service up again.'."<br />";
            
            $this->inform($monJob,$subject,$content); 
        }
    }
    
    private function inform(MonRemoteJobs $monJob, $subject, $content){
        $messageMonContactsString = $monJob->getMonContactsMessage();
        $messageMonContactIds = explode(',',$messageMonContactsString);
        foreach($messageMonContactIds as $contactId){
            $contact = \RNTForest\ovz\models\MonContacts::findFirst($contactId);
            $contact->notify($subject,$content);
        }       
    }
}
