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

namespace RNTForest\ovz\services;

use Phalcon\DiInterface;   

use RNTForest\ovz\models\VirtualServers;   

/**
* @property \Phalcon\Logger\Adapter\File $logger
* 
* @property \RNTForest\core\services\Push $push
* @property \RNTForest\hws\services\Sync $sync
* @property \RNTForest\ovz\services\Replica $replica
* @property \RNTForest\core\libraries\Permissions $permissions
* 
*/
class Replica extends \Phalcon\DI\Injectable
{
    /**
    * @var FactoryDefault
    */
    private $di;

    public function __construct(DiInterface $di){
        $this->di = $di;
    }

    protected function translate($token,$params=array()){
        return $this->getDI()->getShared('translate')->_($token,$params);
    }

    /**
    * starts a replica sync (background job)
    * 
    * @param \RNTForest\ovz\models\VirtualServers $replicaMasterID
    * @return \RNTForest\core\models\Jobs $job
    * @throws Exceptions
    */
    public function run($replicaMaster){

        // sync already runs ?
        if($replicaMaster->getOvzReplicaStatus() == 2)
            throw new \Exception("replica_sync_already running");

        // execute ovz_list_snapshots job 
        // no pending needed because job is readonly       
        $params = array('UUID'=>$replicaMaster->getOvzUuid());
        $job = $this->push->executeJob($replicaMaster->PhysicalServers,'ovz_list_snapshots',$params);
        $message = $this->translate("virtualserver_job_listsnapshots_failed");
        if(!$job || $job->getDone()==2) throw new \Exception($message);

        // save snapshots
        $snapshots = $job->getRetval();
        $replicaMaster->setOvzSnapshots($snapshots);
        if ($replicaMaster->save() === false) {
            $message = $this->translate("virtualserver_update_failed");
            throw new \Exception($message.$replicaMaster->getName());
        }

        if(count(json_decode($snapshots)) >= 120)
            throw new \Exception("replica_max_snapshots_reached");

        // calculate next run (cron should not be empty!)
        // ToDo: composer "poliander/cron": "dev-master"
        $nextTimestamp = PHP_INT_MAX;
        $nextRun = "";
        if(!empty($replicaMaster->getOvzReplicaCron())){
            $acron = explode("\n",$replicaMaster->getOvzReplicaCron());
            foreach($acron as $cronline){
                $cronline = trim($cronline);
                $cron = new Cron($cronline,new DateTimeZone('Europe/Zurich'));
                if(!$cron->isValid()) throw new \Exception("replica_cron_ivalid");
                // calc next possible run
                if ($cron->getNext() < $nextTimestamp) $nextTimestamp = $cron->getNext();
            }
            $nextRun = date('Y-m-d H:i:s',$nextTimestamp);
        }

        // save next possible run
        $replicaMaster->setOvzReplicaNextrun($nextRun);
        if ($replicaMaster->save() === false) {
            $message = $this->translate("virtualserver_update_failed");
            throw new \Exception($message.$replicaMaster->getName());
        }

        // change status
        if($replicaMaster->getOvzReplicaStatus() != 3){
            $replicaMaster->setOvzReplicaStatus(2);
            if ($replicaMaster->save() === false) {
                $message = $this->translate("virtualserver_update_failed");
                throw new \Exception($message.$replicaMaster->getName());
            }
        }

        // initial Sync
        $params = array(
            "UUID"=>$replicaMaster->getOvzUuid(),
            "SLAVEHOSTFQDN"=>$replicaMaster->ovzReplicaHost->getFqdn(),
            "SLAVEUUID"=>$replicaMaster->ovzReplicaId->getOvzUuid(),
        );
        // pending with severity 1 so that in error state further jobs can be executed but the entity is marked with a errormessage     
        // callback to update virtualserver
        $pending = array(
            'model' => '\RNTForest\ovz\models\VirtualServers',
            'id' => $replicaMaster->getId(),
            'element' => 'replica',
            'severity' => 1,
            'params' => array(),
            'callback' => '\RNTForest\ovz\functions\Pending::updateAfterReplicaRun'
        );
        $job = $this->push->executeJob($replicaMaster->physicalServers,'ovz_sync_replica',$params,$pending);
        if($job->getDone() == 2){
            $message = $this->translate("virtualservers_job_sync_replica_failed");
            throw new \Exception($message.$job->getError());
        }

        return $job;
    }

