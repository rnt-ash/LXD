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
    * Can be executed every minute or more.
    * 
    */
    public function runMonRemoteJobs(){
        try{
            //$monJobs = $this->modelManager->executeQuery("SELECT * FROM \\RNTForest\\ovz\\models\\MonRemoteJobs WHERE active = 1 AND status != 'down' AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_run)>period*60");
            $monJobs = MonRemoteJobs::find(
                [
                "active = 1 AND status != 'down' AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_run)>period*60",
                ]
            );
            echo("runJobs ".count($monJobs)." MonRemoteJobs\n");
            foreach($monJobs as $monJob){
                echo(json_encode($monJob));
                $monJob->execute();
            }
            
        }catch(\Exception $e){
            echo $e->getMessage()."\n";
        }
        
    }
    
    public function runMonLocalJobs(){
        try{
            $this->updateOvzStatisticsOnAllServers();
            $monJobs = MonLocalJobs::find(
                [
                "active = 1",
                ]
            );
            echo("runLocalJobs ".count($monJobs)." MonLocalJobs\n");
            foreach($monJobs as $monJob){
                echo(json_encode($monJob));
                $monJob->execute();
                if($monJob->getStatus() != 'normal'){
                    $this->getMonAlarm()->notifyMonLocalJobs($monJob);                
                }
            }
            
        }catch(\Exception $e){
            echo $e->getMessage()."\n";
        }
    }
    
    private function updateOvzStatisticsOnAllServers(){
        try{
            $physicals = PhysicalServers::find(
            [
            "ovz = 1",
            ]
            );
            foreach($physicals as $physical){
                $physical->updateOvzStatistics();
            }
            
            // could be better done with a join and PHQL, would be more performant
            $virtuals = VirtualServers::find();
            foreach($virtuals as $virtual){
                if($virtual->PhysicalServers->getOvz() == '1'){
                    $virtual->updateOvzStatistics();
                }
            }
        }catch(\Exception $e){
            echo $e->getMessage()."\n";
        }     
    } 
    
    /**
    * 
    * @return \RNTForest\ovz\services\MonAlarm
    */
    private function getMonAlarm(){
        return $this->getDI()['monAlarm'];
    }
}
