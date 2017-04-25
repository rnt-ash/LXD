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
use \RNTForest\ovz\models\MonLocalJobs;
use \RNTForest\ovz\models\PhysicalServers;
use \RNTForest\ovz\models\VirtualServers;

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
    * Runs the current open MonRemoteJobs.
    * Recommendation: every minute
    * 
    */
    public function runMonRemoteJobs(){
        try{
            //$monJobs = $this->modelManager->executeQuery("SELECT * FROM \\RNTForest\\ovz\\models\\MonRemoteJobs WHERE active = 1 AND status != 'down' AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_run)>period*60");
            $monJobs = MonRemoteJobs::find(
                [
                "active = 1 AND status != 'down' AND UNIX_TIMESTAMP(NOW())-IFNULL(UNIX_TIMESTAMP(last_run),0)>period*60",
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
    * Runs all MonLocalJobs.
    * Recommendation: every minute
    * 
    */
    public function runMonLocalJobs(){
        try{
            $monJobs = MonLocalJobs::find(
                [
                "active = 1 AND UNIX_TIMESTAMP(NOW())-IFNULL(UNIX_TIMESTAMP(last_run),0)>period*60",
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
            
        }catch(\Exception $e){
            $this->logger->debug("runMonLocalJobs: ".$e->getMessage());
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
    * Recomputes the Field uptime of a MonRemoteJobs from the available MonRemoteLogs and MonUptimes.
    * Recommendation: every hour
    * 
    */
    public function recomputeUptimes(){
       try{
            $this->logger->debug("Start with recomputeUptimes");
            $monJobs = MonRemoteJobs::find();
            
            foreach($monJobs as $monJob){
                $this->logger->debug("handle monjob id ".$monJob->getId());
                
                $monJob->recomputeUptime();
            }
        }catch(\Exception $e){
            $this->logger->debug("recomputeUptimes: ".$e->getMessage());
        }  
    }
    
    /**
    * Generates the MonUptimes from old MonRemoteLogs.
    * Recommendation: every month
    * 
    */
    public function genMonUptimes(){
        try{
            $this->logger->debug("Start with genMonUptimes");
            $monJobs = MonRemoteJobs::find();
            
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
                // delete MonRemoteLogs with nonexisting MonRemoteJobs
                $this->modelManager->executeQuery("DELETE FROM \\RNTForest\\ovz\\models\\MonRemoteLogs WHERE mon_remote_jobs_id NOT IN (?0)",[$ids]);
            }

        }catch(\Exception $e){
            $this->logger->debug("genMonUptimes: ".$e->getMessage());
        }    
    }
    
    /**
    * Generates the LocalDailyLogs from old MonLocalLogs.
    * Recommendatoin: every month
    * 
    */
    public function genMonLocalDailyLogs(){
        try{
            $this->logger->debug("Start with genMonLocalDailyLogs");
            $monJobs = MonLocalJobs::find();
            
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
                // delete MonLocalLogs with nonexisting MonLocalJobs
                $this->modelManager->executeQuery("DELETE FROM \\RNTForest\\ovz\\models\\MonLocalLogs WHERE mon_local_jobs_id NOT IN (?0)",[$ids]);
            }

        }catch(\Exception $e){
            $this->logger->debug("genMonLocalDailyLogs: ".$e->getMessage());
        }    
    }
}
