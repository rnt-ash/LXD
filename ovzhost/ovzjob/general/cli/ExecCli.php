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

namespace RNTForest\OVZJOB\general\cli;

use RNTForest\OVZJOB\general\psrlogger\LoggerInterface;

class ExecCli implements CliInterface {
    
    /**
    * @var LoggerInterface $Logger
    */
    private $Logger;
    
    /**
    * @var array $Output
    */
    private $Output = array();
    
    /**
    * @param LoggerInterface $logger
    */
    public function __construct(LoggerInterface $logger){
        $this->Logger = $logger;
    }

    /**
    * getter
    *     
    */
    public function getOutput(){
        return $this->Output;
    }

    
/*
            $fullCommand = "ssh ".$host." 'vzctl exec ".intval($ctid)." ".escapeshellarg($command)." '";
            exec($fullCommand,$returnvalue,$exitstatus);
*/  
    
    
    /**
    * Execute a Shell Command.
    * suffix '2>&1' is added by method.
    * 
    * @param string $command Input Parameter
    * @param array $output Output Parameter
    * @param int $exitstatus Output Parameter
    * @return int ExitStatus
    */
    public function execute($command,$host=''){
        try{
            if(empty($command)){
                throw new \Exception("Command cannot be empty.");
            }

            if(!empty($host)){
                $command = "ssh -o StrictHostKeyChecking=no ".$host." ".escapeshellarg($command)." 2>&1";
            }else{
                $command = $command." 2>&1";
            }
            $this->Logger->debug("ExecCli: ".$command);
            
            // clear output
            $this->Output = array();
            $exitstatus = 0;
            
            exec($command,$this->Output,$exitstatus);

        }catch(\Exception $e){
            $exitstatus = 253;
            $output[] = $e->getMessage();
            $this->Logger->error("ExecCli Exception catched: ".$e->getMessage());
        }
        return $exitstatus;
    }
    
        
    /**
    * Execute a Shell Command in background.
    * prefix 'nohup' and suffix '2>&1 &' is set by method.
    * 
    * @param string $command Input Parameter
    * @return int ExitStatus
    */
    public function executeBackground($command,$host=''){
        try{
            if(empty($command)){
                throw new \Exception("Command cannot be empty.");
            }

            if(!empty($host)){
                $command = "nohup ssh -o StrictHostKeyChecking=no ".$host." ".escapeshellarg($command)." 2>&1";
            }else{
                $command = "nohup ".$command." 2>&1 &";
            }
            $this->Logger->debug("ExecCli: ".$command);
            
            // clear output
            $this->Output = array();
            $exitstatus = 0;
            
            exec($command,$this->Output,$exitstatus);

        }catch(\Exception $e){
            $exitstatus = 254;
            $output[] = $e->getMessage();
            $this->Logger->error("ExecCli Exception catched: ".$e->getMessage());
        }
        return $exitstatus;
    }
}
