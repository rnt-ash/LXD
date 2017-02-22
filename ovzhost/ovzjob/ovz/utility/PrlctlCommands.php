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

namespace RNTFOREST\OVZJOB\ovz\utility;

use RNTFOREST\OVZJOB\general\utility\Context;

/**
* VS: Virtual System eg. CT or VM
* CT: Container
* VM: Virtual Machine
*/

class PrlctlCommands {

    /**
    * @var Context
    */
    private $Context;

    /**
    * @var {\RNTFOREST\OVZJOB\general\psrlogger\LoggerInterface|LoggerInterface}
    */
    private $Logger;

    /**
    * @var {\RNTFOREST\OVZJOB\general\cli\CliInterface|CliInterface}
    */
    private $Cli;
    
    /**
    * status of a VS
    * 
    * @var array
    */
    private $Status = array();
    
    /**
    * container for JSON retunvalues
    * 
    * @var string
    */
    private $Json = "";

    /**
    * getter
    * 
    */
    public function getStatus(){
        return $this->Status;
    }

    public function getJson(){
        return $this->Json;
    }

    public function __construct(Context $context){
        $this->Context = $context;
        $this->Logger = $this->Context->getLogger();
        $this->Cli = $this->Context->getCli();
    }
    
    /**
    * start a VS
    * 
    * @param string $UUID
    * @return int return value
    */
    public function start($UUID){
        $cmd = "prlctl start ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }

    /**
    * restart a VS
    * 
    * @param string $UUID
    * @return int return value
    */
    public function restart($UUID){
        $cmd = "prlctl restart ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }

    /**
    * stop a VS
    * 
    * @param string $UUID
    * @return int return value
    */
    public function stop($UUID){
        $cmd = "prlctl stop ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }

    /**
    * write the status of a VS into the public property
    * 
    * @param string $UUID
    * @param string $host
    * 
    * @return int $exitstatus
    */
    public function status($UUID){
        $cmd = "prlctl status ".escapeshellarg($UUID);
        $exitstatus = $this->Cli->execute($cmd);
        if ($exitstatus == 0) {
            $aRetValTemp = explode(' ',$this->Cli->getOutput()[0]);
            $this->Status = array(
                'VSTYPE' => strtolower(trim($aRetValTemp[0]))=='ct'?"CT":"VM",
                'NAME' => trim($aRetValTemp[1]),
                'EXIST' => strtolower(trim($aRetValTemp[2]))=='exist'?true:false,
                'MOUNTED' => strtolower(trim($aRetValTemp[3]))=='mounted'?true:false,
                'RUNNING' => strtolower(trim($aRetValTemp[3]))=='running'?true:false,
                'SUSPENDED'  => (isset($aRetValTemp[3]) && strtolower(trim($aRetValTemp[3]))=='suspended')?true:false,
            );
        }
        return $exitstatus;
    }

    /**
    * write infos of a VS into a public property
    * 
    * @param string $UUID
    */
    public function listInfo($UUID){
        $cmd = "prlctl list -aifj ".escapeshellarg($UUID);
        $exitstatus = $this->Cli->execute($cmd);
        if ($exitstatus == 0) {
            $this->Json = implode("\n",$this->Cli->getOutput());
        }
        return $exitstatus;
    }

    /**
    * write JSON list of all VS into a public property
    */
    public function listVs(){
        $cmd = "prlctl list -ajo uuid,name,type,status";
        $exitstatus = $this->Cli->execute($cmd);
        if ($exitstatus == 0) {
            $this->Json = implode("\n",$this->Cli->getOutput());
        }
        return $exitstatus;
    }

    /**
    * set cpu cores
    * 
    * @param string $UUID
    * @param int $cpus
    */
    public function setCpu($UUID,$cpus){
        $cmd = "prlctl set ".escapeshellarg($UUID)." --cpus ".intval($cpus);
        return $this->Cli->execute($cmd);
    }

    /**
    * set ram
    * 
    * @param string $UUID
    * @param int $cpus
    */
    public function setRam($UUID,$ram){
        $cmd = "prlctl set ".escapeshellarg($UUID)." --memsize ".intval($ram);
        return $this->Cli->execute($cmd);
    }

    /**
    * set a value to a VS
    * 
    * @param string $UUID
    * @param string $key
    * @param mixed $value
    */
    public function setValue($UUID,$key,$value){
        switch(strtolower($key)){
            case 'hostname':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --hostname ".escapeshellarg($value);
                break;
            case 'memsize':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --memsize ".intval($value);
                break;
            case 'cpus':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --cpus ".intval($value);
                break;
            case 'diskspace':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --diskspace ".intval($value);
                break;
            case 'onboot':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --onboot ".escapeshellarg($value);
                break;
            case 'nameserver':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --nameserver ".escapeshellarg($value);
                break;
            case 'description':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --description ".escapeshellarg($value);
                break;
            case 'ipadd':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --ipadd ".escapeshellarg($value);
                break;
            case 'ipdel':
                $cmd = "prlctl set ".escapeshellarg($UUID)." --ipdel ".escapeshellarg($value);
                break;
            
        }
        return $this->Cli->execute($cmd);
    }
    
    /**
    * tries to kill an VS
    * 
    * @param int $UUID
    */
    public function killVs($UUID,$vsType="CT"){
        if($vsType=="CT")
            $cmd = "prlctl stop ".escapeshellarg($UUID)." --fast";
        else
            $cmd = "prlctl stop ".escapeshellarg($UUID)." --kill";
        return $this->Cli->execute($cmd);
    }
    
    /**
    * tries to mount a VS
    *         
    * @param int $uuid
    */
    public function mountVs($UUID){
        $cmd = "prlctl mount ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }
    
    /**
    * tries to unmount a VS
    *         
    * @param int $uuid
    */
    public function umountVs($UUID){
        $cmd = "prlctl umount ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }
    
    /**
    * tries to delete a VS
    * 
    * @param int $uuid
    */
    public function deleteVs($UUID){
        $cmd = "prlctl delete ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }

    /**
    * tries to set the root password of a VS
    * 
    * @param int $uuid
    * @param string $pwd password
    */
    public function setRootPwd($UUID,$pwd){
        $cmd = "prlctl set ".escapeshellarg($UUID)." --userpasswd root:".escapeshellcmd($pwd);
        return $this->Cli->execute($cmd);
    }
    
    /**
    * tries to exec a command in a VS
    * 
    * @param int $uuid
    * @param string $cmd command
    */
    public function setExecCmd($UUID,$cmd){
        $cmd = "prlctl exec ".escapeshellarg($UUID)." ".escapeshellcmd($cmd);
        return $this->Cli->execute($cmd);
    }

    /**
    * tries to receive a list of CT OS Templates
    */
    public function ostemplatesList(){
        $cmd = "vzpkg list -O";
        $exitstatus = $this->Cli->execute($cmd);
        if ($exitstatus == 0) {
            $templates = array();
            foreach($this->Cli->getOutput() as $line){
                $part = explode(' ',$line,2);
                $lastupdate = isset($part[1])?trim($part[1]):"0000-00-00 00:00:00";
                $templates[] = array('name'=>trim($part[0]),'lastupdate'=>$lastupdate);
            }
            $this->Json = json_encode($templates);
        }
        return $exitstatus;
    }
    
    /**
    * tries to create a new CT
    * 
    * @param array $params
    */
    public function createCt($params){
        $cmd = "prlctl create ".escapeshellarg($params['NAME']).
                " --vmtype ct ".
                " --ostemplate ".escapeshellarg($params['OSTEMPLATE']).
                " --uuid ".escapeshellarg($params['UUID']);
        $exitstatus = $this->Cli->execute($cmd);
        return $exitstatus;
    }
    
    /**
    * tries to create a new VM
    * 
    * @param array $params
    */
    public function createVm($params){
        $cmd = "prlctl create ".escapeshellarg($params['NAME']).
                " --vmtype vm ".
                " --distribution ".escapeshellarg($params['DISTRIBUTION']).
                " --uuid ".escapeshellarg($params['UUID']);
        $exitstatus = $this->Cli->execute($cmd);
        return $exitstatus;
    }
 
    /**
    * get all snapshots of a VS
    * 
    * @param string $UUID
    */
    public function listSnapshots($UUID){
        $cmd = ("prlctl snapshot-list ".escapeshellarg($UUID));
        $exitstatus = $this->Cli->execute($cmd);
        if($exitstatus > 0) return $exitstatus;

        $snapshots = array();
        $i=0;
        $lines = $this->Cli->getOutput();
        // ignore header
        unset($lines[0]);
        foreach($lines as $line){
            // get IDs
            $snapshots[$i]["Parent"] = trim(substr($line,1,36));
            $snapshots[$i]["Current"] = trim(substr($line,38,2));
            $snapshots[$i]["UUID"] = trim(substr($line,41,36));

            // additional info...
            $cmd = ("prlctl snapshot-list ".escapeshellarg($UUID)." -i ".$snapshots[$i]["UUID"]);
            $exitstatus = $this->Cli->execute($cmd);
            if($exitstatus == 0){
                $infos = $this->Cli->getOutput();
                unset($infos[0]);
                foreach($infos as $info){
                    $ainfo = explode(":",$info,2);
                    if(empty($ainfo[0])) continue;
                    $snapshots[$i][trim($ainfo[0])] = trim($ainfo[1]);
                }
            }
            $i++;
        }
        
        $this->Json = json_encode($snapshots);
        return 0;
    }

    /**
    * create a snapshot
    * 
    * @param string $UUID
    */
    public function createSnapshot($UUID,$name,$description){
        $cmd = ("prlctl snapshot ".escapeshellarg($UUID).
                " --name ".escapeshellarg($name).
                " --description ".escapeshellarg($description));
        $exitstatus = $this->Cli->execute($cmd);
        return $exitstatus;
    }

    /**
    * delete a snapshot
    * 
    * @param string $UUID
    */
    public function deleteSnapshot($UUID,$snapshotID){
        $cmd = ("prlctl snapshot-delete ".escapeshellarg($UUID).
                " --id ".escapeshellarg($snapshotID));
        $exitstatus = $this->Cli->execute($cmd);
        return $exitstatus;
    }

    /**
    * switch to a snapshot
    * 
    * @param string $UUID
    */
    public function switchSnapshot($UUID,$snapshotID){
        $cmd = ("prlctl snapshot-switch ".escapeshellarg($UUID).
                " --id ".escapeshellarg($snapshotID));
        $exitstatus = $this->Cli->execute($cmd);
        return $exitstatus;
    }
}
