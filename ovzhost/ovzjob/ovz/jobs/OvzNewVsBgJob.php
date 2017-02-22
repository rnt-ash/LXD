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
* ovz_new_vs    VSTYPE, UUID, NAME, OSTEMPLATE, DISTRIBUTION, HOSTNAME, CPUS, RAM, DISKSPACE, ROOTPWD
*/

class OvzNewVsBgJob extends AbstractOvzJob{

    public function usage(){
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

        $errors = array();

        // try to set cpus
        $exitstatus = $this->PrlctlCommands->setCpu($this->Params['UUID'],$this->Params['CPUS']);
        if($exitstatus > 0) {
            $errors['CPUS'] = "Setting CPUs failed (Exit Code: ".$exitstatus."), Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($errors['CPUS']);
            // go on with work...
        }

        // try to set RAM
        $exitstatus = $this->PrlctlCommands->setRam($this->Params['UUID'],$this->Params['RAM']);
        if($exitstatus > 0) {
            $errors['RAM'] = "Setting RAM failed (Exit Code: ".$exitstatus."), Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($errors['RAM']);
            // go on with work...
        }
        
        // try to set DISKSPACE
        $exitstatus = $this->PrlctlCommands->setValue($this->Params['UUID'],'diskspace',$this->Params['DISKSPACE']);
        if($exitstatus > 0){
            $errors[] = "Setting diskspace failed. Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($this->Error);
            // go on with work...
        }

        // try to set Root password
        $exitstatus = $this->PrlctlCommands->setRootPwd($this->Params['UUID'],$this->Params['ROOTPWD']);
        if($exitstatus > 0) {
            $errors['ROOTPWD']= "Setting Root password failed (Exit Code: ".$exitstatus."), Output:\n".implode("\n",$this->Context->getCli()->getOutput());
            $this->Context->getLogger()->debug($errors['ROOTPWD']);
            // go on with work...
        }

        $this->Error = implode("\n",$errors);
        $this->Done = 1;    
        $this->Context->getLogger()->debug("Creating background VS done");
    }
}
