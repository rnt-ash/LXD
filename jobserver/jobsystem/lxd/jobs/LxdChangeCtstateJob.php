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

class LxdChangeCtstateJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_change_ctstate",
            "description" => "Change the state of a container",
            "params" => [
                "NAME" => "Name of the container",
                "ACTION" => "Action to do (start, stop, restart)"
            ],
            "params_example" => '{"NAME":"my-container","ACTION":"start"}',
            "retval" => "Current settings of the CT",
            "warning" => "nothing specified",
            "error" => "different causes (CT doesn't exist, Action doesn't exist, or something while effectively changing the state of the CT fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("CT change state!");
        
        // execute API command to start/stop/restart the CT
        $exitstatus = $this->lxdApiExecCommand('PUT','a/1.0/containers/'.$this->Params['NAME'].'/state','{"action": "'.$this->Params['ACTION'].'"}');
        
        // check if operation is created and executed successfully
        $this->lxdApiCheckOperation('Changing state of the CT successful');
        
        // if execution was successful, put the settings in the retval
        if($this->Done == 1){
            $this->lxdApiExecCommand('GET','a/1.0/containers/'.$this->Params['NAME']);
            $this->Retval = $this->Context->getCli()->getOutput()[0];
        }
    }
}
