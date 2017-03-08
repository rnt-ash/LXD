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

use RNTForest\ovz\models\RemoteMonJobs;
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
    /**
    * Alarms to all MonContactsAlarm of the given RemoteMonJobs.
    * Checks AlarmPeriod and sends only if it's already time again.
    * 
    * @param RemoteMonJobs $monJob
    */
    public function alarmRemoteMonJob(RemoteMonJobs $monJob){
        if($monJob->getAlarm() && !$monJob->getMuted() && $this->checkAlarmPeriod($monJob->getAlarmPeriod(), $monJob->getLastAlarm())){
            $alarmMonContacts = $monJob->getMonContactsAlarmInstances();
            foreach($alarmMonContacts as $contact){
                $subject = 'Alarm: '.$monJob->getMonServicesCase().' on '.$monJob->getServer()->getName();
                $contact->notify($subject,$this->genAlarmContentRemoteMonJob($monJob));
            }
            $monJob->setLastAlarm((new \DateTime())->format('Y-m-d H:i:s'));
            $monJob->setAlarmed(True);
            $this->logger->notice("RemoteMonJobs (ID: ".intval($monJob->getId()).", MonService: ".$monJob->getMonServicesCase().") alarmed.");
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
    
    private function genAlarmContentRemoteMonJob(RemoteMonJobs $monJob){
        $content = '';
        $content .= $this->genContentRemoteMonJobsGeneralSection($monJob);
        $content .= $this->genContentUptimeSection($monJob);
        return $content;    
    }
    
    private function genContentRemoteMonJobsGeneralSection(RemoteMonJobs $monJob){
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
        $monService = $monJob->getMonServicesCase();
        $content .= 'OVZ AlarmingSystem Alarm for '.$name.' ('.$mainIp.')'."<br />";
        $content .= '==>'.$monService.'<=='." (MonJob ID: ".$monJob->getId().")<br />";
        $content .= 'Status now: '.$status.' (since '.$lastStatuschange.')'."<br />";
        return $content;
    }
    
    private function genContentUptimeSection(RemoteMonJobs $monJob){
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
}
