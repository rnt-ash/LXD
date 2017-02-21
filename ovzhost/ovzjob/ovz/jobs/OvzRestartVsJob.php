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
* @jobname ovz_restart_vs
* 
* @jobparam UUID
*/

class OvzRestartVsJob extends AbstractOvzJob {

    public function run() {
        $this->Context->getLogger()->debug("VS restart!");

        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }
        
        $exitstatus = $this->PrlctlCommands->status($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting status failed",$exitstatus);
        
        if($this->PrlctlCommands->getStatus()['EXIST']){
            $exitstatus = $this->PrlctlCommands->restart($this->Params['UUID']);
            if($exitstatus == 0){
                $this->commandSuccess("VS restart done.");
            }else{
                $this->commandFailed("Restarting VS failed",$exitstatus);
            }
        } else {
            $this->Context->getLogger()->debug("VS not Exist. Nothing to do...");
        }
    }
}