    /**
    * search for all unactivated replicas
    * 
    */
    public function ovzReplicaCheckForUnactivatedReplicas(){
        $replicaMasters = VirtualServers::find(["conditions"=>"ovz=1 AND ovz_replica=0","order"=>"name"]);

        $message = "";
        $excludedServers = explode(',',$this->config->replica['excludedServers']);
        foreach($replicaMasters as $replicaMaster){
            if(in_array($replicaMaster->getId(),$excludedServers)) continue;
            $message .=  "Server ".$replicaMaster->getName().": Replica not activated.\n";
        }

        if(!empty($message)){
            $login = \RNTForest\core\models\Logins::findFirst($this->config->replica['infoLoginId']);
            $message = "Servers with non activated replica found!\n\n".$message;
            mail($login->getEmail(),"Inconsistencies in replicas",$message);
        }
    }

    /**
    * Führt einen Sync aller aktiven Replikas aus. Ungeachtet ob diese fällig sind
    * Die Syncs werden nacheinader ausgeführt, somit kann das Script recht lange laufen!
    * 
    */
    public function dailyReplicaSync(){
        try{
            $replicaMasters = VirtualServers::tryFind([
                "conditions"=>"ovz_replica=1 AND (ovz_replica_status = 1 OR ovz_replica_status = 9)",
                "order"=>"name",
            ]);

            foreach($replicaMasters as $replicaMaster){
                $this->logger->info('dailyReplica for '.$replicaMaster->getName().' started...');
                
                // replica today succesfull finished? Then skip this replica...
                $today = new \DateTime();
                $today->setTime(0,0,0); 
                $lastrun = \DateTime::createFromFormat( "Y-m-d H:i:s", $replicaMaster->getOvzReplicaLastrun());
                $lastrun->setTime(0,0,0);
                if($lastrun >= $today){
                    $this->logger->info('dailyReplica for '.$replicaMaster->getName().' skipped...');
                    continue;
                }

                try{
                    $job = $this->run($replicaMaster);
                    // wait until job is finished
                    while(!$this->isJobFinished($job))sleep(10);
                    $this->logger->info('dailyReplica for '.$replicaMaster->getName().' finished...');
                    if($job->getDone()==2) throw new \Exception($job->getError());
                    
                } catch (\Exception $e){
                    // something goes wrong: write log and go on...
                    $this->logger->error('dailyReplica for '.$replicaMaster->getName().' error:'.$e->getMessage());
                }               
            }

        } catch (\Exception $e){
            $this->dbPing();
            $this->logger->error($e->getMessage());
            return false;
        }
        return true;
    }

    public function isJobFinished(\RNTForest\core\models\Jobs $job){
        $this->dbPing();
        $job->refresh();
        return ($job->getDone()>0?true:false);
    }
    
    /**
    * checks the DB connection and try to reconnect
    * 
    */
    public function dbPing() {
        try {
            $this->db->fetchOne('SELECT 1');
        } catch (\PDOException $e) {
            $this->db->connect();
        }
    }
    
