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

class LxdDeleteCtJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_delete_ct",
            "description" => "delete a container",
            "params" => [
                "NAME" => "Name of the container"
            ],
            "params_example" => '{"NAME":"my-container"}',
            "retval" => "nothing specified, maybe some output from the CLI",
            "warning" => "nothing specified",
            "error" => "different causes (Name already exists, or something while effectively deleting the CT fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("CT delete!");
        
        // execute API command to delete CT
        $exitstatus = $this->lxdApiExecCommand('DELETE','a/1.0/containers/'.$this->Params['NAME']);
        
        // check if operation is created and executed successfully
        $this->lxdApiCheckOperation('Deleting CT successful');
    }
}
