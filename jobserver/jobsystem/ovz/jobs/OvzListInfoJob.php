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

class OvzListInfoJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_list_info",
            "description" => "get a JSON with information about a specific VirtualServer",
            "params" => [
                "UUID" => "Universally Unique Identifier (UUID)"
            ],
            "params_example" => '{"UUID":"47cb40ea-e0cf-440f-b098-94e1a5b06fad"}',
            "retval" => 'JSON object with output what \'prlctl list -aifj UUID\' command would give, e.g. {"ID":"081b2b8e-bc5b-4a76-bd46-84251a8091fd","EnvID":"081b2b8e-bc5b-4a76-bd46-84251a8091fd",.....} ',
            "warning" => "nothing specified",
            "error" => "different causes (VS does not exist, getting info failed)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Get info!");

        if(!$this->vsExists($this->Params['UUID'])){
             return $this->commandFailed("VS with UUID ".$this->Params['UUID']." does not exist!",9);
        }
        
        $exitstatus = $this->PrlctlCommands->listInfo($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting info failed",$exitstatus);
        
        $array = json_decode($this->PrlctlCommands->getJson(),true);
        if(is_array($array) && !empty($array)){
            $array[0]['Timestamp'] = date('Y-m-d H:i:s');
            $this->Done = 1;    
            $this->Retval = json_encode($array[0]);
            $this->Context->getLogger()->debug("Get info success.");
        }else{
            $this->Done = 2;
            $this->Error = "Convert info to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
        }
    }
}