    public function cleanUpReplicaSnapshots(){
        try{
            // calculate last possible date
            $lastDate = new \DateTime(NULL,new \DateTimeZone('Europe/Zurich'));
            $lastDate->sub(new \DateInterval('P'.$this->config->replica["snapshotsKeepDays"].'D'));
            
            print_r($lastDate);

            $replicaMasters = VirtualServers::tryFind(["conditions"=>"ovz_replica=1","limit"=>"2"]);
            foreach($replicaMasters as $replicaMaster){
                // get all snapshots of this replica slave
                // execute ovz_list_snapshots job 
                // no pending needed because job is readonly       
                $params = array('UUID'=>$replicaMaster->OvzReplicaId->getOvzUuid());
                $job = $this->push->executeJob($replicaMaster->OvzReplicaId->PhysicalServers,'ovz_list_snapshots',$params);
                $message = $this->translate("virtualserver_job_listsnapshots_failed");
                if(!$job || $job->getDone()==2) {
                    $this->logger->warning($message);
                    continue;
                }
                $aSnapshots = $job->getRetval(true);
                foreach($aSnapshots as $snapshot){
                    $snapshotdate = new \DateTime($snapshot['Date'],new \DateTimeZone('Europe/Zurich'));
                    if($snapshotdate < $lastDate){
                        // remove snapshot
                        $params = array('UUID'=>$replicaMaster->OvzReplicaId->getOvzUuid(),'SNAPSHOTID'=>$snapshot['UUID']);
                        $job = $this->push->executeJob($replicaMaster->OvzReplicaId->PhysicalServers,'ovz_delete_snapshot',$params);
                        $message = "Deleting snapshot ".$snapshot['UUID']." of replica ".$replicaMaster->OvzReplicaId->getOvzUuid()." failed: ".$job->getError();
                        if(!$job || $job->getDone()==2) {
                            $this->logger->warning($message);
                            continue;
                        } else {
                            // wait until job is finished
                            while(!$this->isJobFinished($job))sleep(10);
                            $this->logger->info('Deleting snapshot '.$snapshot['UUID'].' for replica slave '.$replicaMaster->OvzReplicaId->getName().' finished.');
                        }
                    }
                }
            }
            
            // success
            $this->logger->info('Deleting snapshots for replica slaves sucessfull finished.');
            
        } catch(Exception $e){
            $this->logger->error('Deleting snapshots for replica slaves failed: '.$e->getMessage());
            return false;
        }
        return true;
    }

    /**
    * Checks if an sync is due and starts them
    * 
    */
    public function checkForNextSync(){
        try{
            // gets all overdue replicas
            $replicaMasters = VirtualServers::tryFind("ovz_replica=1 AND ovz_replica_status > 0 AND ovz_replica_nextrun != '0000-00-00 00:00:00' AND ovz_replica_nextrun <= now()");

            // Start overdues replicas...
            foreach($replicaMasters as $replicaMaster){
                try{
                    $job = $this->run($replicaMaster);
                    // wait until job is finished
                    while(!$this->isJobFinished($job))sleep(10);
                    if($job->getDone()==2) throw new \Exception($job->getError());
                } catch(Exception $e){
                    // somtehing went wrong? Warn an go on
                    $this->logger->warning("Replica (".$replicaMaster->getName().") checkForNextSync() failed ".$e->getMessage());
                }                
            }
        } catch(Exception $e){
            $this->logger->error("Replica:checkForNextSync() failed ".$e->getMessage());
            return false;
        }
        return true;
    }

    /**
    * collect all replica sync stats from a day
    * 
    * @param string $date format Y-m-d
    * @throws \Exceptions
    */
    public static function tryGetStats($date){
        $stats = array();
        $jobs = \RNTForest\core\models\Jobs::tryFind([
            "conditions"=>"type ='ovz_sync_replica' AND DATE(created) ='".$date."' AND done = 1",
            "oder"=>"created",
        ]);
        
        // get all replica servers
        $replicaServers = VirtualServers::tryFind([
            "conditions"=>"ovz_replica = 1",
            "columns"=>"ovz_uuid"
        ]);
        
        // prepare array with uuid as key
        $replicaServers = $replicaServers->toArray();
        $emptyServers = array();
        foreach($replicaServers as $replicaServer){
            $emptyServers[$replicaServer['ovz_uuid']] = $replicaServer;
        }
        
        foreach($jobs as $job){
            $params = $job->getParams(true);
            $stat = $job->getRetval(true); 
            unset($stat['sync_stats']);
            $stat=array('server_uuid'=>$params['UUID'])+$stat;
            $stats[]=$stat;
            
            // if a job to this server exists, the replica was executed
            // remove server from emptyServers Array
            unset($emptyServers[$params['UUID']]);
        }
        
        $stats['empty_servers'] = $emptyServers;
        return $stats;
    }
    
