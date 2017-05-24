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

class OvzSupportTaskJob extends AbstractOvzJob{

    public static function usage(){
        return [
            "type" => "ovz_support_task",
            "description" => "does a specific support task, has to be implemented if needed...",
            "params" => [
            ],
            "params_example" => '',
            "retval" => "",
            "warning" => "",
            "error" => "",
        ];
    }


    public function run() {
        $this->Context->getLogger()->debug("Do Support Job!");

        try{
            // place your code here
            
        } catch(\Exception $e) {
            // error
            $this->Done = 2;
            $this->Error = "Support job failed: ".$e->getMessage();
            $this->Context->getLogger()->debug($this->Error);
            return 1;
        }

        // everything ok
        $this->Done = 1;    
        $this->Context->getLogger()->debug("Support job success.");
        return 0;
    }
}