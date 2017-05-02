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

use Phalcon\Cli\Task;

use RNTForest\core\services\Push;
use RNTForest\ovz\services\MonSystem;
use RNTForest\ovz\services\MonHealing;
use RNTForest\ovz\models\VirtualServers;
use RNTForest\ovz\models\MonRemoteJobs;

class MonitoringTask extends Task
{
    public function mainAction(){
        try{

        }catch(\Exception $e){
            $this->logger->error($e->getMessage());
        }
    }
    
    public function runJobsAction(){
        $system = new MonSystem();
        $system->runMonRemoteJobs();
    }
    
    public function runCriticalJobsAction(){
        $healing = new MonHealing();
        $healing->healFailedMonRemoteJobs();
    }
    
    public function runLocalJobsAction(){
        $system = new MonSystem();
        $system->runMonLocalJobs();
    }
    
    public function recomputeUptimesAction(){
        $system = new MonSystem();
        $system->recomputeUptimes();
    }
    
    public function genMonUptimesAction(){
        $system = new MonSystem();
        $system->genMonUptimes();
    }
    
    public function genMonLocalDailyLogsAction(){
        $system = new MonSystem();
        $system->genMonLocalDailyLogs();
    }
    
    public function testAction(){
        $system = new MonSystem();
        $system->test();
    }
}
