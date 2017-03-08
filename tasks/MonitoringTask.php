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
use RNTForest\ovz\models\VirtualServers;
use RNTForest\ovz\models\RemoteMonJobs;

class MonitoringTask extends Task
{
    public function mainAction(){
        echo "called main in ovz..." . PHP_EOL;

        try{

        }catch(\Exception $e){
            $this->logger->error($e->getMessage());
        }
    }
    
    public function localAction(){
        echo "called local in ovz...".PHP_EOL; 
        $monJob = RemoteMonJobs::findFirst(1);
        $alarmSystem = new \RNTForest\ovz\services\MonAlarm();
        $alarmSystem->alarmRemoteMonJob($monJob);
    }
    
    public function runJobsAction(){
        echo "called runJobs in ovz...".PHP_EOL;
        $system = new \RNTForest\ovz\services\MonSystem();
        $system->runJobs();
    }
}
