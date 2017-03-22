<?php
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
