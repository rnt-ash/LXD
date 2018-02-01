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

class LxdStartCtJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_start_ct",
            "description" => "start a container",
            "params" => [
                "NAME" => "Name of the container"
            ],
            "params_example" => '{"NAME":"container1"}',
            "retval" => "nothing specified, maybe some output from the CLI",
            "warning" => "nothing specified",
            "error" => "different causes (UUID does not exist, couldn't get actual state, or something while effectively starting the VS fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("CT start!");
        
        $exitstatus = $this->Cli->execute('curl -s --unix-socket /var/lib/lxd/unix.socket -X PUT -d \'{"action": "start"}\' a/1.0/containers/test/state');
        
        if($exitstatus == 0){
            $this->Done = 1;    
            $this->Retval = implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($message);
        }else{
            $this->Done = 2;
            $this->Error = $message." Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->error($this->Error);
        }
    }
}