    /**
    * Print replica stats of a certain date
    * 
    * @param mixed $date
    */
    public function replicaStatsPDFPrint($date){
        // convert date
        $date = date("Y-m-d",strtotime($date));
        
        // Create PDF Object
        $this->PDF = new \RNTForest\core\libraries\PDF;
        
        // Author and title 
        $this->PDF->SetAuthor(BASE_PATH.$this->config->pdf['author']);
        $this->PDF->SetTitle(self::translate("virtualservers_replicapdf"));
        $this->PDF->SetAutoPageBreak(false);

        // Creating page 
        $this->PDF->AddPage('L','A4');

        // Print Logo
        if(file_exists(BASE_PATH.$this->config->pdf['logo'])) {
            $this->PDF->Image(BASE_PATH.$this->config->pdf['logo'], 230, 12, 50, '', 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
        }

        // Title
        $this->PDF->SetFont('' ,'B', 18);
        $this->PDF->Cell(0,0,self::translate('virtualservers_replicapdf'),0,1);

        // Date
        $locale = '';
        $sessionLocale = \Phalcon\Di::getDefault()->get("session")->get("auth")["locale"];
        if($sessionLocale == 'de_DE.utf8') $locale = 'de_CH.utf8';
        setlocale(LC_TIME,$sessionLocale,$locale);
        $this->PDF->SetFont('' ,'', 12); 
        $this->PDF->Cell(0,0,strftime('%d. %B %Y',strtotime($date)),0,1);
        $this->PDF->Ln(10);

        // Get stats of all replicas
        $replicas = $this->tryGetStats($date);
        if(!$replicas){
            $message = self::translate("virtualserver_replicapdf_no_replicas_found");
            throw new \Exception($message);
        }
        
        // print column header
        $this->replicaStatsPDFPrintHeader();
        
        // define cell height for the stats
        $cellHeight = 7;
        
        // go through all replica stats
        foreach($replicas as $key=>$replicaStats){
            // check for pagebreak
            $this->replicaStatsPDFCheckPageBreak();
            
            // list all replica servers without a replica jobs
            if($key === 'empty_servers'){
                foreach($replicaStats as $uuid=>$replicaServer){
                    // check for pagebreak
                    $this->replicaStatsPDFCheckPageBreak();
                    
                    // get virtual server
                    $virtualServer = VirtualServers::findFirst(array("ovz_uuid = '".$uuid."'"));
                    
                    // print master and slave name
                    $this->PDF->Cell(55,$cellHeight,$virtualServer->getName(),1,0,'',false,'',1);
                    $this->PDF->Cell(60,$cellHeight,$virtualServer->OvzReplicaId->getName(),1,0,'',false,'',1);
                    
                    // print error message
                    $this->PDF->Cell(150,$cellHeight,self::translate("virtualserver_replicapdf_no_replica"),1,1,'',false,'',1);
                }
            }else{
                // check if server_uuid exists in replicaStats array
                if(array_key_exists('server_uuid',$replicaStats)){
                    // get virtual server via uuid
                    $virtualServer = VirtualServers::findFirst(array("ovz_uuid = '".$replicaStats['server_uuid']."'"));
                    
                    // check if the stats key exist in the array
                    if(array_key_exists('start',$replicaStats) && 
                        array_key_exists('end',$replicaStats) && array_key_exists('stats_numbre_of_files',$replicaStats) &&
                        array_key_exists('stats_total_transferred_file_size',$replicaStats)){
                    
                        // print master and slave name
                        $this->PDF->Cell(55,$cellHeight,$virtualServer->getName(),1,0,'',false,'',1);
                        $this->PDF->Cell(60,$cellHeight,$virtualServer->OvzReplicaId->getName(),1,0,'',false,'',1);
                        
                        // get time without date from start
                        $start = date("H:i:s",strtotime($replicaStats['start']));
                        $this->PDF->Cell(25,$cellHeight,$start,1,0);
                        // get time without date from end
                        $end = date("H:i:s",strtotime($replicaStats['end']));
                        $this->PDF->Cell(25,$cellHeight,$end,1,0);
                        // calculate duration
                        $difference = strtotime($replicaStats['end'])-strtotime($replicaStats['start']);
                        if(gmdate("H",$difference) >= 1){
                            $duration = gmdate("H:i:s",$difference);
                        }else{
                            $duration = gmdate("i:s",$difference);
                        }
                        if($difference/60/60 > 1){
                            // if it took more than 1hour, mark as dark red
                            $this->PDF->SetFillColor(255,84,84);
                            $duration = $duration." Stdn.";
                        }elseif($difference/60 > 5){
                            // if duration is longer than 5min, mark as red
                            $this->PDF->SetFillColor(255,153,153);
                            $duration = $duration." Min.";
                        }else{
                            // else mark as green
                            $this->PDF->SetFillColor(181,255,181);
                            $duration = $duration." Min.";
                        }
                        $this->PDF->Cell(30,$cellHeight,$duration,1,0,'',true);
                        // number of files
                        $this->PDF->Cell(30,$cellHeight,$replicaStats['stats_numbre_of_files'],1,0);
                        // format total transferred bytes
                        $this->PDF->Cell(40,$cellHeight,\RNTForest\core\libraries\Helpers::formatBytesHelper($replicaStats['stats_total_transferred_file_size']),1,1);
                    }else{
                        // print master and slave name
                        $this->PDF->Cell(55,$cellHeight,$virtualServer->getName(),1,0,'',false,'',1);
                        $this->PDF->Cell(60,$cellHeight,$virtualServer->OvzReplicaId->getName(),1,0,'',false,'',1);
                        
                        // print error message
                        $this->PDF->Cell(150,$cellHeight,self::translate("virtualserver_replicapdf_no_stats"),1,1,'',false,'',1);
                    }
                }
            }
        }
        
        // Dispaly PDF
        $this->PDF->Output('Replica_Stats.pdf', 'I');
        die();
    }
    
    /**
    * helper method to print the columd header in the replica stats PDF
    * 
    */
    private function replicaStatsPDFPrintHeader(){
        // Print column header
        $this->PDF->SetFont('','B');
        $this->PDF->Cell(55,8,self::translate("virtualserver_replicapdf_master"),1,0);
        $this->PDF->Cell(60,8,self::translate("virtualserver_replicapdf_slave"),1,0);
        $this->PDF->Cell(25,8,self::translate("virtualserver_replicapdf_start"),1,0);
        $this->PDF->Cell(25,8,self::translate("virtualserver_replicapdf_end"),1,0);
        $this->PDF->Cell(30,8,self::translate("virtualserver_replicapdf_duration"),1,0);
        $this->PDF->Cell(30,8,self::translate("virtualserver_replicapdf_files"),1,0);
        $this->PDF->Cell(40,8,self::translate("virtualserver_replicapdf_bytes"),1,1);
        $this->PDF->SetFont('','');
    }
    
    /**
    * helper method to check for an page break and print header
    * 
    */
    private function replicaStatsPDFCheckPageBreak(){
        // check for page break
        if ($this->PDF->getY() > ($this->PDF->getPageHeight() - 35)) {
            // add new page
            $this->PDF->AddPage();
            // print column heaader
            $this->replicaStatsPDFPrintHeader();
        }
    }    
}