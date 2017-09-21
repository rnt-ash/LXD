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

class OvzAllInfoJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_all_info",
            "description" => "get a JSON with statistics and information about the OpenVZ 7 host and all his guests",
            "params" => [
            ],
            "params_example" => '',
            "retval" => "JSON object with statistics and infos of the OpenVZ 7 hostand sll his guests",
            "warning" => "nothing specified",
            "error" => "different causes (getting statistics failed)",
        ];
    }

    public function run() {
        $this->Context->getLogger()->debug("Get all info!");
        $info = array();

        try{
            // Guest Infos
            $exitstatus = $this->PrlctlCommands->listInfo('');
            if($exitstatus > 0) return $this->commandFailed("Getting guest info failed",$exitstatus);
            $guestInfo = json_decode($this->PrlctlCommands->getJson(),true);
            foreach($guestInfo as $key=>$value){
                // Build GuestInfo Array with UUID as Key
                $info['GuestInfo'][$value['ID']] = $value;
                $this->array_unshift_assoc($info['GuestInfo'][$value['ID']],'Timestamp',date("Y-m-d H:i:s"));
            }

            // Host Info
            $exitstatus = $this->PrlsrvctlCommands->hostInfo();
            if($exitstatus > 0) return $this->commandFailed("Getting host info failed",$exitstatus);
            $info['HostInfo'] = json_decode($this->PrlsrvctlCommands->getJson(),true);
            $this->array_unshift_assoc($info['HostInfo'],'Timestamp',date("Y-m-d H:i:s"));

            // Guest Statistics
            $exitstatus = $this->PrlctlCommands->listVS();
            if($exitstatus > 0) return $this->commandFailed("Getting VS list failed",$exitstatus);
            foreach(json_decode($this->PrlctlCommands->getJson(),true) as $vs) {
                $info['GuestStatistics'][$vs['uuid']] = json_decode($this->readStatisticsFromFile($vs['uuid']));
            }

            // Host Statistics
            $hoststats = array();
            $hoststats['Timestamp'] = date("Y-m-d H:i:s");
            $hoststats['FsInfo'] = $this->genHostFsInfo();
            $hoststats['cpu_load'] = $this->checkCPULoad();
            $hoststats['memory_free_mb'] = $this->checkMemoryFree();
            $hoststats['diskspace_free_gb'] = $this->checkDiskspaceFree();
            $info['HostStatistics'] = $hoststats;

            // Ostemplates
            $exitstatus = $this->PrlctlCommands->ostemplatesList();
            if($exitstatus > 0) return $this->commandFailed("Getting ostemplates failed",$exitstatus);
            $info['OsTemplates']= json_decode($this->PrlctlCommands->getJson(),true);
            
            // everything seems ok            
            $this->Done = 1;    
            $this->Retval = json_encode($info);
            $this->Context->getLogger()->debug("Get all info success.");

        }catch(\Exception $e){
            $this->Done = 2;
            $this->Error = "Get all info failed.";
            $this->Context->getLogger()->debug($this->Error);            
        }
    }
    
    private function readStatisticsFromFile($uuid){
        $file = __DIR__.'/../../../statistics/'.$uuid;
        if(!file_exists($file)){
            $this->Cli->execute('touch '.$file);
        }
        return file_get_contents($file);    
    }

    function array_unshift_assoc(&$arr, $key, $val) { 
        $arr = array_reverse($arr, true); 
        $arr[$key] = $val; 
        $arr = array_reverse($arr, true); 
        return $arr;
    }

    /**
    * Gens the FsInfo Array for the Host
    * 
    * @return array
    */
    private function genHostFsInfo() {
        $parts = [];
            
        if($this->Context->getCli()->execute('df --output="source,target,size,used,avail"') == 0){
            $output = $this->Context->getCli()->getOutput();
            
            // kick out the first element, it is the header which we do not need
            array_shift($output);
            
            foreach($output as $line){
                $splits = preg_split('/\s+/', $line);
                // make a warning if there ar not 5 splits
                if( count($splits) != 5){
                    $this->Warning .= 'Problem generate HostFsInfo while handling line: \''.json_encode($line)."\'\n";    
                }
                
                // only take /dev but ignore /dev/ploop 
                if(strpos($splits[0],'/dev/') !== false
                AND strpos($splits[0],'/dev/ploop') === false
                ){
                    $temp['source'] = $splits[0];
                    $temp['target'] = $splits[1];
                    $temp['size_gb'] = $splits[2]/1024/1024;
                    $temp['used_gb'] = $splits[3]/1024/1024;
                    $temp['free_gb'] = $splits[4]/1024/1024;
                    $parts[$temp['target']] = $temp;
                }
            }
        }

        return $parts;
    }
    
    /**
    * Checks free Diskspace
    * 
    */
    private function checkDiskspaceFree() {
        $output = disk_free_space("/");
        $diskfreespace = $output / 1024 / 1024 / 1024;
        return $diskfreespace;
    }

    /**
    * Checks CPU load
    * 
    */
    private function checkCPULoad() {
        $output = file_get_contents('/proc/loadavg');
        $splits = explode(' ',$output);

        // array on key 2 contains the avg value of the last 15 mins 
        if(!is_array($splits) || !key_exists(2,$splits)){
            throw new \Exception("Could not get cpu load");
        }
        $loadavgTotal = $splits[2];

        // divide the total load to the number of cores
        $loadavg = round($loadavgTotal / intval(exec('nproc')),2)*100;

        $cpuload = $loadavg;
        return $cpuload;
    }

    /**
    * Checks free system memory
    * 
    */
    private function checkMemoryFree() {       
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = array();
        foreach ($data as $line) {

            if (!empty($line)){
                list($key,$val) = explode(":",$line);
                $meminfo[$key] = trim($val);
            }
        }
        $memory = array();
        $memFree = explode(" ",$meminfo["MemFree"]);
        $cached = explode(" ",$meminfo["Cached"]);
        // not used, so free and cached
        $freeMb = (intval($memFree[0])+intval($cached[0]))/1024;

        return $freeMb;
    }
}
