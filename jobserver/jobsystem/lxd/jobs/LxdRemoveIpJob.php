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

class LxdRemoveIpJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_remove_ip",
            "description" => "Remove the IP address from the CT",
            "params" => [
                "NAME" => "Name of the container",
            ],
            "params_example" => '{"NAME":"my-container"}',
            "retval" => "nothing specified",
            "warning" => "nothing specified",
            "error" => "different causes (CT doesn't exist, or something while effectively removing the IP address from the CT fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Remove IP address from CT!");
        
        // remove the interfaces file
        $exitstatus = $this->Cli->execute('lxc exec '.$this->Params['NAME'].' rm /etc/network/interfaces');
        if($exitstatus > 0) return $this->commandFailed("Removing the interfaces file in the CT failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput()),$exitstatus);
        
        // restart the CT
        $this->lxdApiExecCommand('PUT','a/1.0/containers/'.$this->Params['NAME'].'/state','{"action": "restart"}');
        // check if operation is created and executed successfully
        $this->lxdApiCheckOperation('Restarted CT after removing IP successfully');
        
        // if everything went through, job is fine
        $this->Done = 1;    
        $this->Retval = '';
        $this->Context->getLogger()->debug('IP address was successfully removed');
    }
}
