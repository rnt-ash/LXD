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

class LxdAssignIpJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_assign_ip",
            "description" => "Assign an IPv4 address to a CT",
            "params" => [
                "NAME" => "Name of the container",
                "CONFIG" => "IP configuration (IP address, gateway, netmask, nameservers)"
            ],
            "params_example" => '{"NAME":"my-container","CONFIG":"Auto generated config"}',
            "retval" => "nothing specified",
            "warning" => "nothing specified",
            "error" => "different causes (CT doesn't exist, or something while effectively assigning the IP address to the CT fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Assign IP address to CT!");
        
        // create interfaces file with config in it in temp file on host
        $exitstatus = $this->Cli->execute('echo "'.$this->Params['CONFIG'].'" > /tmp/interfaces-'.$this->Params['NAME']);
        if($exitstatus > 0) return $this->commandFailed("Creating temporary interfaces file on host failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput()),$exitstatus);
        
        // push the tmp file to the network folder in the CT
        $exitstatus = $this->Cli->execute('lxc file push /tmp/interfaces-'.$this->Params['NAME'].' '.$this->Params['NAME'].'/etc/network/interfaces');
        if($exitstatus > 0) return $this->commandFailed("Pushing the interfaces file to the CT failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput()),$exitstatus);
        
        // restart the CT via API
        $this->lxdApiExecCommand('PUT','a/1.0/containers/'.$this->Params['NAME'].'/state','{"action": "restart"}');
        // check if operation is created and executed successfully
        $this->lxdApiCheckOperation('Restarted CT after assigning IP successfully');
        
        // remove the temp file
        $exitstatus = $this->Cli->execute('rm /tmp/interfaces-'.$this->Params['NAME']);
        if($exitstatus > 0) return $this->commandFailed("Temporary interfaces file on the host couldn't be removed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput()),$exitstatus);
        
        // if everything went through, job is fine
        $this->Done = 1;    
        $this->Retval = '';
        $this->Context->getLogger()->debug('IP address was successfully assigned');
    }
}
