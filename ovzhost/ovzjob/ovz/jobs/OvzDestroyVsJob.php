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

class OvzDestroyVsJob extends AbstractOvzJob {

    public function run() {
        $this->Context->getLogger()->debug("VS destroy!");

        if($this->vsExists($this->Params['UUID'])){
            $exitstatus = $this->PrlctlCommands->status($this->Params['UUID']);
            if($exitstatus > 0) return $this->commandFailed("Getting status failed",$exitstatus);
        
            if($this->PrlctlCommands->getStatus()['RUNNING']){
                // kill VS
                $this->Context->getLogger()->debug("Kill VS");
                $vsType = $this->PrlctlCommands->getStatus()['VSTYPE'];
                $exitstatus = $this->PrlctlCommands->killVs($this->Params['UUID'],$vsType);
                if($exitstatus > 0) return $this->commandFailed("Killing VS failed",$exitstatus);
            }elseif($this->PrlctlCommands->getStatus()['MOUNTED']){
                // Unmount VS
                $this->Context->getLogger()->debug("Unmount VS");
                $exitstatus = $this->PrlctlCommands->umountVs($this->Params['UUID']);
                if($exitstatus > 0) return $this->commandFailed("Unmounting VS failed",$exitstatus);
            }
            // Delete VS
            $this->Context->getLogger()->debug("Delete VS");
            $exitstatus = $this->PrlctlCommands->deleteVs($this->Params['UUID']);
            if($exitstatus > 0) return $this->commandFailed("Deleting VS failed",$exitstatus);
            
            $this->Done = 1;
            $this->Context->getLogger()->debug("Destroy VS done.");
        }else{
            // if the container does not exist on this host the job is still successfull
            // because if a container is deleted manually in the shell and afterwards deleted on the panel
            // which starts a job, the job should be successfull if the container doesn't exist anymore on the ovz host.
            $this->Done = 1;
            $this->Error = "";
            $this->Retval = "VS does not exist so it cannot be destroyed.";
            $this->Context->getLogger()->debug($this->Retval);
        }
    }
}
