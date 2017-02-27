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
* @jobname ovz_stop_vs
* 
* @jobparam UUID
*/

class OvzStopVsJob extends AbstractOvzJob {

    public static function usage(){
        return null;
    }
    
    public function run() {
        $this->Context->getLogger()->debug("VS stop!");
        
        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }

        $exitstatus = $this->PrlctlCommands->status($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting status failed",$exitstatus);
        
        if($this->PrlctlCommands->getStatus()['EXIST'] && $this->PrlctlCommands->getStatus()['RUNNING']){
            $exitstatus = $this->PrlctlCommands->stop($this->Params['UUID']);
            if($exitstatus == 0){
                $this->commandSuccess("VS stop done.");
            }else{
                $this->commandFailed("Stopping VS failed",$exitstatus);            }
        } else {
            $this->Context->getLogger()->debug("Wrong VS status. Nothing to do...".str_replace("\n","; ",json_encode($this->PrlctlCommands->getStatus())));
        }
    }
}
