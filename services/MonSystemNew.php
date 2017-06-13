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
use \RNTForest\ovz\utilities\AllInfoUpdater;

class MonSystem extends \Phalcon\DI\Injectable
{
    /**
    * 
    * @var \Phalcon\Logger\AdapterInterface
    */
    private $logger;
    
    /**
    * @var \Phalcon\Mvc\Model\Manager
    */
    private $modelManager;
 
    public function __construct(){
        $this->logger = $this->getDI()['logger'];
        $this->modelManager = $this->getDI()['modelsManager'];
    }
    
    /**
    * Runs the current open remote MonJobs.
    * Recommendation: every minute
    * 
    */
    public function runMonRemoteJobs(){
        try{
            $monJobs = MonJobs::find(
                [
                "mon_type = 'remote' AND active = 1 AND status != 'down' AND UNIX_TIMESTAMP(NOW())-IFNULL(UNIX_TIMESTAMP(last_run),0)>period*60",
                ]
            );
            $this->logger->debug("runJobs ".count($monJobs)." MonRemoteJobs");
            foreach($monJobs as $monJob){
                $monJob->execute();
            }
            
        }catch(\Exception $e){
            $this->logger->debug("runMonRemoteJobs: ".$e->getMessage());
        }
        
    }
    
    /**
    * Runs all local MonJobs.
    * Recommendation: every minute
    * 
    */
    public function runMonLocalJobs(){
        try{
            // first update all info from all servers
            AllInfoUpdater::updateAllServers();
            
            // then do the monitoring
            $beforeLocalMonitoring = microtime(true);
            
            $monJobs = MonJobs::find(
                [
                "mon_type = 'local' AND active = 1 AND UNIX_TIMESTAMP(NOW())-IFNULL(UNIX_TIMESTAMP(last_run),0)>period*60",
                ]
            );
            $this->logger->debug("runLocalJobs ".count($monJobs)." MonLocalJobs");
            foreach($monJobs as $monJob){
                // separate Exception-Handling to not abort the whole process if one MonLocalJob execution fails
                try{
                    $monJob->execute();
                }catch(\Exception $e){
                    $this->logger->warning("runMonLocalJobs execute Job-ID ".$monJob->getId().": ".$e->getMessage());
                }
                if($monJob->getStatus() != 'normal'){
                    $this->logger->notice('Job will be alarmed: '.$monJob->getId());
                    $this->getMonAlarm()->notifyMonLocalJobs($monJob);                
                }
            }
            $durationLocalMonitoring = (microtime(true))-$beforeLocalMonitoring;
            $this->logger->debug('duration of for localmonitoring '.$durationLocalMonitoring.' seconds');
        
        }catch(\Exception $e){
            $this->logger->error("runMonLocalJobs: ".$e->getMessage());
        }
    }
    
    /**
    * 
    * @return \RNTForest\ovz\services\MonAlarm
    */
    private function getMonAlarm(){
        return $this->getDI()['monAlarm'];
    }
    
    /**
    * Recomputes the Field uptime of a remote MonJobs from the available MonLogs and MonUptimes.
    * Recommendation: every hour
    * 
    */
    public function recomputeUptimes(){
       try{
            $this->logger->debug("Start with recomputeUptimes");
            $monJobs = MonJobs::find(
                [
                    "mon_type = 'remote'",
                ]
            );
            
            foreach($monJobs as $monJob){
                $this->logger->debug("handle monjob id ".$monJob->getId());
                
                $monJob->recomputeUptime();
            }
        }catch(\Exception $e){
            $this->logger->debug("recomputeUptimes: ".$e->getMessage());
        }  
    }
    
