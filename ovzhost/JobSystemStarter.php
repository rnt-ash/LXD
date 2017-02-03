#!/usr/bin/php
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

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/../ovzcp.local.config.php');

use RNTFOREST\OVZJOB\general\data\PdoFactory;
use RNTFOREST\OVZJOB\general\psrlogger\FileLogger;
use RNTFOREST\OVZJOB\general\cli\ExecCli;
use RNTFOREST\OVZJOB\general\utility\Context;
use RNTFOREST\OVZJOB\general\repository\DbJobDTORepository;
use RNTFOREST\OVZJOB\general\utility\JobExecutorFactory;

class JobSystemStarter{
    private $JobExecutorFactory;
    private $Context;
    
    public function __construct(){
        $logger = new FileLogger(LOGFILE);
        $logger->setLogLevel(LOGLEVEL);
        $pdo = PdoFactory::createSqlitePdo();
        $cli = new ExecCli($logger);
        $jobDtoRepo = new DbJobDTORepository($pdo, $logger);
        $this->Context = new Context($pdo,$logger,$cli,$jobDtoRepo);
        $this->JobExecutorFactory = new JobExecutorFactory($this->Context);
    }
    
    public function startNormal($id){
        try{
            $this->JobExecutorFactory->createOvzHandler(intval($id))->executeById(intval($id));
        }catch(\Exception $e){
            $this->Context->getLogger()->error($e->getMessage());
            echo $e->getMessage();
            return false;
        }
        return true;
    }
    
    public function startBackground($id){
        try{
            $this->JobExecutorFactory->createBackgroundOvzHandler(intval($id))->executeById(intval($id));
        }catch(\Exception $e){
            $this->Context->getLogger()->error($e->getMessage());
            echo $e->getMessage();
            return false;
        }
        return true;
    }
}  

$jobSystemStarter = new JobSystemStarter();

// First Param defines Method, normal and background are valid
// Second Param defines Job-ID 
if(!empty($_SERVER['argv'][1]) && is_string($_SERVER['argv'][1])
&& (!empty($_SERVER['argv'][2]) &&is_numeric($_SERVER['argv'][2]))
){
    $method = $_SERVER['argv'][1];
    $id = intval($_SERVER['argv'][2]);
    switch($method){
        case 'normal':
            if(!$jobSystemStarter->startNormal($id)) exit(3);
            break;
        case 'background':
            if(!$jobSystemStarter->startBackground($id)) exit(4);
            break;
        default:
            // invalid method
            exit(2);
            break;
    }
}else{
    // invalid params
    exit(1);
}
// everything ok
exit(0);
