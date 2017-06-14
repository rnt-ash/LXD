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

namespace RNTForest\jobsystem\ovz\jobs;

class OvzPoolUpdateJob extends AbstractOvzJob{

    public static function usage(){
        return [
            "type" => "ovz_pool_update",
            "description" => "update the jobsystem from OVZ pool",
            "params" => [
            ],
            "params_example" => '',
            "retval" => "",
            "warning" => "",
            "error" => "",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Update pool!");
        
        // start background job
        $this->Context->getLogger()->debug("Starting background job");
        $exitstatus = $this->startInBackground();
        if($exitstatus > 0) return $this->commandFailed("Starting backgroundjob failed",$exitstatus);
        
        $this->Done = -1;
    }
}
