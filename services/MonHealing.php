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

use \RNTForest\ovz\models\MonRemoteJobs;
use \RNTForest\ovz\models\MonRemoteLogs;
use \RNTForest\ovz\interfaces\MonServerInterface;

class MonHealing extends \Phalcon\DI\Injectable
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
    
    public function healFailedMonRemoteJobs(){
        try{
            $monJobs = MonRemoteJobs::find(["active = 1 AND status = 'down'"]);
            $this->logger->debug("healFailedMonRemoteJobs ".count($monJobs)." MonRemoteJobs");
            
            foreach($monJobs as $monJob){
                $this->healStepwise($monJob);
                $monJob->save();
                $this->logger->debug("MonJob id ".$monJob->getId()." ".$this->translate("monitoring_healing_executed"));
            }
        }catch(\Exception $e){
            echo $e->getMessage()."\n";   
        }
    }
    
    private function healStepwise(MonRemoteJobs $monJob){
        $monJob->execute();
        $this->logger->debug("executed with value ".$monJob->getStatus());
            
        if($monJob->isInErrorState()){
            if($this->shouldAlarmImmediately($monJob)){
                // recompute uptime first for actual data in notification
                $monJob->recomputeUptime();
                // alarm if nothing else is possible (termination condition)
                $this->getMonAlarm()->alarmMonRemoteJobs($monJob);  
            }else{
                // heal, only if not muted
                if(!$monJob->getMuted()){
                    $server = $this->getMonServerInstance($monJob);
                    
                    $pending = '';
                    if($server instanceof \RNTForest\ovz\models\VirtualServers){
                        $pending = 'RNTFOREST\ovz\models\VirtualServers:'.$server->getId().':general:1';
                    }
                    
                    $healJobId = $this->executeHealJob('ovz_restart_vs',$monJob,$pending);
                    $monJob->setRecentHealJobId($healJobId);

                    // check again (recursion call)
                    $this->healStepwise($monJob);    
                    // and notify AFTER about the healjob (so that the state after the new check is available in the info-mail)
                    $this->getMonAlarm()->informAboutHealJob($monJob);
                }
            }
        }else{
            if($monJob->getAlarmed() == '1'){
                // recompute uptime first for actual data in notification
                $monJob->recomputeUptime();
                $this->getMonAlarm()->disalarmMonRemoteJobs($monJob);
            }else{
                // if no healjob is sent and service is up again without any interaction
                if(!$monJob->hadRecentHealJob()){
                     $this->getMonAlarm()->informAboutShortDowntime($monJob);    
                }
            }
        }
    }
    
    /**
    * Checks if monitoring should immediately alarm or try to heal first.
    * Under certain circumstances it is impossible to heal autonomous, e.g. hostserver is down or no network.
    * So it should alarm to notify an admin.
    * 
    * @param MonRemoteJobs $monJob
    * @return boolean
    * @throws \Exception
    */
    private function shouldAlarmImmediately(MonRemoteJobs $monJob){
        // alarm immediately if healing is not active
        if(!$monJob->getHealing()){
            return True;
        }
            
        // alarm immediately if last healjob was a restart of the vs
        $lastHealJobType = $this->getLastRelevantHealJobType($monJob);    
        if($lastHealJobType == 'ovz_restart_vs'){
            return True;
        } 
        
        // alarm immediately if the parent of the MonServer is a PhysicalServer but has not an ovz=1 enabled
        $parent = $this->getParentOfMonServerInstance($monJob);
        if($parent instanceof \RNTForest\ovz\models\PhysicalServers){
            if($parent->getOvz() == 0){
                return True;
            }
        }
        return False; 
    }
    
    /**
    * Returns the type of the last relevant HealJob for this MonJob.
    * 
    * @param MonRemoteJobs $monJob
    * @return string
    */
    private function getLastRelevantHealJobType(MonRemoteJobs $monJob){
        $healJobType = '';
        try{
            // get the second to last (vorletzt auf deutsch...) monlog
            $monLogResult = MonRemoteLogs::find(
                [
                "mon_remote_jobs_id" => $monJob->getId(),
                "order" => "id DESC",
                "limit" => "2",
                ]
            );
            $monLogResult->seek(1);
            $monLog = $monLogResult->current();
            
            $healJobId = $monLog->getHealJob();
            if(is_numeric($healJobId) && $healJobId > 0){
                $healJob = \RNTForest\core\models\Jobs::findFirst($healJobId);
                $healJobType = $healJob->getType();
            }
        }catch(\Exception $e){
            echo $e->getMessage()."\n";
        } 
        return $healJobType;
    }
    
    /**
    * Executes a healjob.
    * 
    * @param string $healJobType
    * @param MonRemoteJobs $monJob
    * @param string $pending default ''
    * @return integer healJobId
    * @throws \Exception
    */
    private function executeHealJob($healJobType, MonRemoteJobs $monJob, $pending = ''){
        $parent = $this->getParentOfMonServerInstance($monJob);
        if(!($parent instanceof \RNTForest\ovz\models\PhysicalServers)){
            throw new \Exception($this->translate("monitoring_parent_cannot_execute_jobs"));
        }
        $push = $this->getPush();
        $job = null;
        $healJobId = 0;
        
        $monServer = $this->getMonServerInstance($monJob);
        if(!($monServer instanceof \RNTForest\ovz\models\VirtualServers)){
            throw new \Exception($this->translate("monitoring_monserver_is_not_managable"));
        }
        $params['UUID'] = $monServer->getOvzUuid();
        try{
            $job = $push->executeJob($parent,$healJobType,$params,$pending);
            $healJobId = $job->getId();
            $monLog = MonRemoteLogs::findFirst(
                [
                "mon_remote_jobs_id" => $monJob->getId(),
                "order" => "id DESC",
                ]
            );
            $monLog->setHealJob($job->getId());
            $monLog->save();
            if($job->getDone() != '1'){
                throw new \Exception($this->translate("monitoring_healjob_failed").$job->getError());    
            }
            
            // wait some seconds if healjob was successful, so that the server has time to be up again for the next monitoring
            if($job->getDone() == '1'){
                sleep(10);
            }
        }catch(\Exception $e){
            echo $e->getMessage();
            if($job != null && $job->getDone() == 0){
                // if job was not sent it should be marked as failed so that it wont be executed in future
                $error = $this->translate("monitoring_healjob_not_executed_error");
                $job->setDone(2);
                $job->setError($error);
                $job->save();
                $this->logger->error("Healjob: ".$job->getId(). " ".$error);
            }
        }

        return $healJobId;  
    }
    
    /**
    * 
    * @param MonRemoteJobs $monJob
    * @return MonServerInterface
    */
    private function getMonServerInstance(MonRemoteJobs $monJob){
        $server = $monJob->getServerClass()::findFirst($monJob->getServerId());
        if(!($server instanceof MonServerInterface)){
            throw new \Exception($this->translate("monitoring_mon_server_not_implements_interface"));    
        }
        return $server;
    }
    
    /**
    * 
    * @param MonRemoteJobs $monJob
    * @retorn object
    */
    private function getParentOfMonServerInstance(MonRemoteJobs $monJob){
        $server = $this->getMonServerInstance($monJob);
        return $server->getParentClass()::findFirst($server->getParentId());
    }
    
    /**
    * 
    * @return \RNTForest\ovz\services\MonAlarm
    */
    private function getMonAlarm(){
        return $this->getDI()['monAlarm'];
    }
    
    /**
    * 
    * @return \RNTForest\core\services\Push
    */
    private function getPush(){
        return $this->getDI()['push'];
    }
}
