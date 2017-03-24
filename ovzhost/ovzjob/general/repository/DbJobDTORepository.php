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

namespace RNTForest\OVZJOB\general\repository;

use RNTForest\OVZJOB\general\utility\JobDTO;
use RNTForest\OVZJOB\general\psrlogger\LoggerInterface;

class DbJobDTORepository implements JobDTORepository{
    
    /**
    * @var \PDO
    */
    private $Pdo;
    
    /**
    * @var LoggerInterface
    */
    private $Logger;
    
    public function __construct(\PDO $pdo, LoggerInterface $logger){
        $this->Pdo = $pdo;
        $this->Logger = $logger;
    }
    
    /**
    * Returns the JobDTO element with the given id.
    * 
    * @param int $id
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function get($id){
        $stmt = $this->Pdo->prepare("SELECT id, type, params, executed, done, error, warning, retval FROM jobs WHERE id=:id");
        $stmt->execute(array(':id' => intval($id)));
        if(!$row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            throw new \Exception("JobDTO does not exist on this Server.");
        }
        
        $jobDTO = new JobDTO();
        $jobDTO->setId($id);
        $jobDTO->setType($row['type']);
        $jobDTO->setJsonParams($row['params']);
        $jobDTO->setExecuted($row['executed']);
        $jobDTO->setDone($row['done']);
        $jobDTO->setError($row['error']);
        $jobDTO->setWarning($row['warning']);
        $jobDTO->setRetval($row['retval']);
        return $jobDTO;                
    }
    
    /**
    * Creates a new JobDTO element in db and returns this created element.
    * 
    * @param JobDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function create(JobDTO $jobDTO){
        $stmt = $this->Pdo->prepare("INSERT INTO jobs (id, type, params, executed, done, error, warning, retval) VALUES(:id, :type, :params, :executed, :done, :error, :warning, :retval)");
        $stmt->bindValue(':id',intval($jobDTO->getId()));
        $stmt->bindValue(':type',$jobDTO->getType());
        $stmt->bindValue(':params',$jobDTO->getJsonParams());
        $stmt->bindValue(':executed',$jobDTO->getExecuted());
        $stmt->bindValue(':done',intval($jobDTO->getDone()));
        $stmt->bindValue(':error',$jobDTO->getError());
        $stmt->bindValue(':warning',$jobDTO->getWarning());
        $stmt->bindValue(':retval',$jobDTO->getRetval());
        if(!$stmt->execute()){
            $this->Logger->error("Pdo Error in INSERT, Error Code ".$this->Pdo->errorCode()." Message: ".json_encode($this->Pdo->errorInfo()));
        }
        
        return $this->get($jobDTO->getId());     
    }
    
    /**
    * Updates a given JobDTO element in db and returns the updated element.
    * 
    * @param JobDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function update(JobDTO $jobDTO){
        $stmt = $this->Pdo->prepare("UPDATE jobs SET type=:type, params=:params, executed=:executed, done=:done, error=:error, warning=:warning, retval=:retval WHERE id=:id");
        $stmt->bindValue(':id',intval($jobDTO->getId()));
        $stmt->bindValue(':type',$jobDTO->getType());
        $stmt->bindValue(':params',$jobDTO->getJsonParams());
        $stmt->bindValue(':executed',$jobDTO->getExecuted());
        $stmt->bindValue(':done',intval($jobDTO->getDone()));
        $stmt->bindValue(':error',$jobDTO->getError());
        $stmt->bindValue(':warning',$jobDTO->getWarning());
        $stmt->bindValue(':retval',$jobDTO->getRetval());
        if(!$stmt->execute()){
            $this->Logger->error("Pdo Error in UPDATE, Error Code ".$this->Pdo->errorCode()." Message: ".json_encode($this->Pdo->errorInfo()));
        }
        
        return $this->get($jobDTO->getId());
    }
    
    /**
    * Deletes the JobDTO element and returns the last state in db of this element.
    * 
    * @param JobDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function delete(JobDTO $jobDTO){
        return $this->deleteById($jobDTO->getId());
    }
    
    /**
    * Deletes the JobDTO element with specified id and returns the last state in db of this element.
    * 
    * @param int $id
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function deleteById($id){
        $jobDTO = $this->get($id);
        
        $stmt = $this->Pdo->prepare("DELETE FROM jobs WHERE id=:id");
        $stmt->bindValue(':id',intval($jobDTO->getId()));
        if(!$stmt->execute()){
            $this->Logger->error("Pdo Error in DELETE, Error Code ".$this->Pdo->errorCode()." Message: ".json_encode($this->Pdo->errorInfo()));
        }
        return $jobDTO;
    }
}
