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
  
namespace RNTForest\lxd\services;

use RNTForest\lxd\models\MonJobs;
use RNTForest\lxd\models\MonLogs;
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
    * Alarms to all MonContactsAlarm of the given remote MonJobs.
    * Checks AlarmPeriod and sends only if it's already time again.
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    * @throws \Exception
    */
    public function alarmMonRemoteJobs(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        if($monJob->getAlarm() && !$monJob->getMuted() && $this->checkAlarmPeriod($monJob->getAlarmPeriod(), $monJob->getLastAlarm())){
            $alarmMailaddresses = $monJob->getMonContactsAlarmMailaddresses();
            foreach($alarmMailaddresses as $mailaddress){
                $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
                $subject = 'Alarm: '.$behavior.' on '.$monJob->getServer()->getName();
                $this->sendMail($mailaddress,$subject,$this->genAlarmContentMonRemoteJobs($monJob));
            }
            $monJob->setLastAlarm(date('Y-m-d H:i:s'));
            $monJob->setAlarmed(1);
            $monJob->save();
            $this->logger->notice("Remote MonJobs (ID: ".intval($monJob->getId()).", MonBehavior: ".$monJob->getMonBehaviorClass().") alarmed.");
        }
    }
    
    /**
    * Checks if it is already time to send again.
    * 
    * @param integer $alarmPeriodInMinutes
    * @param integer $lastAlarm
    */
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
    
    /**
    * Gens alarm content for remote MonJobs.
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    */
    private function genAlarmContentMonRemoteJobs(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        $content = '';
        $content .= $this->genContentMonRemoteJobsGeneralSection($monJob);
        $content .= $this->genContentUptimeSection($monJob);
        return $content;    
    }
    
    /**
    * Gens the general section content for a remote MonJobs.
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    */
    private function genContentMonRemoteJobsGeneralSection(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
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
    
    /**
    * Gens the general section content for a remote MonJobs.
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    */
    private function genContentUptimeSection(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
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
    * Disalarms fo the given remote MonJobs object.
    *
    * remote only
    *  
    * @param MonJobs $monJob
    * @throws \Exception
    */
    public function disalarmMonRemoteJobs(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        if($monJob->getAlarm() == '1' && $monJob->getMuted() == '0'){
            $alarmMailaddresses = $monJob->getMonContactsAlarmMailaddresses();
            foreach($alarmMailaddresses as $mailaddress){
                $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
                $subject = 'Disalarm: '.$behavior.' on '.$monJob->getServer()->getName();
                $this->sendMail($mailaddress,$subject,$this->genAlarmContentMonRemoteJobs($monJob));
            }
            $monJob->setAlarmed(0);
            $monJob->save();
        }
    }
    
    /**
    * Informs about the current healjob of the given remote MonJobs object.
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    * @throws \Exception
    */
    public function informAboutHealJob(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
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
    * remote only
    * 
    * @param MonJobs $monJob
    * @throws \Exception
    */
    public function informAboutShortDowntime(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
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
    
    private function inform(MonJobs $monJob, $subject, $content){
        $messageMailaddresses = $monJob->getMonContactsMessageMailaddresses();
        foreach($messageMailaddresses as $mailaddress){
            $this->sendMail($mailaddress,$subject,$content);
        }       
    }
    
    /**
    * Notifies for local MonJobs.
    * 
    * local only
    * 
    * @param MonJobs $monJob
    */
    public function notifyMonLocalJobs(MonJobs $monJob){
        if($monJob->getMonType() != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
        if($monJob->getAlarm() && !$monJob->getMuted() && $this->checkAlarmPeriod($monJob->getAlarmPeriod(), $monJob->getLastAlarm())){
            $mailaddresses = array();
            if($monJob->getStatus() == MonJobs::$LOCAL_STATEMAXIMAL){
                $mailaddresses = $monJob->getMonContactsAlarmMailaddresses();
            }elseif($monJob->getStatus() == MonJobs::$LOCAL_STATEWARNING){
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
    
    /**
    * Gens the content for a local MonJob
    * 
    * local only
    * 
    * @param MonLocalJobs $monJob
    */
    private function genAlarmContentMonLocalJob(MonJobs $monJob){
        if($monJob->getMonType() != 'local') throw new \Exception($this->translate('monitoring_monjobs_montype_local_expected'));
        $content = '';
        $monServer = $monJob->getServer();
        $name = $monServer->getName();  
        $status = $monJob->getStatus();
        $lastStatuschange = $monJob->getLastStatuschange();
        $behavior = $this->extractNameFromMonBehaviorClass($monJob->getMonBehaviorClass());
        $content .= 'OVZ AlarmingSystem Alarm for '.$name."<br />";
        $content .= '==>'.$behavior.'<=='." (MonJob ID: ".$monJob->getId().")<br />";
        $content .= 'Status now: '.$status.' (since '.$lastStatuschange.')'."<br />";
        $newestMonLog = MonLogs::findFirst(
            [
                "mon_jobs_id = :id:",
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
    * \RNTForest\lxd\utilities\monbehaviors\PingMonBehavior -> Ping
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
