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
namespace RNTForest\ovz\functions;

use \RNTForest\ovz\models\VirtualServers;

class Pending{

    /**
    * update the ovz_replica_lastrun field
    * 
    * @param array $pendingArray
    * @param \RNTForest\core\models\Jobs $job
    * @throws \Exceptions
    */
    public static function updateAfterReplicaRun($pendingArray,$job){
        $replicaMaster = VirtualServers::tryFindById($pendingArray['id']);

        if($job->getDone() == 1){
            $replicaMaster->setOvzReplicaLastrun(date("Y-m-d H:i:s")); //format: 0000-00-00 00:00:00
            $replicaMaster->setOvzReplicaStatus(1);
            $replicaMaster->ovzReplicaID->setOvzReplicaLastrun(date("Y-m-d H:i:s")); //format: 0000-00-00 00:00:00
            $replicaMaster->ovzReplicaID->setOvzReplicaStatus(1);
        }else{
            $replicaMaster->setOvzReplicaStatus(9);
            $replicaMaster->ovzReplicaID->setOvzReplicaStatus(9);
        }

        if(!$replicaMaster->update()) throw new \Exception("Replica Master update failed");
        if(!$replicaMaster->ovzReplicaID->update()) throw new \Exception("Replica Slave update failed");
    }

}
?>
