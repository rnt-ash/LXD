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

class LxdGetSettingsJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_get_settings",
            "description" => "get settings of a container",
            "params" => [
                "NAME" => "Name of the container"
            ],
            "params_example" => '{"NAME":"my-container"}',
            "retval" => "CT settings as JSON",
            "warning" => "nothing specified",
            "error" => "different causes (CT doesn't exist, or something while effectively getting the CT setting fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("CT get settings!");
        
        // execute API command to get the settings of the CT
        $exitstatus = $this->lxdApiExecCommand('GET','a/1.0/containers/'.$this->Params['NAME']);
        $output = json_decode($this->Context->getCli()->getOutput()[0],true);
        if($output['status_code'] == 200){
            $this->Done = 1;
            $this->Retval = $this->Context->getCli()->getOutput()[0];
            $this->Context->getLogger()->debug('Getting CT settings successful');
        }else{
            $this->commandFailed($output['error'],$exitstatus);
        }
    }
}
