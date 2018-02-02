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

class LxdRestoreSnapshotJob extends AbstractLxdJob {

    public static function usage(){
        return [
            "type" => "lxd_restore_snapshot",
            "description" => "Restore to a snapshot from a container",
            "params" => [
                "CTNAME" => "Name of the container",
                "SNAPSHOTNAME" => "Name of the snapshot"
            ],
            "params_example" => '{"CTNAME":"my-container","SNAPSHOTNAME":"my-snapshot"}',
            "retval" => "snapshots as JSON",
            "warning" => "nothing specified",
            "error" => "different causes (Snapshotname doesn't exists, or something while effectively restoring to the snapshot fails)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Restore to snapshot!");
        
        // execute API command to restore to the snapshot
        $exitstatus = $this->lxdApiExecCommand('PUT','a/1.0/containers/'.$this->Params['CTNAME'],'{"restore": "'.$this->Params['SNAPSHOTNAME'].'"}');
        
        // check if operation is created and executed successfully
        $this->lxdApiCheckOperation('Restored to snapshot successfully');
        
        // if execution was successful, get list of the current snapshots
        if($this->Done == 1){
            return $this->lxdListSnapshots($this->Params['CTNAME']);
        }
    }
}
