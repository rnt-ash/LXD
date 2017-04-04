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

class OvzGetOstemplatesJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_get_ostemplates",
            "description" => "get a JSON of all ostemplates",
            "params" => [],
            "params_example" => '',
            "retval" => 'JSON array of template objects with attributes name and lastupdate, e.g. [{"name":"ubuntu-14.04-x86_64","lastupdate":"0000-00-00 00:00:00"},{"name":"ubuntu-16.04-x86_64","lastupdate":"0000-00-00 00:00:00"}]',
            "warning" => "nothing specified",
            "error" => "different causes (getting templates failed)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Get templates!");

        $exitstatus = $this->PrlctlCommands->ostemplatesList();
        if($exitstatus > 0) return $this->commandFailed("Getting templates failed",$exitstatus);

        $this->Done = 1;    
        $this->Retval = $this->PrlctlCommands->getJson();
        $this->Context->getLogger()->debug("Get templates Success.");
    }
}

