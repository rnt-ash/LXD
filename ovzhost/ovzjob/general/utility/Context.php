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

use RNTFOREST\OVZJOB\general\data\DbInterface;
use RNTFOREST\OVZJOB\general\psrlogger\LoggerInterface;
use RNTFOREST\OVZJOB\general\cli\CliInterface;
use RNTFOREST\OVZJOB\general\repository\JobDTORepository;

class Context{
    
    /**
    * @var \PDO
    */
    private $Pdo;
    
    /**
    * @var LoggerInterface
    */
    private $Logger;
    
    /**
    * @var CliInterface
    */
    private $Cli;
    
    /**
    * @var JobDTORepository
    */
    private $JobDTORepository;
    
    public function __construct(\PDO $pdo, LoggerInterface $logger, CliInterface $cli, JobDTORepository $jobDTORepository){
        $this->Pdo = $pdo;
        $this->Logger = $logger;
        $this->Cli = $cli;
        $this->JobDTORepository = $jobDTORepository;
    }
    
    /**
    * @return DbInterface
    */
    public function getDb(){
        return $this->Db;
    }
    
    /**
    * @return LoggerInterface
    */
    public function getLogger(){
        return $this->Logger;
    }
    
    /**
    * @return CliInterface
    */
    public function getCli(){
        return $this->Cli;
    }
    
    /**
    * @return JobDTORepository
    */
    public function getJobDTORepository(){
        return $this->JobDTORepository;
    }
    
}
