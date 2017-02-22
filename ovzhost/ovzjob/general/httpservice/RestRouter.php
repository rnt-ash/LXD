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

namespace RNTFOREST\OVZJOB\general\httpservice;

use RNTFOREST\OVZJOB\general\utility\JobDTO;
use RNTFOREST\OVZJOB\general\utility\Context;
use RNTFOREST\OVZJOB\general\utility\ServerDTO;

class RestRouter{

    private $JobExecutor;
    private $Context;

    private $HttpMethod = '';
    private $HttpBody = '';

    /**
    * @var HttpResponse
    */
    private $HttpResponse;


    public function __construct(Context $context){
        $this->Context = $context;
        $this->HttpResponse = new HttpResponse();
    }

    public function handleRequest()  {
        try{
            $this->HttpMethod = $_SERVER['REQUEST_METHOD'];
            $this->HttpBody = file_get_contents('php://input',true);

            $this->Context->getLogger()->debug("Method: ".$this->HttpMethod);
            $this->Context->getLogger()->debug("Input: ".$this->HttpBody);

            switch($this->HttpMethod){
                case 'POST':
                    $this->handleHttpPost();
                    break;
                default:
                    http_response_code(501);  // not implemented is code 501
                    throw new \Exception("HTTP method is not supported");
                break;
            }
        }catch(\Exception $e){
            $this->Context->getLogger()->error($e->getMessage());
            // set http response code to 500 if no other special error code is already set
            if(http_response_code()<500){
                http_response_code(500);
            }
            $this->HttpResponse->Body = $e->getMessage();
        }
        
        // has to pe separate because if encryption fails it should response a fix error message
        try{
            $this->Context->getLogger()->debug("HttpResponseBody: ".json_encode($this->HttpResponse->Body));
            echo $this->getEncryptedResponseBody();    
        }catch(\Exception $e){
            $this->Context->getLogger()->error($e->getMessage());
            http_response_code(500);
            echo $e->getMessage();
        }
        
    }  

    private function handleHttpPost(){
        if($this->HttpMethod != 'POST'){
            throw new \Exception("wrong HTTP method '".$this->HttpMethod."' for handleHttpPost.");
        }
        $body = $this->getDecryptRequestBody();

        $method = '';
        if(key_exists('method',$body)){
            $method = $body['method'];
            $this->Context->getLogger()->debug("Methode: ".$method);
        }else{
            throw new \Exception("incorrect HTTP request body");
        }    

        switch($method){
            case 'runJob':
                $this->runJob($body);
                break;
            case 'getJob':
                $this->getJob($body);
                break;
            case 'delJob':
                $this->delJob($body);
                break;
            case 'getLogs':
                $this->getLogs($body);
                break;
            default:
                throw new \Exception("Method ".$method." is not a valid method.");
        }

        http_response_code($this->HttpResponse->Code);
    }

