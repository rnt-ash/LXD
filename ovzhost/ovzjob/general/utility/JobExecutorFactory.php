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

namespace RNTFOREST\OVZJOB\general\utility;

class JobExecutorFactory{

    private $Context;
    
    public function __construct(Context $context){
        $this->Context = $context;
    }
    
    /**
    * @param int $id
    * @return JobHandler
    */
    public function createOvzHandler($id){
        $handler = new JobHandler($this->Context);
        $jobType = $this->Context->getJobDTORepository()->get($id)->getType();
        $className = $this->genClassPath($jobType);
        
        if(!class_exists($className)){
            // if the class is found and php tries to load it but the class includes errors (such as a not overriden abstract method)
            // php will gen a fatal E_ERROR, which is not possible to catch with an error_handler (by design)
            // if the class is simply not found, this exception will be thrown
            throw new \Exception("could not load ".$className);
        }
        $job = new $className($this->Context);
        
        $handler->addRunnableJob($job);
        
        return $handler;
    }
    
    /**
    * @param int $id
    * @return JobHandler
    */
    public function createBackgroundOvzHandler($id){
        $handler = new JobHandler($this->Context);
        $jobType = $this->Context->getJobDTORepository()->get($id)->getType();
        $className = $this->genClassPath($jobType,true);
        $handler->addRunnableJob(new $className($this->Context));
        
        return $handler;
    }

    /**
    * Returns the absolute classpath as string of a given jobtype (incl. namespace).
    * A JobType is in the following format: prefix_identifier1_identifier2 (i.e. ovz_new_vs or general_test_sendmail)
    * The prefix defines in which folder the needed class should be found (ovzjob/ovz/jobs, ovzjob/general/jobs, ...)
    * All classes for jobs are located in those subfolders and follow a strict namingconvention:
    * From prefix_identifier1_identifier2 (lowercase) will be made to uppercamelcase and added the suffix Job.
    * i.e. for jobType ovz_new_vs will be made the classname OvzNewVsJob 
    * and returned the absolute classepath with namespace RNTFOREST\OVZJOB\ovz\jobs\OvzNewVsJob
    * 
    * If the background-Flag is set, the suffix will be BackgroundJob
    * 
    * @param string $jobType
    * @param bool $background
    * @return string Absolute Clkasspath
    */
    private function genClassPath($jobType, $background = false){
        $identifier = $this->genJobIdentifier($jobType);
        $ucJobType = str_replace(' ','',ucwords(str_replace('_',' ',$jobType)));
        
        $classSuffix = "Job";
        if($background){
            $classSuffix = "BgJob";
        }
        
        return "RNTFOREST\\OVZJOB\\".$identifier."\\jobs\\".$ucJobType.$classSuffix;
    }
    
    /**
    * Returns the prefix of a job type.
    * i.e. ovz or general
    * 
    * @param string $jobType
    * @return string Identifier
    */
    private function genJobIdentifier($jobType){
        $splits = explode('_',$jobType);
        return $splits[0];
    }
}
