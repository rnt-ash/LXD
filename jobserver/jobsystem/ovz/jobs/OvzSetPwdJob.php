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

class OvzSetPwdJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_set_pwd",
            "description" => "set the root password of a VirtualServer",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)",
                "ROOTPWD" => "the root password to set"
            ],
            "params_example" => '{"UUID":"717a8925-f92b-48d3-81aa-a948cfe177af, "ROOTPWD":"v3rys4fep4s5w0rd!"}',
            "retval" => "nothing specified, maybe some output from the CLI",
            "warning" => "nothing specified",
            "error" => "different causes (UUID does not exist, or something while effectively setting root password to the VS fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("VS set root password!");

        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }

        $exitstatus = $this->PrlctlCommands->setRootPwd($this->Params['UUID'],$this->Params['ROOTPWD']);
        if($exitstatus == 0){
            $this->commandSuccess("Setting root password done.");
        }else{
            if($exitstatus > 0) return $this->commandFailed("Setting root password failed",$exitstatus);
        }
    }
}
