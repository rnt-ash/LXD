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

class LxdModifyCtJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_modify_ct",
            "description" => "modify the configuration of a CT",
            "params" => [
                "NAME" => "Name of the container",
                "CPUS" => "Number of CPU Cores",
                "RAM" => "Memory in MB",
                "DISKSPACE" => "Diskspace in GB",
                "STORAGEPOOL" => "Pool to which the the CT is assigned",
            ],
            "params_example" => '{"NAME":"my-container","CPUS":"4","RAM":"2048MB","DISKSPACE":"50GB"}',
            "retval" => "CT settings as JSON",
            "warning" => "nothing specified",
            "error" => "different causes (CT doesn't exist, or something while effectively modifying the CT fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("CT modify!");
        
        // execute API command to modify the CT
        $exitstatus = $this->lxdApiExecCommand('PATCH','a/1.0/containers/'.$this->Params['NAME'],'{"config": {"limits.cpu": "'.$this->Params['CPUS'].'", "limits.memory": "'.$this->Params['RAM'].'MB"}, "devices": {"root": {"size": "'.$this->Params['DISKSPACE'].'GB","type": "disk", "path": "/", "pool": "'.$this->Params['STORAGEPOOL'].'"}}}');
        $output = json_decode($this->Context->getCli()->getOutput()[0],true);
        if($output['status_code'] == 200){
            // if modifying was successful, put the settings in the retval
            $this->lxdApiExecCommand('GET','a/1.0/containers/'.$this->Params['NAME']);
            $this->Done = 1;
            $this->Retval = $this->Context->getCli()->getOutput()[0];
            $this->Context->getLogger()->debug('Modified CT successfully');
        }else{
            $this->commandFailed($output['error'],$exitstatus);
        }
    }
}
