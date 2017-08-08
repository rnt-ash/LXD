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

use RNTForest\jobsystem\general\utility\Helpers;

class OvzSyncReplicaBgJob extends AbstractOvzJob{

    public function run() {
        $this->Context->getLogger()->debug("Sync Replica background!");

        $snapshotName = "TEMP REPLICA ".date('Y-m-d H:i:s');
        $snapshotDesc = "Temporary snapshot for replica";
        $snapshotUUID = Helpers::genUuid();

        $this->Done = 1;    
        $retval = array();
        // set start flag
        $retval['start'] = date('Y-m-d H:i:s');

        try{
            // mount slave VS
            $exitstatus = $this->PrlctlCommands->mount($this->Params['SLAVEUUID'],$this->Params['SLAVEHOSTFQDN']);
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Mount of replica slave failed",$exitstatus));

            // create replica master snapshot
            $exitstatus = $this->VzctlCommands->createSnapshot($this->uuid2ctid($this->Params['UUID']),$snapshotName,$snapshotDesc,$snapshotUUID);        
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Snapshot for Replica failed",$exitstatus));

            // set flag
            $retval['start_sync'] = date('Y-m-d H:i:s');

            // create mount directory
            if(!file_exists("/vz/mnt/".$this->Params['UUID'])){
                $cmd = 'mkdir /vz/mnt/'.escapeshellarg($this->Params['UUID']);
                $exitstatus = $this->Cli->execute($cmd);
                if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Mountpoint (/vz/mnt/".$this->Params['UUID'].") could not created: MKDIR Returncode: $exitstatus",$exitstatus));
            }
                
            // mount snapshot
            $exitstatus = $this->VzctlCommands->mountSnapshot($this->uuid2ctid($this->Params['UUID']),$snapshotUUID,"/vz/mnt/".$this->Params['UUID']);        
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Mount snapshot failed. VZCTL Returncode: $exitstatus",$exitstatus));

            // Run sync (save detail log in file)
            if(!file_exists("/srv/jobsystem/log/replica/")){ 
                $cmd = 'mkdir -p /srv/jobsystem/log/replica/';
                $exitstatus = $this->Cli->execute($cmd);
                if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Logfolder (/srv/jobsystem/log/replica/) could not created: MKDIR Returncode: $exitstatus",$exitstatus));
            }

            $cmd = 'rsync -axAHS -e "ssh -o StrictHostKeyChecking=no -i /root/.ssh/id_rsa" --log-file-format="%i %n%L %l %b" --log-file="/srv/jobsystem/log/replica/'.$this->Params['UUID'].'_'.date("Y-m-d").'.log" '.
                '--stats --numeric-ids --delete --exclude /srv/backups --exclude /mnt --exclude /proc --exclude /dev --exclude /sys '.
                '/vz/mnt/'.$this->Params['UUID'].'/ root@'.$this->Params['SLAVEHOSTFQDN'].':/vz/root/'.$this->Params['SLAVEUUID'].'/';
            $exitstatus = $this->Cli->execute($cmd);
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("RSync stops with error: RSYNC returncode: $exitstatus\n\nOutput: ".implode("\n",$this->Cli->getOutput()),$exitstatus));
                
            // save whole output                
            $retval['sync_stats'] = implode("\n",$this->Cli->getOutput())."\n";

            // Processing RSync stats
            foreach($this->Cli->getOutput() as $line){
                if (strpos($line,"Number of files:")!==false) 
                    $retval['stats_numbre_of_files'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Number of files transferred:")!==false) 
                    $retval['stats_numbre_of_transfered'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Number of regular files transferred:")!==false) 
                    $retval[$groupID]['stats_numbre_of_transfered'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Number of created files:")!==false) 
                    $retval['stats_numbre_of_created'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Number of deleted files:")!==false) 
                    $retval['stats_numbre_of_deleted'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Total file size:")!==false) 
                    $retval['stats_total_file_size'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Total transferred file size:")!==false) 
                    $retval['stats_total_transferred_file_size'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Total bytes sent:")!==false) 
                    $retval['stats_total_bytes_sent'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
                if (strpos($line,"Total bytes received:")!==false) 
                    $retval['stats_total_bytes_received'] = intval(preg_replace("/[,.]/", "",substr($line,strpos($line,":")+1)));
            }

            // copy logfiles also to replicahost (no errorhandling, destination folder must exists)
            $cmd = 'rsync -az /srv/jobsystem/log/replica/'.$this->Params['UUID'].'_'.date("Y-m-d").'.log  root@'.$this->Params['SLAVEHOSTFQDN'].':/srv/jobsystem/log/replica/';
            $this->Cli->execute($cmd);
            

            // umount snapshots
            $exitstatus = $this->VzctlCommands->umountSnapshot($this->uuid2ctid($this->Params['UUID']),$snapshotUUID);        
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Unmount snapshot failed. VZCTL returncode: $exitstatus",$exitstatus));

            // delete mountdirectory
            $cmd = 'rm -rf /vz/mnt/'.$this->Params['UUID'];
            $exitstatus = $this->Cli->execute($cmd);
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Mountpoint could not deleted : RM returncode: $exitstatus\n\nOutput: ".implode("\n",$this->Cli->getOutput()),$exitstatus));

            // set flag
            $retval['end_sync'] = date('Y-m-d H:i:s');

            // delete snapshot
            $exitstatus = $this->PrlctlCommands->deleteSnapshot($this->Params['UUID'],$snapshotUUID);
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Delete snapshot failed. PRLCTL returncode: $exitstatus",$exitstatus));

            // unmount slave
            $this->PrlctlCommands->umount($this->Params['SLAVEUUID'],$this->Params['SLAVEHOSTFQDN']);
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("Unmount snapshot failed. PRLCTL returncode: $exitstatus",$exitstatus));

            // create snapshot for versioning
            $exitstatus = $this->PrlctlCommands->listInfo($this->Params['SLAVEUUID'],$this->Params['SLAVEHOSTFQDN']);
            if($exitstatus > 0) throw new \Exception("Fail",$this->commandFailed("List Info failed. VZCTL returncode: $exitstatus",$exitstatus));
            $listInfo = json_decode($this->PrlctlCommands->getJson(),true)[0];
            if(!is_array($listInfo) || !key_exists("Name", $listInfo)) throw new \Exception("Fail",$this->commandFailed("List Info failed. Does not have a Name in list info",$exitstatus));
            // take the id of the name, there should be "replica" followed by a number, like: replica1234
            $id = substr($listInfo["Name"],7);
            $snapshotname = "REPLICA ".$id." ".date('Y-m-d H:i:s');
            $snapshotdesc = "Replika-Snapshot für Backups";
            $this->PrlctlCommands->createSnapshot($this->Params['SLAVEUUID'],$snapshotname,$snapshotdesc,$this->Params['SLAVEHOSTFQDN']);
        }catch(\Exception $e){
            // Cleanup (no error dedection)
            $this->VzctlCommands->umountSnapshot($this->uuid2ctid($this->Params['UUID']),$snapshotUUID);
            exec('rm -rf /vz/mnt/'.$this->Params['UUID']);
            $this->PrlctlCommands->deleteSnapshot($this->Params['UUID'],$snapshotUUID);
            $this->PrlctlCommands->umount($this->Params['SLAVEUUID'],$this->Params['SLAVEHOSTFQDN']);
            
            // Update job with Error state
            $this->Done = 2;
            $this->Error = "Sync Replica background failed! Error:".$e->getMessage();
            $this->Context->getLogger()->debug($this->Error);
            return 1;

        }

        // set end flag
        $retval['end'] = date('Y-m-d H:i:s');
        $this->Done = 1;
        $this->Retval = json_encode($retval);
        $this->Context->getLogger()->debug("Sync Replica background done");
        return 0;
    }
}
