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
namespace RNTForest\jobsystem\ovz\utility;

require_once(__DIR__.'/../../../vendor/autoload.php');
require_once(__DIR__.'/../../../../local.config.php');

use RNTForest\jobsystem\general\psrlogger\FileLogger;
use RNTForest\jobsystem\general\cli\ExecCli;

/**
* Used to precompute prlctl statistics and store them in files, because this command can last long.
* 
* - Can be used directly, just call this class with PHP
* - Can be called multiple times, it won't run parallel (good for cron)
* - Creates a lock file to prevent parallel prlctl statistics executions
*/
class PrlctlStatisticsFileGenerator {

    /**
    * LockFile, should be out of jobsystem directory to prevent deletion cause of syncs
    * 
    * @var string
    */
    private $LockFile;

    /**
    * Directory in which statistiscs files are stored
    * 
    * @var string
    */
    private $StatisticsFilesDirectory;

    /**
    * @var \RNTForest\jobsystem\general\psrlogger\FileLogger
    */
    private $Logger;

    /**
    * @var \RNTForest\jobsystem\general\cli\ExecCli
    */
    private $Cli;

    private $AllVs;

    private $Warning;

    public function __construct(){
        $this->LockFile = __DIR__.'/../../../../prlctlstatistics.lock';
        $this->StatisticsFilesDirectory = __DIR__.'/../../../statistics/';
        $this->Logger = new FileLogger(LOG_FILE);
        $this->Logger->setLogLevel(LOG_LEVEL);
        $this->Cli = new ExecCli($this->Logger);
    }

    public function genAll(){
        if($this->isLocked()){
            // do nothing...
        }else{
            $this->lock();

            $cmd = "prlctl list -ajo uuid,name,type,status";
            $exitstatus = $this->Cli->execute($cmd);
            if ($exitstatus != 0) {
                throw new \Exception("Could not list all virtuals.");
            }
            $json = implode("\n",$this->Cli->getOutput());
            $this->AllVs = json_decode($json,true);

            foreach($this->AllVs as $vs){
                $this->genForUuid($vs['uuid']);    
            }

            $this->cleanUpFilesOfNonexistentUuids();

            $this->freeLock();
        }
    }

    private function genForUuid($uuid){
        // get info
        $statistic = $this->statisticsInfo($uuid);

        // add timestamp and compute FsInfo
        $this->array_unshift_assoc($statistic,'FsInfo',$this->genGuestFsInfo($statistic));
        $this->array_unshift_assoc($statistic,'Timestamp',date("Y-m-d H:i:s"));

        // try to get cpu load from proc loadavg 
        $statistic = $this->setCpuLoadFromProcLoadAvgIfPossible($statistic, $uuid);

        // directory already exists?
        if(!file_exists($this->StatisticsFilesDirectory)){
            mkdir($this->StatisticsFilesDirectory,0770,true);
        }        

        // write to file
        $file = $this->StatisticsFilesDirectory.$uuid;
        if(!file_exists($file)){
            $this->Cli->execute('touch '.$file);
        }
        file_put_contents($file,json_encode($statistic)); 
    }


    /**
    * Gens the FsInfo Array for a Guest (from the given array)
    * 
    * @param array $virtualArray
    * @return array
    */
    private function genGuestFsInfo($virtualArray) {
        $disk = [];
        try{
            if(key_exists('guest',$virtualArray)
            && is_array($virtualArray['guest'])
            && key_exists('fs0',$virtualArray['guest'])
            && is_array($virtualArray['guest']['fs0'])
            ){
                $subArray = $virtualArray['guest']['fs0'];    
                if(!key_exists('name',$subArray)
                || !key_exists('total',$subArray)
                || !key_exists('free',$subArray)
                ){
                    throw new \Exception('needed keys in fs0 do not exist in '.json_encode($subArray));
                }

                $temp['source'] = $subArray['name'];
                $temp['target'] = '/';
                $temp['size_gb'] = $subArray['total']/1024/1024;
                $temp['used_gb'] = null;
                $temp['free_gb'] = $subArray['free']/1024/1024;

                $temp['used_gb'] = $temp['size_gb']-$temp['free_gb'];

                $disk[$temp['target']] = $temp;
            }else{
                throw new \Exception('needed keys for access to fs0 dont exist in '.json_encode($virtualArray)); 
            }   
        }catch(\Exception $e){
            $this->Warning .= "Prooblem generate GuestFsInfo: ".$e->getMessage()."\n"; 
        }

        return $disk;
    }

    private function array_unshift_assoc(&$arr, $key, $val) { 
        $arr = array_reverse($arr, true); 
        $arr[$key] = $val; 
        $arr = array_reverse($arr, true); 
        return $arr;
    }

