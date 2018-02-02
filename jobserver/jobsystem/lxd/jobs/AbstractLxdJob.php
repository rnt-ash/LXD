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

namespace RNTForest\jobsystem\lxd\jobs;

use RNTForest\jobsystem\general\jobs\AbstractJob;
use RNTForest\jobsystem\general\utility\Context;

abstract class AbstractLxdJob extends AbstractJob{
    public function __construct(Context $context) {
        parent::__construct($context);
    }
    
    /**
    * Execute an API command
    * 
    * @param mixed $requestMethod GET, POST, PUT, PATCH, DELETE
    * @param mixed $url the URL to call via curl
    * @param mixed $data the data to pass to the URL as JSON
    */
    protected function lxdApiExecCommand($requestMethod,$url,$data=""){
        return $this->Cli->execute('curl -s --unix-socket /var/lib/lxd/unix.socket -X '.$requestMethod.' -d \''.$data.'\' '.$url);
    }
    
    protected function lxdApiCheckOperation($successMessage){
        // check if operation could be created
        $output = json_decode($this->Context->getCli()->getOutput()[0],true);
        if($output['status_code'] == 100){
            // wait until operation has finished
            $exitstatus = $this->lxdApiExecCommand('GET','a'.$output['operation'].'/wait');
            
            // check if operation was successful
            unset($output);
            $output = json_decode($this->Context->getCli()->getOutput()[0],true);
            if($output['metadata']['status_code'] == '200'){
                $this->Done = 1;
                $this->Retval = $this->Context->getCli()->getOutput()[0];
                $this->Context->getLogger()->debug($successMessage);
            }else{
                $this->commandFailed($output['metadata']['err'],$exitstatus);
            }
        }else{
            $this->commandFailed($output['error'],$exitstatus);
        }
    }
    
    protected function lxdListSnapshots($ctName){
        // get all snapshots of this CT
        $this->lxdApiExecCommand('GET','a/1.0/containers/'.$ctName.'/snapshots');
        $output = json_decode($this->Context->getCli()->getOutput()[0],true);
        if($output['status_code'] == 200){
            // go through all snapshots
            $snapshotList = array();
            foreach($output['metadata'] as $snapshot){
                // get more details
                $this->lxdApiExecCommand('GET','a'.$snapshot);
                $output = json_decode($this->Context->getCli()->getOutput()[0],true);
                
                // save name and creation date to array
                if($output['status_code'] == 200){
                    // get only name of the snapshot (without CT name)
                    $name = substr($output['metadata']['name'],strpos($output['metadata']['name'],'/')+1);
                    
                    // save snapshot to array
                    $snapshotList[$name]['created_at'] = $output['metadata']['created_at'];
                }else{
                    return $this->commandFailed("Getting Snapshot details failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput()),$exitstatus);
                }
            }
            
            $this->Done = 1;
            $this->Retval = json_encode($snapshotList);
            $this->Context->getLogger()->debug('Listing snapshot successul');
        }else{
            return $this->commandFailed("Listing snapshots failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput()),$exitstatus);
        }
    }

    /**
    * helper method
    *     
    * @param mixed $message
    */
    protected function commandSuccess($message){
        $this->Done = 1;    
        $this->Retval = $this->Context->getCli()->getOutput();
        $this->Context->getLogger()->debug($message);
    }

    /**
    * helper method
    * 
    * @param string $message
    */
    protected function commandFailed($message,$exitstatus){
        $this->Done = 2;
        $this->Error = $message;
        $this->Context->getLogger()->error($this->Error);
        return $exitstatus;
    }
}
