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

/**
* @jobname ovz_list_snapshots
* 
* @jobparam UUID
* @jobreturn JSON Array with snapshots
*/

class OvzListSnapshotsJob extends AbstractOvzJob {

    public function usage(){
        return null;
    }
    
    public function run() {
        $this->Context->getLogger()->debug("List snapshots!");

        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }
        
        $exitstatus = $this->PrlctlCommands->listSnapshots($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting snapshots failed",$exitstatus);
        
        $json = $this->PrlctlCommands->getJson();
        if(!empty($json)){
            $this->Done = 1;    
            $this->Retval = $json;
            $this->Context->getLogger()->debug("Get snapshots success.");
        }else{
            $this->Done = 2;
            $this->Error = "Convert info to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
        }
    }
}
