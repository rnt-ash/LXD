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

class OvzHostInfoJob extends AbstractOvzJob {

    public function usage(){
        return null;
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Get host info!");
        
        $exitstatus = $this->PrlsrvctlCommands->hostInfo();
        if($exitstatus > 0) return $this->commandFailed("Getting host info failed",$exitstatus);
        
        $json = $this->PrlsrvctlCommands->getJson();
        if(!empty($json)){
            $this->Done = 1;    
            $this->Retval = $json;
            $this->Context->getLogger()->debug("Get host info Success.");
        }else{
            $this->Done = 2;
            $this->Error = "Convert host info to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
        }
    }
}
