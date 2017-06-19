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

use \RNTForest\ovz\models\MonJobs;
use \RNTForest\ovz\models\MonLogs;
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
    
    /**
    * Heals remote MonJobs with status 'down'.
    * 
    * 
    * remote only
    */
    public function healFailedMonRemoteJobs(){
        try{
            $monJobs = MonJobs::find(["mon_type = 'remote' AND active = 1 AND status = 'down'"]);
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
    
    /**
    * Tries to immediately check again, heal with a reboot of the ct (if poosible).
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    */
    private function healStepwise(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
        $monJob->execute();
        $this->logger->debug("executed with value ".$monJob->getStatus());
            
        if($monJob->isInErrorState()){
            if($this->shouldAlarmImmediately($monJob)){
                $this->logger->debug('MonHealing checked to alarm this job immediately: '.$monJob->getId());
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
    * remote only
    * 
    * @param MonJobs $monJob
    * @return boolean
    * @throws \Exception
    */
    private function shouldAlarmImmediately(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
        // alarm immediately if healing is not active
        if(!$monJob->getHealing()){
            return True;
        }
        
        // alarm immediately if last healjob was a restart of the vs
        $lastHealJobType = $this->getLastRelevantHealJobType($monJob);    
        if($lastHealJobType == 'ovz_restart_vs'){
            return True;
        }
        // if the jobsystem fails it should alarm immediately
        elseif($lastHealJobType == 'jobsystemfailed'){
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
    * remote only
    * 
    * @param MonJobs $monJob
    * @return string
    */
    private function getLastRelevantHealJobType(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
        $healJobType = '';
        try{
            // get the second to last (vorletzt auf deutsch...) monlog
            // get the modified of the last up 
            $maxUpModified = MonLogs::maximum(
                [
                    "column" => "modified",
                    "conditions" => "mon_remote_jobs_id = ".$monJob->getId()." AND value = 1",
                ]
            );
            if(empty($maxUpModified))$maxUpModified = 0;
            $monJobWithHealJob = MonLogs::findFirst(
                [
                    "mon_remote_jobs_id = :id: AND value = 0 AND heal_job != '' AND modified > :maxupmodified:",
                    "order" => "modified DESC",   
                    "bind" => [
                        "id" => $monJob->getId(),
                        "maxupmodified" => $maxUpModified,
                    ]
                ]
            );
            if(!empty($monJobWithHealJob)){
                $healJobId = $monJobWithHealJob->getHealJob();
                if(is_numeric($healJobId) && $healJobId > 0){
                    $healJob = \RNTForest\core\models\Jobs::findFirst($healJobId);
                    $healJobType = $healJob->getType();
                    $this->logger->notice("found healjob: ".$healJobType);
                }elseif($healJobId == -1){
                    $this->logger->error("no existent healjob found, but log was marked as healed.");
                    $healJobType = 'jobsystemfailed';
                }   
            }
            
        }catch(\Exception $e){
            $this->logger->error('Problem while get last relevant healjobtype: '.$e->getMessage());
        } 
        return $healJobType;
    }
    
    /**
    * Executes a healjob.
    * 
    * @param string $healJobType
    * @param MonJobs $monJob
    * @param string $pending default ''
    * @return integer healJobId
    * @throws \Exception
    */
    private function executeHealJob($healJobType, MonJobs $monJob, $pending = ''){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
        $parent = $this->getParentOfMonServerInstance($monJob);
        if(!($parent instanceof \RNTForest\ovz\models\PhysicalServers)){
            throw new \Exception($this->translate("monitoring_parent_cannot_execute_jobs"));
        }
        $push = $this->getPush();
        $job = null;
        // set healjobid to minus one to mark the monlog that a healjob will be executed
        $healJobId = -1;
        
        $monServer = $this->getMonServerInstance($monJob);
        if(!($monServer instanceof \RNTForest\ovz\models\VirtualServers)){
            throw new \Exception($this->translate("monitoring_monserver_is_not_managable"));
        }
        $params['UUID'] = $monServer->getOvzUuid();
        try{
            // first, mark monlog to be healed to prevent loops
            $monLog = MonLogs::findFirst(
                [
                "mon_remote_jobs_id" => $monJob->getId(),
                "order" => "id DESC",
                ]
            );
            $monLog->setHealJob($healJobId);
            $monLog->save();
            
            // then heal
            $job = $push->executeJob($parent,$healJobType,$params,$pending);
            $healJobId = $job->getId();

            // only set the id of the job, when the job id is not null (prevent loops)
            if($job->getId() != null){
                $monLog->setHealJob($job->getId());
                $monLog->save();
                
                if($job->getDone() == '1'){
                    // wait some seconds if healjob was successful, so that the server has time to be up again for the next monitoring
                    sleep(10);
                }
            
                if($job->getDone() != '1'){
                    throw new \Exception($this->translate("monitoring_healjob_failed").$job->getError());    
                }   
            }
            
        }catch(\Exception $e){
            $this->logger->error("HealJob execution failed: ".$e->getMessage());
            if($job != null && ($job->getDone() == 0 || $job->getDone() == 2)){
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
    * remote only
    * 
    * @param MonJobs $monJob
    * @return MonServerInterface
    */
    private function getMonServerInstance(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
        $server = $monJob->getServerClass()::findFirst($monJob->getServerId());
        if(!($server instanceof MonServerInterface)){
            throw new \Exception($this->translate("monitoring_mon_server_not_implements_interface"));    
        }
        return $server;
    }
    
    /**
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    * @retorn object
    */
    private function getParentOfMonServerInstance(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
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
