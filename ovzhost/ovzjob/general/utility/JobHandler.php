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

namespace RNTForest\OVZJOB\general\utility;

use RNTForest\OVZJOB\general\jobs\AbstractJob;

class JobHandler implements JobExecutor{

    /**
    * @var AbstractJob[]
    */
    private $JobPrototypes = array();

    /**
    * @var AbstractJob
    */
    private $RunnableJob;
    
    /**
    * @var Context
    */
    private $Context;
    
    /**
    * @var {\RNTForest\OVZJOB\general\repository\JobDTORepository|JobDTORepository}
    */
    private $JobDTORepository;    
    /**
    * @var {\RNTForest\OVZJOB\general\psrlogger\LoggerInterface|LoggerInterface}
    */
    private $Logger;
    
    /**
    * @param Context $context
    */
    public function __construct(Context $context) {
        $this->Context = $context;
        $this->Logger = $context->getLogger();
        $this->JobDTORepository = $this->Context->getJobDTORepository();
    }
    
    /**
    * Executes a specified Job.
    * 
    * @param int $id
    */
    public function executeById($id){
        $this->Logger->debug("JobHandler durch executeById() gestartet");
        
        $jobDTO = $this->JobDTORepository->get($id);
        $this->RunnableJob->setParams($jobDTO->getJsonParams());
        $this->RunnableJob->setId($jobDTO->getId());
        
        $this->RunnableJob->run();
        
        $jobDTO->setDone($this->RunnableJob->getDone());
        $jobDTO->setError($this->RunnableJob->getError());
        $jobDTO->setWarning($this->RunnableJob->getWarning());
        $jobDTO->setRetval($this->RunnableJob->getRetval());
        $jobDTO = $this->genExecutedIfDone($jobDTO);
        
        $this->JobDTORepository->update($jobDTO);
    }                                         
    
    /**
    * @param JobDTO $jobDTO
    * @return JobDTO
    */
    private function genExecutedIfDone(JobDTO $jobDTO){
        if($jobDTO->getDone() > 0){
            $jobDTO->setExecuted(date("Y-m-d H:i:s"));
        }
        return $jobDTO;
    }
    
    public function addRunnableJob(AbstractJob $job){
        $this->RunnableJob = $job;
    }
}