    /**
    * Tries to get the cpu load average from /proc/loadavg if the system is a container and running.
    * This value is more reliable then the one from prlctl statistics.
    * 
    * @param array $statistic
    * @param string $uuid
    */
    private function setCpuLoadFromProcLoadAvgIfPossible($statistic, $uuid){
        $this->Cli->execute('prlctl list -ij '.$uuid);
        $output = implode("\n",$this->Cli->getOutput());
        $listinfo = json_decode($output, true);
        
        if(
        is_array($listinfo) 
        && !empty($listinfo) 
        && is_array($listinfo[0]) 
        && key_exists('Type', $listinfo[0]) 
        && $listinfo[0]['Type'] == 'CT'
        && key_exists('State', $listinfo[0]) 
        && $listinfo[0]['State'] == 'running'
        ){
            try{
                $loadAvg = $this->getCpuLoadFromProcLoadAvg($uuid);
                if ($loadAvg !== false) $statistic["guest"]["cpu"]["usage"] = $loadAvg;
            }catch(\Exception $e){
                $this->Logger->debug('could not get CpuLoadFromProcLoadAvg, so no value in statistic is changed');
            }
        }

        return $statistic;
    }

    private function getCpuLoadFromProcLoadAvg($uuid){
        $cmd = "prlctl exec ".$uuid." 'cat /proc/loadavg'";
        $this->Cli->execute($cmd);
        $output = $this->Cli->getOutput();
        $splits = explode(' ',$output[0]);

        // array on key 2 contains the avg value of the last 15 mins 
        if(!is_array($splits) || !key_exists(2,$splits)){
            throw new \Exception("Could not get cpu load");
        }
        $loadavgTotal = floatval($splits[2]);
        
        // divide the total load to the number of cores
        $exitstatus = $this->Cli->execute("prlctl exec ".$uuid." 'nproc'");
        if ($exitstatus > 0) return false;
        $nproc = $this->Cli->getOutput();
        
        $loadavg = round($loadavgTotal / intval($nproc[0]),2)*100;
        return $loadavg;
    }    

    /**
    * get statistics of a VS
    * 
    * @param string $UUID
    */
    private function statisticsInfo($UUID){
        // allenfalls auslagern, da in PrlctlCommands das gleiche drin ist
        $cmd = ("prlctl statistics ".escapeshellarg($UUID));
        $exitstatus = $this->Cli->execute($cmd);
        if($exitstatus > 0) return $exitstatus;

        $statistics = array();
        $i=0;
        $lines = $this->Cli->getOutput();
        foreach($lines as $line){
            $parts = explode(':',$line);
            $parts = array_map(function($p){return trim($p);},$parts);

            $keys = $parts[0];

            $this->Logger->debug($keys);
            // ignore some statistics who start with net.classfull...
            if(preg_match('`(net\.classful).*`',$keys)) continue;

            $val = $parts[1];

            // build the multidimensional array from the point-separated string as keys 
            $keys = explode('.', $keys);
            $arr = &$statistics;
            foreach ($keys as $key) {
                if(!key_exists($key,$arr)){
                    $arr[$key] = array();
                }
                $arr = &$arr[$key];
            }
            $arr = $val;
            unset($arr);

            // convert ram to mb and add to array at new key
            if(is_array($statistics) 
            && key_exists('guest',$statistics)
            && is_array($statistics['guest'])
            && key_exists('ram',$statistics['guest'])
            && is_array($statistics['guest']['ram'])
            && key_exists('total',$statistics['guest']['ram'])
            && key_exists('usage',$statistics['guest']['ram'])
            ){
                $memoryFreeMb = $statistics['guest']['ram']['total'] - ($statistics['guest']['ram']['usage'] - $statistics['guest']['ram']['cached']);
                // already in MB per default in statistics
                $statistics['guest']['ram']['memory_free_mb'] = $memoryFreeMb;
            }

            // convert diskspace to gb and add to array at new key
            if(is_array($statistics) 
            && key_exists('guest',$statistics)
            && is_array($statistics['guest'])
            && key_exists('fs0',$statistics['guest'])
            && is_array($statistics['guest']['fs0'])
            && key_exists('free',$statistics['guest']['fs0'])
            ){
                $diskspaceFreeKb = $statistics['guest']['fs0']['free'];
                // convert from KB to GB
                $diskspaceFreeGb = $diskspaceFreeKb / 1024 / 1024;
                $statistics['guest']['fs0']['diskspace_free_gb'] = $diskspaceFreeGb;
            }
        } 
        return $statistics;
    }

    private function cleanUpFilesOfNonexistentUuids(){
        $allUuids = [];
        foreach($this->AllVs as $vs){
            $allUuids[] = $vs['uuid'];
        }
        if ($handle = opendir($this->StatisticsFilesDirectory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if(!in_array($entry,$allUuids)){
                        $this->Logger->notice('Remove statistics file '.$entry.' which uuid does not exist anymore.');
                        $this->Cli->execute('rm '.$this->StatisticsFilesDirectory.$entry);
                    }
                }
            }
            closedir($handle);
        }   
    }

    private function isLocked(){
        if(file_exists($this->LockFile)){
            // remove lock if older than 1 hour (in case of a crash)
            if(filemtime($this->LockFile)+3600 < time()){
                $this->freeLock();
                return false;
            } else{
                return true;
            }
        }else{
            return false;
        }

    }

    private function lock(){
        $this->Cli->execute('touch '.$this->LockFile);
    }

    private function freeLock(){
        $this->Cli->execute('rm -f '.$this->LockFile);
    }

}

$generator = new PrlctlStatisticsFileGenerator();
$generator->genAll();
