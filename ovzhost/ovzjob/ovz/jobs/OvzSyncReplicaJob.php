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

namespace RNTForest\OVZJOB\ovz\jobs;

class OvzSyncReplicaJob extends AbstractOvzJob{

    public static function usage(){
        return [
            "type" => "ovz_sync_replica",
            "description" => "runs a replica sync",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)",
                "SLAVEHOSTFQDN" => "FQDN of the replica slave host",
                "SLAVEUUID" => "Universally Unique Identifier (UUID) of the replica slave"
            ],
            "params_example" => '{"UUID":"717a8925-f92b-48d3-81aa-a948cfe177af","SLAVEHOSTFQDN":"test.domain.tld","SLAVEUUID":"827a8925-f92b-48d3-81aa-a948cfe147fa"}',
            "retval" => "nothing specified",
            "warning" => "nothing specified",
            "error" => "different causes (couldn't get VS list, UUID already exists, no available OSTEMPLATE, or something while effectively creating the VS fails)",
        ];
    }

    public function run(){
        $this->Context->getLogger()->debug("Sync Replica!");
        
        // check if master exists
        if(!$this->vsExists($this->Params['UUID'])){
            return $this->commandFailed("Replica master with UUID ".$this->Params['UUID']." does not exist!",9);
        }

        // check if sync is already running
        $exitstatus = $this->PrlctlCommands->listSnapshots($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting snapshots failed",$exitstatus);
        $snapshots = json_decode($this->PrlctlCommands->getJson(),true);
        foreach($snapshots as $snapshot){
            if(substr($snapshot['Name'],0,12) == "TEMP REPLICA")
                return $this->commandFailed("Already exist an 'REPLICA' snapshot. Sync may not be running!",255);
        }

        // check if slave exists
        if(!$this->vsExists($this->Params['SLAVEUUID'],$this->Params['SLAVEHOSTFQDN'])){
            return $this->commandFailed("Replica slave with UUID ".$this->Params['SLAVEUUID']." does not exist on server ".$this->Params['SLAVEHOSTFQDN']." !",8);
        }

        // check if mounted or running            
        $exitstatus = $this->PrlctlCommands->status($this->Params['SLAVEUUID'],$this->Params['SLAVEHOSTFQDN']);
        if($exitstatus > 0) return $this->commandFailed("Getting status failed",$exitstatus);
        if($this->PrlctlCommands->getStatus()['EXIST'] && $this->PrlctlCommands->getStatus()['MOUNTED']){
            return $this->commandFailed("Replica slave may not be mounted!",9);
        }
        if($this->PrlctlCommands->getStatus()['EXIST'] && $this->PrlctlCommands->getStatus()['RUNNING']){
            return $this->commandFailed("Replica slave may not be started!",9);
        }

        // start background job
        $this->Context->getLogger()->debug("Starting background job");
        $cmd = "php JobSystemStarter.php background ".intval($this->Id)." > /dev/null";
        $exitstatus = $this->Context->getCli()->executeBackground($cmd);
        if($exitstatus > 0) return $this->commandFailed("Starting backgroundjob failed",$exitstatus);

        $this->Done = -1;
    }
}