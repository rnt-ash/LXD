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
    public function runJobs(){
        try{
            //$monJobs = $this->modelManager->executeQuery("SELECT * FROM \\RNTForest\\ovz\\models\\MonRemoteJobs WHERE active = 1 AND status != 'down' AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_run)>period*60");
            $monJobs = MonRemoteJobs::find(
                [
                "active = 1 AND status != 'down' AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_run)>period*60",
                ]
            );
            echo("runJobs ".count($monJobs)." MonJobsRemote\n");
            foreach($monJobs as $monJob){
                echo(json_encode($monJob));
                $monJob->execute();
            }
            
        }catch(\Exception $e){
            echo $e->getMessage()."\n";
        }
        
    } 
}
