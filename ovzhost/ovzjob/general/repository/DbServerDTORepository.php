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

use RNTForest\OVZJOB\general\utility\ServerDTO;
use RNTForest\OVZJOB\general\psrlogger\LoggerInterface;

/**
* AKTUELL NICHT VERWENDET
*/
class DbServerDTORepository implements ServerDTORepository{
    
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
    * Returns the ServerDTO element with the given id.
    * 
    * @param string $uuid
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function get($uuid){
        $stmt = $this->Pdo->prepare("SELECT uuid, name, ovz_settings, fqdn, os, modified FROM servers WHERE uuid=:uuid");
        $stmt->execute(array(':uuid' => $uuid));
        if(!$row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            throw new \Exception("ServerDTO does not exist on this Server.");
        }
        
        $serverDTO = new ServerDTO($row['uuid'],$row['name'],$row['ovz_settings'],$row['fqdn'],$row['os'],$row['modified']);
        return $serverDTO;         
    }
    
    /**
    * Creates a new ServerDTO element in db and returns this created element.
    * 
    * @param ServerDTO $serverDTO
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function create(ServerDTO $serverDTO){
        $stmt = $this->Pdo->prepare("INSERT INTO servers (uuid, name, ovz_settings, fqdn, os, modified) VALUES(:uuid, :name, :ovz_settings, :fqdn, :os, :modified)");
        $stmt->bindValue(':uuid',$serverDTO->Uuid);
        $stmt->bindValue(':name',$serverDTO->Name);
        $stmt->bindValue(':ovz_settings',$serverDTO->OvzSettings);
        $stmt->bindValue(':fqdn',$serverDTO->Fqdn);
        $stmt->bindValue(':os',$serverDTO->Os);
        $stmt->bindValue(':modified',date("Y-m-d H:i:s"));
        if(!$stmt->execute()){
            $this->Logger->error("Pdo Error in INSERT, Error Code ".$this->Pdo->errorCode()." Message: ".json_encode($this->Pdo->errorInfo()));
        }
        
        return $this->get($serverDTO->Uuid);  
    }
    
    /**
    * Updates a given ServerDTO element in db and returns the updated element.
    * 
    * @param ServerDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function update(ServerDTO $serverDTO){
        $stmt = $this->Pdo->prepare("UPDATE servers SET name=:name, ovz_settings=:ovz_settings, fqdn=:fqdn, os=:os, modified=:modified WHERE uuid=:uuid");
        $stmt->bindValue(':uuid',$serverDTO->Uuid);
        $stmt->bindValue(':name',$serverDTO->Name);
        $stmt->bindValue(':ovz_settings',$serverDTO->OvzSettings);
        $stmt->bindValue(':fqdn',$serverDTO->Fqdn);
        $stmt->bindValue(':os',$serverDTO->Os);
        $stmt->bindValue(':modified',date("Y-m-d H:i:s"));
        if(!$stmt->execute()){
            $this->Logger->error("Pdo Error in UPDATE, Error Code ".$this->Pdo->errorCode()." Message: ".json_encode($this->Pdo->errorInfo()));
        }
        
        return $this->get($serverDTO->Uuid);
    }
    
    /**
    * Deletes the ServerDTO element and returns the last state in db of this element.
    * 
    * @param ServerDTO $jserverDTO
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function delete(ServerDTO $serverDTO){
        return $this->deleteByUuid($serverDTO->Uuid);
    }
    
    /**
    * Deletes the ServerDTO element with specified id and returns the last state in db of this element.
    * 
    * @param string $uuid
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function deleteByUuid($uuid){
        $serverDTO = $this->get($uuid);
        
        $stmt = $this->Pdo->prepare("DELETE FROM servers WHERE uuid=:uuid");
        $stmt->bindValue(':uuid',$serverDTO->Uuid);
        if(!$stmt->execute()){
            $this->Logger->error("Pdo Error in DELETE, Error Code ".$this->Pdo->errorCode()." Message: ".json_encode($this->Pdo->errorInfo()));
        }
        return $serverDTO;
    }    
}
