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

chdir(dirname(__FILE__));
require_once(__DIR__.'/vendor/autoload.php');

use RNTFOREST\OVZJOB\general\data\PdoFactory;
use RNTFOREST\OVZJOB\general\psrlogger\FileLogger;
use RNTFOREST\OVZJOB\general\cli\ExecCli;
use RNTFOREST\OVZJOB\general\utility\Context;
use RNTFOREST\OVZJOB\general\repository\DbJobDTORepository;
use RNTFOREST\OVZJOB\general\utility\JobExecutorFactory;

use RNTFOREST\OVZJOB\general\utility\JobDTO;

class JobSystemDebugStarter{
    private $JobExecutorFactory;
    private $Context;
    
    /**
    * CHANGE THIS JSON TO DEFINE JOB
    * 
    * @var string
    */
    private $JsonJob = '{"method":"runJob","id":1,"type":"ovz_restart_vs","params":"{\"CTID\":2201}"}';
    
    /**
    * @var \RNTFOREST\OVZJOB\general\utility\JobDTO
    */
    private $JobDTO;
    
    public function __construct(){
        $logger = new FileLogger();
        $logger->setLogLevel('debug');
        $pdo = PdoFactory::createSqlitePdo();
        $cli = new ExecCli($logger);
        $jobDtoRepo = new DbJobDTORepository($pdo, $logger);
        $this->Context = new Context($pdo,$logger,$cli,$jobDtoRepo);
        $this->JobExecutorFactory = new JobExecutorFactory($this->Context);
        $this->JobDTO = $this->insertJobInDb();
    }
    
    public function startNormal(){
        // start the execution
        $this->JobExecutorFactory->createOvzHandler($this->JobDTO->getId())->executeById(intval($this->JobDTO->getId()));
        
        // update JobDTO for view in debugger
        $this->JobDTO = $this->Context->getJobDTORepository()->get($this->JobDTO->getId());
        
        var_dump($this->JobDTO);
        // delete Test-Job after execution
        $this->Context->getJobDTORepository()->delete($this->JobDTO); 
    }
    
    private function insertJobInDb(){
        $body = json_decode($this->JsonJob,true);
        
        $id = $type = $params = null;
        if(key_exists('id',$body)){
            $id = intval($body['id']);
            $this->Context->getLogger()->debug("Job Id: ".$id);
        }else{
            throw new \Exception("Inkorrekter HTTP Request Body");
        }    
        if(key_exists('type',$body)){
            $type = $body['type'];
            $this->Context->getLogger()->debug("Job Type: ".$type);
        }else{
            throw new \Exception("Inkorrekter HTTP Request Body");
        }    
        if(key_exists('params',$body)){
            $params = $body['params'];
            $this->Context->getLogger()->debug("Job Params: ".$params);
        }else{
            throw new \Exception("Inkorrekter HTTP Request Body");
        }    

        $this->Context->getJobDTORepository()->deleteById($id); 
        
        $jobDTO = $this->createJobDTO($id, $type, $params);
        return $this->Context->getJobDTORepository()->create($jobDTO);
    }
    
    private function createJobDTO($id, $type, $jsonParams){
        $jobDTO = new JobDTO();
        $jobDTO->setId($id);
        $jobDTO->setJsonParams($jsonParams);
        $jobDTO->setType($type);
        return $jobDTO;    
    }
}  

$jobSystemDebugStarter = new JobSystemDebugStarter();
$jobSystemDebugStarter->startNormal();

