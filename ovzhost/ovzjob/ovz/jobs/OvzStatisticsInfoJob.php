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

namespace RNTFOREST\OVZJOB\ovz\jobs;

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
        
        $array = json_decode($this->PrlctlCommands->getJson(),true);
        if(is_array($array) && !empty($array)){
            $array['Timestamp'] = date('Y-m-d h:m:s');
            $this->Done = 1;    
            $this->Retval = json_encode($array);
            $this->Context->getLogger()->debug("Get statistics success.");
        }else{
            $this->Done = 2;
            $this->Error = "Convert statistics to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
        }
    }
}
