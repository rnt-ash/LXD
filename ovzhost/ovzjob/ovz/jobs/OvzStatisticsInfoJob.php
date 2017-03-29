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

class OvzStatisticsInfoJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_statistics_info",
            "description" => "get a JSON with statistics information about a specific VirtualServer",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)"
            ],
            "params_example" => '{"UUID":"47cb40ea-e0cf-440f-b098-94e1a5b06fad"}',
            "retval" => 'JSON object with statistics infos of the VirtualServer, e.g.  {"guest":{"cpu":{"usage":"0","time":"0"},"ram":{"usage":"206","cached":"89","total":"512",...}}} ',
            "warning" => "nothing specified",
            "error" => "different causes (VS does not exist, getting statistics failed)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Get statisticss!");

        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }
        
        $exitstatus = $this->PrlctlCommands->statisticsInfo($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting statistics failed",$exitstatus);
        
        $ovzStatistics = json_decode($this->PrlctlCommands->getJson(),true);
        
        // add modified at first position of the array
        $this->array_unshift_assoc($ovzStatistics,'Timestamp',date("Y-m-d H:i:s"));
        
        // convert ram to mb and add to array at new key
        if(is_array($ovzStatistics) 
        && key_exists('guest',$ovzStatistics)
        && is_array($ovzStatistics['guest'])
        && key_exists('ram',$ovzStatistics['guest'])
        && is_array($ovzStatistics['guest']['ram'])
        && key_exists('total',$ovzStatistics['guest']['ram'])
        && key_exists('usage',$ovzStatistics['guest']['ram'])
        ){
            $memoryFreeMb = $ovzStatistics['guest']['ram']['total'] - $ovzStatistics['guest']['ram']['usage'];
            // already in MB per default in statistics
            $ovzStatistics['guest']['ram']['memory_free_mb'] = $memoryFreeMb;
        }
        
        // convert diskspace to gb and add to array at new key
        if(is_array($ovzStatistics) 
        && key_exists('guest',$ovzStatistics)
        && is_array($ovzStatistics['guest'])
        && key_exists('fs0',$ovzStatistics['guest'])
        && is_array($ovzStatistics['guest']['fs0'])
        && key_exists('free',$ovzStatistics['guest']['fs0'])
        ){
            $diskspaceFreeKb = $ovzStatistics['guest']['fs0']['free'];
            // convert from KB to GB
            $diskspaceFreeGb = $diskspaceFreeKb / 1024 / 1024;
            $ovzStatistics['guest']['fs0']['diskspace_free_gb'] = $diskspaceFreeGb;
        }
        
        if(is_array($ovzStatistics) && !empty($ovzStatistics)){
            $this->Done = 1;    
            $this->Retval = json_encode($ovzStatistics);
            $this->Context->getLogger()->debug("Get statistics success.");
        }else{
            $this->Done = 2;
            $this->Error = "Convert statistics to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
        }
    }
    
    function array_unshift_assoc(&$arr, $key, $val) { 
        $arr = array_reverse($arr, true); 
        $arr[$key] = $val; 
        $arr = array_reverse($arr, true); 
        return $arr;
    }
}
