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

class LxdCreateCtJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_create_ct",
            "description" => "create a new container",
            "params" => [
                "NAME" => "Name of the container",
                "CPUS" => "Number of CPU Cores",
                "RAM" => "Memory",
                "DISKSPACE" => "Diskspace in GB",
                "STORAGEPOOL" => "Pool of which to take the diskspace",
                "IMAGEALIAS" => "Assigned alias of the image for the CT"
            ],
            "params_example" => '{"NAME":"newContainer","CPUS":"4","RAM":"4GB","DISKSPACE":"100GB","STORAGEPOOL":"zfs_pool","IMAGEALIAS":"ubuntu 16.04"}',
            "retval" => "JSON with settings of the container",
            "warning" => "nothing specified",
            "error" => "different causes (Name already exists, or something while effectively creating the CT fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("CT create!");
        
        // execute API command to create new CT
        $exitstatus = $this->lxdApiExecCommand('POST','a/1.0/containers','{"name": "'.$this->Params['NAME'].'","config" : {"limits.cpu": "'.$this->Params['CPUS'].'", "limits.memory": "'.$this->Params['RAM'].'"}, "devices": {"root": {"path": "/", "pool": "'.$this->Params['STORAGEPOOL'].'", "size": "'.$this->Params['DISKSPACE'].'", "type": "disk"}}, "source": {"type": "image", "alias": "'.$this->Params['IMAGEALIAS'].'"}}');
        
        // check if operation is created and executed successfully
        $this->lxdApiCheckOperation('Created CT successful');
        
        // if creating was successful, start the CT put the settings in the retval
        if($this->Done == 1){
            // start container
            $exitstatus = $this->lxdApiExecCommand('PUT','a/1.0/containers/'.$this->Params['NAME'].'/state','{"action": "start"}');
            $this->lxdApiCheckOperation('Started the CT successfully');
            
            // get settings
            $this->lxdApiExecCommand('GET','a/1.0/containers/'.$this->Params['NAME']);
            $this->Retval = $this->Context->getCli()->getOutput()[0];
        }
    }
}
