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

class OvzNewVsBgJob extends AbstractOvzJob{

    public static function usage(){
        return null;
    }
    
    public function run() {
        $this->Context->getLogger()->debug("VS create background!");
        
        // generate hostname if missing
        if (empty($this->Params['HOSTNAME'])) $this->Params['HOSTNAME']="new".$this->Params['VSTYPE']."_".$this->Params['UUID'];

        // try to create VS
        if(strtoupper($this->Params['VSTYPE'])=="CT")
            $exitstatus = $this->PrlctlCommands->createCt($this->Params);
        else
            $exitstatus = $this->PrlctlCommands->createVm($this->Params);
        if($exitstatus > 0) return $this->commandFailed("Creating VS failed",$exitstatus);

        $warnings = array();

        // try to set cpus
        $exitstatus = $this->PrlctlCommands->setCpu($this->Params['UUID'],$this->Params['CPUS']);
        if($exitstatus > 0) {
            $warnings['CPUS'] = "Setting CPUs failed (Exit Code: ".$exitstatus."), Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($warnings['CPUS']);
            // go on with work...
        }

        // try to set RAM
        $exitstatus = $this->PrlctlCommands->setRam($this->Params['UUID'],$this->Params['RAM']);
        if($exitstatus > 0) {
            $warnings['RAM'] = "Setting RAM failed (Exit Code: ".$exitstatus."), Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($warnings['RAM']);
            // go on with work...
        }
        
        // try to set DISKSPACE
        $exitstatus = $this->PrlctlCommands->setValue($this->Params['UUID'],'diskspace',$this->Params['DISKSPACE']);
        if($exitstatus > 0){
            $warnings[] = "Setting diskspace failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($this->Error);
            // go on with work...
        }

        // try to set Root password
        $exitstatus = $this->PrlctlCommands->setRootPwd($this->Params['UUID'],$this->Params['ROOTPWD']);
        if($exitstatus > 0) {
            $warnings['ROOTPWD']= "Setting Root password failed (Exit Code: ".$exitstatus."), Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($warnings['ROOTPWD']);
            // go on with work...
        }

        $this->Warning = implode("\n",$warnings);
        
        $exitstatus = $this->PrlctlCommands->listInfo($this->Params['UUID']);
        if($exitstatus > 0) return $this->commandFailed("Getting info failed",$exitstatus);

        $array = json_decode($this->PrlctlCommands->getJson(),true);
        if(is_array($array) && !empty($array)){
            $array[0]['Timestamp'] = date('Y-m-d H:i:s');
            $this->Done = 1;    
            $this->Retval = json_encode($array[0]);
            $this->Context->getLogger()->debug("Creating background VS done");
            return 0;
        }else{
            $this->Done = 2;
            $this->Error = "Convert info to JSON failed!";
            $this->Context->getLogger()->debug($this->Error);
            return 1;
        }
    }
}
