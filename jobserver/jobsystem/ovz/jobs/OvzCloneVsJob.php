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

class OvzCloneVsJob extends AbstractOvzJob{
    
    public static function usage(){
        return [
            "type" => "ovz_clone_vs",
            "description" => "clone a VirtualServer",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)",
                "NAME" => "New name of the cloned system, not functional",
                "TEMPLATE" => "Create a virtual machine template instead of a real virtual machine. Templates are used as a basis for creating new virtual machines. (true/false)",
                "DST" => "Full path to the directory where the new virtual machine will be stored. If this option is omitted, the new virtual machine will be created in the default directory.",
            ],
            "params_example" => '"UUID":"717a8925-f92b-48d3-81aa-a948cfe177af","NAME":"My new cloned VS","TEMPLATE":true,"DST":"/vz/private/clone"',
            "retval" => "Info in JSON format of the cloned VS",
            "warning" => "nothing specified",
            "error" => "different causes (...)",
        ];
    }

    public function run() {
        $this->Context->getLogger()->debug("VS clone!");

        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }

        // start background job
        $this->Context->getLogger()->debug("Starting background job");
        $exitstatus = $this->startInBackground();
        if($exitstatus > 0) return $this->commandFailed("Starting backgroundjob failed",$exitstatus);
        
        $this->Done = -1;
    }
}
