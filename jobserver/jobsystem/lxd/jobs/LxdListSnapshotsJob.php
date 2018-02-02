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

namespace RNTForest\jobsystem\lxd\jobs;

class LxdListSnapshotsJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_list_snapshots",
            "description" => "List snapshots of a container",
            "params" => [
                "NAME" => "Name of the container",
            ],
            "params_example" => '{"NAME":"my-container"}',
            "retval" => "snapshots as JSON",
            "warning" => "nothing specified",
            "error" => "different causes (CT doesn't exist, or something while effectively getting the snapshots fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("List snapshot!");
        
        // call separate method to list all snapshots
        return $this->lxdListSnapshots($this->Params['NAME']);
    }
}