    /**
    * Generates the MonUptimes from old remote MonLogs.
    * Recommendation: every month
    * 
    */
    public function genMonUptimes(){
        try{
            $this->logger->debug("Start with genMonUptimes");
            $monJobs = MonJobs::find(
                [
                    "mon_type = 'remote'",
                ]
            );
            
            // collect all ids for cleanup logs with no monjob afterwards
            $monJobIds = array();
            foreach($monJobs as $monJob){
                $this->logger->debug("handle monjob id ".$monJob->getId());
                $monJobIds[] = $monJob->getId();
                
                $monJob->genMonUptimes();
                
                $monJob->recomputeUptime();
            }
            
            if(!empty($monJobIds)){
                $ids = implode(',',$monJobIds);
                // delete MonLogs with nonexisting remote MonJobs
                $rows = $this->modelManager->executeQuery("SELECT \\RNTForest\\ovz\\models\\MonLogs.mon_jobs_id FROM \\RNTForest\\ovz\\models\\MonLogs LEFT OUTER JOIN \\RNTForest\\ovz\\models\\MonJobs ON \\RNTForest\\ovz\\models\\MonLogs.mon_jobs_id = \\RNTForest\\ovz\\models\\MonJobs.id WHERE \\RNTForest\\ovz\\models\\MonJobs.id IS NULL");
                foreach($rows as $row){
                    $this->modelManager->executeQuery("DELETE FROM \\RNTForest\\ovz\\models\\MonLogs WHERE mon_jobs_id = (:id:)",['id'=>$row['mon_jobs_id']]);
                }
            }

        }catch(\Exception $e){
            $this->logger->debug("genMonUptimes: ".$e->getMessage());
        }    
    }
    
    /**
    * Generates the LocalDailyLogs from old local MonJobs.
    * Recommendatoin: every month
    * 
    */
    public function genMonLocalDailyLogs(){
        try{
            $this->logger->debug("Start with genMonLocalDailyLogs");
            $monJobs = MonJobs::find(
                [
                    "mon_type = 'local'",
                ]
            );
            
            // collect all ids for cleanup logs with no monjob afterwards
            $monJobIds = array();
            foreach($monJobs as $monJob){
                $this->logger->debug("handle monjob id ".$monJob->getId());
                $monJobIds[] = $monJob->getId();
            
                // genMonUptime
                $monJob->genMonLocalDailyLogs();
            }
            
            if(!empty($monJobIds)){
                $ids = implode(',',$monJobIds);
                // delete MonLogs with nonexisting local MonJobs
                $rows = $this->modelManager->executeQuery("SELECT \\RNTForest\\ovz\\models\\MonLogs.mon_jobs_id FROM \\RNTForest\\ovz\\models\\MonLogs LEFT OUTER JOIN \\RNTForest\\ovz\\models\\MonJobs ON \\RNTForest\\ovz\\models\\MonLogs.mon_local_jobs_id = \\RNTForest\\ovz\\models\\MonJobs.id WHERE \\RNTForest\\ovz\\models\\MonJobs.id IS NULL");
                foreach($rows as $row){
                    $this->modelManager->executeQuery("DELETE FROM \\RNTForest\\ovz\\models\\MonLogs WHERE mon_jobs_id = (:id:)",['id'=>$row['mon_jobs_id']]);
                }
            }

        }catch(\Exception $e){
            $this->logger->debug("genMonLocalDailyLogs: ".$e->getMessage());
        }    
    }
    
    public function test(){
        return;
        try{$this->logger->debug("Start with test");
            AllInfoUpdater::updateAllServers();
            
            $beforeLocalMonitoring = microtime(true);
            
            $monJobs = MonJobs::find(
                [
                "mon_type = 'local' AND active = 1 AND UNIX_TIMESTAMP(NOW())-IFNULL(UNIX_TIMESTAMP(last_run),0)>period*60",
                ]
            );
            $this->logger->debug("runLocalJobs ".count($monJobs)." MonLocalJobs");
            foreach($monJobs as $monJob){
                // separate Exception-Handling to not abort the whole process if one MonLocalJob execution fails
                try{
                    $monJob->execute();
                }catch(\Exception $e){
                    $this->logger->debug("runMonLocalJobs execute Job-ID ".$monJob->getId().": ".$e->getMessage());
                }
                if($monJob->getStatus() != 'normal'){
                    $this->getMonAlarm()->notifyMonLocalJobs($monJob);                
                }
            }
            $durationLocalMonitoring = (microtime(true))-$beforeLocalMonitoring;
            $this->logger->debug('duration of for localmonitoring '.$durationLocalMonitoring.' seconds');
        
             
        }catch(\Exception $e){
            $this->logger->error("test: ".$e->getMessage());
        }
    }
}
