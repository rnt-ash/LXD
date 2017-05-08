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

namespace RNTForest\jobsystem\ovz\jobs;

class OvzModifyVsJob extends AbstractOvzJob{

    public static function usage(){
        return [
            "type" => "ovz_modify_vs",
            "description" => "modify the configuration of a VirtualServer",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)",
                "CONFIG" => [
                    "name" => "name of the system (string)",
                    "hostname" => "hostname of the system (string)",
                    "cpus" => "number of cpu cores (int)",
                    "memsize" => "amount of ram in mb (int)",
                    "diskspace" => "amount of diskspace in gb (int)",
                    "onboot" => "if it should be started automatically on boot of the host(string <yes|no>)",
                    "nameserver" => "define the nameserver/s of the system in ip-address format (string 0-unlimited whitespace separated)",
                    "description" => "description of the virtual system (string)"
                ]
            ],
            "params_example" => '{"UUID":"717a8925-f92b-48d3-81aa-a948cfe177af","CONFIG":{"hostname":"server.domain.tld","cpus":"4","memsize":"1024","diskspace":"10240","onboot":"yes","nameserver":"8.8.8.8 123.123.123.123","description":""}}',
            "retval" => "JSON string with settings of the new VS",
            "warning" => "nothing specified",
            "error" => "different causes (UUID does not exist, ...)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Modify VS!");
        
        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }
        
        $config = $this->Params['CONFIG'];
        $errors = array();
        foreach($config as $key=>$value){
            $exitstatus = $this->PrlctlCommands->setValue($this->Params['UUID'],$key,$value);
            if($exitstatus > 0){
                $errors[] = "Setting of '".$key."' failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput());
                $this->Context->getLogger()->debug($this->Error);
            }
        }
        if(!empty($errors)) {
            $this->Done = 2;
            $this->Error = implode("\n",$errors);
            $this->Context->getLogger()->debug($this->Error);
            return 255;
        }

        $exitstatus = $this->PrlctlCommands->listInfo($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting info failed",$exitstatus);

        $array = json_decode($this->PrlctlCommands->getJson(),true);
        if(is_array($array) && !empty($array)){
            $array[0]['Timestamp'] = date('Y-m-d H:i:s');
            $this->Done = 1;    
            $this->Retval = json_encode($array[0]);
            $this->Context->getLogger()->debug("modify VS success.");
            return 0;
        }else{
            $this->Done = 2;
            $this->Error = "Convert info to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
            return 1;
        }
    }
}
