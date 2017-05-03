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
            $alarmMailaddresses = $monJob->getMonContactsAlarmMailaddresses();
            foreach($alarmMailaddresses as $mailaddress){
                $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
                $subject = 'Alarm: '.$behavior.' on '.$monJob->getServer()->getName();
                $this->sendMail($mailaddress,$subject,$this->genAlarmContentMonRemoteJobs($monJob));
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
        $result = $currentTimestamp > ($lastAlarmTimestamp + $alarmPeriodInSeconds);
        
        return $result;
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
        $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
        $content .= 'OVZ AlarmingSystem Alarm for '.$name.' ('.$mainIp.')'."<br />";
        $content .= '==>'.$behavior.'<=='." (MonJob ID: ".$monJob->getId().")<br />";
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
            $alarmMailaddresses = $monJob->getMonContactsAlarmMailaddresses();
            foreach($alarmMailaddresses as $mailaddress){
                $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
                $subject = 'Disalarm: '.$behavior.' on '.$monJob->getServer()->getName();
                $this->sendMail($mailaddress,$subject,$this->genAlarmContentMonRemoteJobs($monJob));
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
            $monServer = $monJob->getServerClass()::findFirst($monJob->getServerId());
            $name = $monServer->getName();
            $mainIp = $monJob->getMainIp();
            $status = $monJob->getStatus();
            $lastStatuschange = $monJob->getLastStatuschange();
            
            $healJob = \RNTForest\core\models\Jobs::findFirst($monJob->getRecentHealJobId());
                        
            $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
            $subject = "HealJob: ".$behavior." on ".$name;
            $content .= 'OVZ MonAlarm HealJob for '.$name.' ('.$mainIp.')'."<br />";
            $content .= 'Comment: todo'."<br />";
            $content .= '==>'.$behavior.'<=='."<br />";
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
            $monServer = $monJob->getServerClass()::findFirst($monJob->getServerId());
            $name = $monServer->getName();
            $mainIp = $monJob->getMainIp();
            $status = $monJob->getStatus();
            $lastStatuschange = $monJob->getLastStatuschange();
            $downTimePeriod = $monJob->getLastDowntimePeriod();
            
            $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
            $subject = "Short Downtime: ".$behavior." on ".$name;
            $content .= 'OVZ MonAlarm Short Downtime for '.$name.' ('.$mainIp.')'."<br />";
            $content .= 'Comment: todo'."<br />";
            $content .= '==>'.$behavior.'<=='."<br />";
            $content .= 'Status now: '.$status.' (since '.$lastStatuschange.')'."<br />";
            $content .= 'MonJob ID: '.$monJob->getId()."<br />";
            $content .= '-------------------------------'."<br />";
            $content .= 'Downtime of '.$downTimePeriod->getDurationString().' measured, from '.$downTimePeriod->getStartString().' to '.$downTimePeriod->getEndString()."<br />";
            $content .= 'No interaction was taken by MonSystem to bring the service up again.'."<br />";
            
            $this->inform($monJob,$subject,$content); 
        }
    }
    
    private function inform(MonRemoteJobs $monJob, $subject, $content){
        $messageMailaddresses = $monJob->getMonContactsMessageMailaddresses();
        foreach($messageMailaddresses as $mailaddress){
            $this->sendMail($mailaddress,$subject,$content);
        }       
    }
    
    public function notifyMonLocalJobs(MonLocalJobs $monJob){
        if($monJob->getAlarm() && $this->checkAlarmPeriod($monJob->getAlarmPeriod(), $monJob->getLastAlarm())){
            $mailaddresses = array();
            if($monJob->getStatus() == MonLocalJobs::$STATEMAXIMAL){
                $mailaddresses = $monJob->getMonContactsAlarmMailaddresses();
            }elseif($monJob->getStatus() == MonLocalJobs::$STATEWARNING){
                $mailaddresses = $monJob->getMonContactsMessageMailaddresses();
            }
            foreach($mailaddresses as $mailaddress){
                $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
                $subject = 'Notification: '.$behavior.' on '.$monJob->getServer()->getName();
                $this->sendMail($mailaddress,$subject,$this->genAlarmContentMonLocalJob($monJob));
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
        $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
        $content .= 'OVZ AlarmingSystem Alarm for '.$name."<br />";
        $content .= '==>'.$behavior.'<=='." (MonJob ID: ".$monJob->getId().")<br />";
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
        $monBehavior = new $behaviorclass();
        $content .= $monBehavior->genThresholdString($newestMonLog->getValue(),$monJob->getWarningValue(),$monJob->getMaximalValue(),$monJob->getMonBehaviorParams());
        return $content;
    }
    
    /**
    * Extracts the short name of a MonBehaviorClass with can have namespaces.
    * \RNTForest\ovz\utilities\monbehaviors\PingMonBehavior -> Ping
    * 
    * @param string $monBehaviorClass
    * @return string
    */
    private function extractNameFromMonBehaviorClass($monBehaviorClass){
        // to guarantee that something useful is available
        $extractedName = "sory".$monBehaviorClass; 
        $splits = explode('\\',$monBehaviorClass);
        if(is_array($splits) && !empty($splits)){
            $name = end($splits);
            if(preg_match('`^(.+)Mon.*Behavior$`',$name,$matches)){
                $extractedName = $matches[1];
            }
        }
        return $extractedName;
    }
    
    private function sendMail($recipient,$subject,$message){
        $header  = 'MIME-Version: 1.0' . "\r\n";
        $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $header .= 'From: OVZ MonAlarm <alarm@ovzalarm.tld>' . "\r\n";
        
        return mail($recipient,$subject,$message,$header);
    }
}
