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

class MonAlarm extends \Phalcon\DI\Injectable
{
    /**
    * put your comment there...
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
        $lastAlarmTimestamp = DateConverter::createUnixTimestampFromDateTime($lastAlarm);
        $currentTimestamp = time();
        
        return $currentTimestamp > ($lastAlarmTimestamp + $alarmPeriodInSeconds);
    }
    
    private function genAlarmContentRemoteMonJob(RemoteMonJob $monJob){
        $content = '';
        $content .= $this->genContentGeneralSection($monJob);
        $content .= $this->genContentUptimeSection($monJob);
        return $content;    
    }   
}
