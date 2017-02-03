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
* @jobname ovz_list_info
* 
* @jobparam UUID
* @jobreturn JSON Array with infos
*/

class OvzListInfoJob extends AbstractOvzJob {

    public function run() {
        $this->Context->getLogger()->debug("Get info!");

        if(!$this->vsExists($this->Params['UUID'])) return 9;
        
        $exitstatus = $this->PrlctlCommands->listInfo($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting info failed",$exitstatus);
        
        $array = json_decode($this->PrlctlCommands->getJson(),true);
        if(is_array($array) && !empty($array)){
            $this->Done = 1;    
            $this->Retval = json_encode($array[0]);
            $this->Context->getLogger()->debug("Get info success.");
        }else{
            $this->Done = 2;
            $this->Error = "Convert info to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
        }
    }
}