    private function runJob($body){
        $id = $type = $params = null;
        if(key_exists('id',$body)){
            $id = intval($body['id']);
            $this->Context->getLogger()->debug("Job Id: ".$id);
        }else{
            throw new \Exception("Incorrect HTTP request body");
        }    
        if(key_exists('type',$body)){
            $type = $body['type'];
            $this->Context->getLogger()->debug("Job Type: ".$type);
        }else{
            throw new \Exception("Incorrect HTTP request body");
        }    
        if(key_exists('params',$body)){
            $params = $body['params'];
            $this->Context->getLogger()->debug("Job Params: ".$params);
        }else{
            throw new \Exception("Incorrect HTTP request body");
        }    

        $jobDTO = $this->createJobDTO($id, $type, $params);
        
        $jobDTO = $this->Context->getJobDTORepository()->create($jobDTO);

        // execute job
        exec('sudo /srv/ovzhost/JobSystemStarter.php normal '.intval($jobDTO->getId()).' 2<&1',$output,$exitstatus);
        
        // update jobDTO after job execution (in other process, so it has to be done manually)
        $jobDTO = $this->Context->getJobDTORepository()->get($jobDTO->getId());
        
        // check if execution of process was OK, if not set jobDTO to error state
        if($exitstatus!=0){
            $jobDTO->setDone(2);
            $jobDTO->setError("JobScript not successful exit code: ".$exitstatus." Output: ".json_encode($output));
        } 
        
        // set http response code depending on jobDTO Done attribute
        switch($jobDTO->getDone()){
            // executed normally
            case 1:
                $this->HttpResponse->Code = 200;
                $this->Context->getLogger()->debug("Job succeeded, HTTP response code: ".$this->HttpResponse->Code);
                break;
            // executed and is in at the moment in background running
            case -1:
                $this->HttpResponse->Code = 200;
                $this->Context->getLogger()->debug("Job running in background, HTTP response code: ".$this->HttpResponse->Code);
                break;
            // executed with error
            case 2:
                $this->HttpResponse->Code = 200;
                $this->Context->getLogger()->debug("Job failed, HTTP response code: ".$this->HttpResponse->Code);
                break;
            // executed with critical error
            case 3:
                $this->HttpResponse->Code = 200;
                $this->Context->getLogger()->debug("Job failed critical, HTTP response code: ".$this->HttpResponse->Code);
                break;
            // not yet executed
            case 0:
                $this->HttpResponse->Code = 200;
                $this->Context->getLogger()->debug("Job not yet executed, HTTP response code: ".$this->HttpResponse->Code);
                break;
            default:
                $this->HttpResponse->Code = 500;
                $this->Context->getLogger()->debug("Could not get done attribute of job, HTTP response code: ".$this->HttpResponse->Code);
                break;
        }

        // HTTP Response setzen
        $this->HttpResponse->Body = $jobDTO->toArray();
    }

    /**
    * @param int $id
    * @param string $type
    * @param string $params
    * @return JobDTO
    */
    private function createJobDTO($id, $type, $jsonParams){
        $jobDTO = new JobDTO();
        $jobDTO->setId($id);
        $jobDTO->setJsonParams($jsonParams);
        $jobDTO->setType($type);
        return $jobDTO;    
    }

    /**
    * @param array $uriParts
    */
    private function getJob($body){
        $id = null;
        if(key_exists('id',$body)){
            $id = intval($body['id']);
            $this->Context->getLogger()->debug("Job Id: ".$id);
        }else{
            throw new \Exception("Incorrect HTTP request body");
        }    

        $jobDTO = $this->Context->getJobDTORepository()->get($id);
        $this->HttpResponse->Code = 200;
        $this->HttpResponse->Body = $jobDTO->toArray();
    }
    
    private function delJob($body){
        $id = null;
        if(key_exists('id',$body)){
            $id = intval($body['id']);
            $this->Context->getLogger()->debug("Job Id: ".$id);
        }else{
            throw new \Exception("Incorrect HTTP request body");
        }    

        $this->Context->getJobDTORepository()->deleteById($id);        

        $this->HttpResponse->Code = 200;
        $this->HttpResponse->Body = "Job deleted from DB";
    }

    private function handleGetLogs($body){
        return "not implemented";
    }

    /**
    * @throws \Exception
    */
    private function getDecryptRequestBody(){
        $plainBody = $this->HttpBody;
     
        $plainBody = JWT::decode($this->HttpBody,SYSTEMWIDE_SECRETKEY);
        $this->Context->getLogger()->debug("Decrypted Input: ".json_encode($plainBody)); 
     
        return (array)$plainBody;
    }

    /**
    * @throws \Exception
    */
    private function getEncryptedResponseBody(){
        $encryptedResponse = '';
        
        $encryptedResponse = JWT::encode($this->HttpResponse->Body,SYSTEMWIDE_SECRETKEY);
        $this->Context->getLogger()->debug("Encrypted HttpResponseBody: ".$encryptedResponse);
        
        return $encryptedResponse;
    }
}
