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

class OvzMigrateVsJob extends AbstractOvzJob{
    
    public static function usage(){
        return [
            "type" => "ovz_migrate_vs",
            "description" => "Migrates a virtual machine from one server to another",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)",
                "DESTINATION" => "The destination server information. (fqdn)",
                "NAME" => "(optional) New name of the migrated system, not functional",
                "DST" => "(optional) Name and path of the directory on the destination server where the virtual machine files should be stored.",
                "CLONE" => "(optional) Do not remove the original VS from the source server.",
                "NOCOMPRESSION" => "(optional) Disable data compression during migration.",
            ],
            "params_example" => '"UUID":"717a8925-f92b-48d3-81aa-a948cfe177af","DESTINATION":"host19.mydomain.com"',
            "retval" => "Info in JSON format of the migrated VS",
            "warning" => "nothing specified",
            "error" => "different causes (...)",
        ];
    }

    public function run() {
        $this->Context->getLogger()->debug("VS migrate!");

        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }

        // start background job
        $this->Context->getLogger()->debug("Starting background job");
        $cmd = "php JobSystemStarter.php background ".intval($this->Id)." > /dev/null";
        $exitstatus = $this->Context->getCli()->executeBackground($cmd);
        if($exitstatus > 0) return $this->commandFailed("Starting backgroundjob failed",$exitstatus);
        
        $this->Done = -1;
    }
}
