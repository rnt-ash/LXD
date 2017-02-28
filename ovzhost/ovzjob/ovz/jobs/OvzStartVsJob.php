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

namespace RNTFOREST\OVZJOB\ovz\jobs;

class OvzStartVsJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_start_vs",
            "description" => "start a VirtualServer",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)"
            ],
            "params_example" => '{"UUID":"717a8925-f92b-48d3-81aa-a948cfe177af"}',
            "retval" => "nothing specified, maybe some output from the CLI",
            "warning" => "nothing specified",
            "error" => "different causes (UUID does not exist, couldn't get actual state, or something while effectively starting the VS fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("VS start!");
        
        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }

        $exitstatus = $this->PrlctlCommands->status($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting status failed",$exitstatus);
        
        if($this->PrlctlCommands->getStatus()['EXIST'] && !$this->PrlctlCommands->getStatus()['RUNNING']){
            $exitstatus = $this->PrlctlCommands->start($this->Params['UUID']);
            if($exitstatus == 0){
                $this->commandSuccess("VS start done.");
            }else{
                if($exitstatus > 0) return $this->commandFailed("Starting VS failed",$exitstatus);
            }
        } else {
            $this->Context->getLogger()->debug("Wrong VS status. Nothing to do...".str_replace("\n","; ",json_encode($this->PrlctlCommands->getStatus())));
        }
    }
}
