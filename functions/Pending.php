<?php
namespace RNTForest\ovz\functions;

use \RNTForest\ovz\models\VirtualServers;

class Pending{

    /**
    * update the ovz_replica_lastrun field
    * 
    * @param array $pendingArray
    * @throws \Exceptions
    */
    public static function updateReplicaLastrun($pendingArray){
        $replicaMaster = VirtualServers::tryFindById($pendingArray['id']);
        $replicaMaster->setOvzReplicaLastrun(date("Y-m-d H:i:s")); //format: 0000-00-00 00:00:00
        if(!$replicaMaster->update()) throw new \Exception("Replica Master update failed");
    }


}
?>
