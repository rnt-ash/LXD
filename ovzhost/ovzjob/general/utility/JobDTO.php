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

class JobDTO {
    private $Id = 0;
    private $Type = '';
    private $JsonParams = '';
    private $Executed = '0000-00-00 00:00:00';
    private $Done = 0;
    private $Error = '';
    private $Warning = '';
    private $Retval = '';
    
    /**
    * @return int
    */
    public function getId(){
        return $this->Id;
    }
    
    /**
    * @param int $id
    */
    public function setId($id){
        $this->Id = intval($id);        
    }
    
    /**
    * @return string
    */
    public function getType(){
        return $this->Type;
    }
    
    /**
    * @param string $type
    */
    public function setType($type){
        if(!is_string($type)){
            $this->Type = 'unknown';
        }else{
            $this->Type = $type;
        }        
    }
    
    /**
    * @return string
    */
    public function getJsonParams(){
        return $this->JsonParams;
    }
    
    /**
    * @param string $jsonParams
    */
    public function setJsonParams($jsonParams){
        if(!is_string($jsonParams)){
            $this->JsonParams = '{"error":"incorrect jsonparams format set (should be string)"}';
        }else{
            $this->JsonParams = $jsonParams;
        }        
    }
    
    /**
    * @return string
    */
    public function getExecuted(){
        return $this->Executed;
    }
    
    /**
    * @param string $executed
    */
    public function setExecuted($executed){
        if(!is_string($executed)){
            $this->Executed = '0000-00-00 00:00:00';
        }else{
            $this->Executed = $executed;
        }        
    }
    
    /**
    * @param int
    */
    public function getDone(){
        return $this->Done;
    }
    
    /**
    * @param string $done
    */
    public function setDone($done){
        $this->Done = intval($done);        
    }
    
    /**
    * @return string
    */
    public function getError(){
        return $this->Error;
    }
    
    /**
    * @param string $error
    */
    public function setError($error){
        if(!is_string($error)){
            $this->Error = 'incorrect error format set (should be string)';
        }else{
            $this->Error = $error;
        }        
    }
    
    /**
    * @return string
    */
    public function getWarning(){
        return $this->Warning;
    }
    
    /**
    * @param string $warning
    */
    public function setWarning($warning){
        if(!is_string($warning)){
            $this->Warning = 'incorrect warning format set (should be string)';
        }else{
            $this->Warning = $warning;
        }        
    }
    
    /**
    * @return string
    */
    public function getRetval(){
        return $this->Retval;
    }
    
    /**
    * @param string $retval
    */
    public function setRetval($retval){
        if(!is_string($retval)){
            $this->Retval = 'incorrect retval format set (should be string)';
        }else{
            $this->Retval = $retval;
        }        
    }
    
    /**
    * @return string
    */
    public function toJsonString(){
        return json_encode($this->toArray());
    }
    
    public function toArray(){
       $array = array(
            "id"=>$this->Id,
            "type"=>$this->Type,
            "params"=>$this->JsonParams,true,
            "executed"=>$this->Executed,
            "done"=>$this->Done,
            "error"=>$this->Error,
            "warning"=>$this->Warning,
            "retval"=>$this->Retval
       ); 
       return $array;
    }
}
