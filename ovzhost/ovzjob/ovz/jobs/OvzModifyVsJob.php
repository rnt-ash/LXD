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

/**
* ovz_modify_vs    UUID, CONFIG
*/

class OvzModifyVsJob extends AbstractOvzJob{

    public function run() {
        $this->Context->getLogger()->debug("Modify VS!");
        
        if(!$this->vsExists($this->Params['UUID'])) return 9;
        
        $config = $this->Params['CONFIG'];
        $errors = array();
        foreach($config as $key=>$value){
            $exitstatus = $this->PrlctlCommands->setValue($this->Params['UUID'],$key,$value);
            if($exitstatus > 0){
                $errors[] = "Setting of '".$key."' failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput());
                $this->Context->getLogger()->debug($this->Error);
            }
        }
        if(!empty($errors)) {
            $this->Done = 2;
            $this->Error = implode("\n",$errors);
            $this->Context->getLogger()->debug($this->Error);
            return 255;
        }

        $exitstatus = $this->PrlctlCommands->listInfo($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting info failed",$exitstatus);

        $array = json_decode($this->PrlctlCommands->getJson(),true);
        if(is_array($array) && !empty($array)){
            $this->Done = 1;    
            $this->Retval = json_encode($array[0]);
            $this->Context->getLogger()->debug("modify VS success.");
            return 0;
        }else{
            $this->Done = 2;
            $this->Error = "Convert info to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
            return 1;
        }
    }
}
