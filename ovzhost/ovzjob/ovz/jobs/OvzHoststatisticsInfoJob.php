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

namespace RNTForest\OVZJOB\ovz\jobs;

class OvzHoststatisticsInfoJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_hoststatistics_info",
            "description" => "get a JSON with statistics information about the OpenVZ 7 host",
            "params" => [
            ],
            "params_example" => '',
            "retval" => 'JSON object with statistics infos of the OpenVZ 7 host, e.g.  {"cpu_load":0.05,"memory_free":223916032,"diskspace_free":21774663680}',
            "warning" => "nothing specified",
            "error" => "different causes (getting statistics failed)",
        ];
    }

    public function run() {
        $this->Context->getLogger()->debug("Get host statistics!");

        try{
            $retval = array();
            $retval['cpu_load'] = $this->checkCPULoad();
            $retval['memory_free'] = $this->checkMemoryFree();
            $retval['diskspace_free'] = $this->checkDiskspaceFree();
            
            $this->Done = 1;    
            $this->Retval = json_encode($retval);
            $this->Context->getLogger()->debug("Get host statistics success.");
        }catch(\Exception $e){
            $this->Done = 2;
            $this->Error = "Convert host statistics to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);            
        }
    }

    /**
    * Checks free Diskspace
    * 
    */
    private function checkDiskspaceFree() {
        $output = disk_free_space("/");
        $diskfreespace = $output;
        return $diskfreespace;
    }

    /**
    * Checks CPU load
    * 
    */
    private function checkCPULoad() {
        $output = file_get_contents('/proc/loadavg');
        $firstSpacePosition = strpos($output," "); 
        $secondSpacePosition = strpos($output," ",$firstSpacePosition+1);
        // we need the 2. value because this is the average of the last 5 minutes
        $loadavgTotal = substr($output,$firstSpacePosition+1,$secondSpacePosition-($firstSpacePosition+1));

        // divide the total load to the number of cores
        $loadavg = round($loadavgTotal / intval(exec('nproc')),2);

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
        $memory[] = $meminfo["MemFree"];
        $memory[] = explode (" ",$memory[0]);

        // convert kilobytes to bytes
        $freebytes = $memory[0] * 1024; 
        return $freebytes;
    }
}
