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

use RNTForest\jobsystem\general\utility\Context;

/**
* VS: Virtual System eg. CT or VM
* CT: Container
* VM: Virtual Machine
*/

class VzctlCommands {

    /**
    * @var Context
    */
    private $Context;

    /**
    * @var {\RNTForest\jobsystem\general\psrlogger\LoggerInterface|LoggerInterface}
    */
    private $Logger;

    /**
    * @var {\RNTForest\jobsystem\general\cli\CliInterface|CliInterface}
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
        $cmd = "vzctl start ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }

    /**
    * restart a VS
    * 
    * @param string $UUID
    * @return int return value
    */
    public function restart($UUID){
        $cmd = "vzctl restart ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }

    /**
    * stop a VS
    * 
    * @param string $UUID
    * @return int return value
    */
    public function stop($UUID){
        $cmd = "vzctl stop ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd);
    }
    
    /**
    * tries to mount a VS
    *         
    * @param int $uuid
    */
    public function mount($UUID,$host=''){
        $cmd = "vzctl mount ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd,$host);
    }
    
    /**
    * tries to unmount a VS
    *         
    * @param int $uuid
    */
    public function umount($UUID,$host=''){
        $cmd = "vzctl umount ".escapeshellarg($UUID);
        return $this->Cli->execute($cmd,$host);
    }

    /**
    * create a snapshot
    * 
    * @param string $CTID
    * @param string $name
    * @param string $description
    * @param string $host
    */
    public function createSnapshot($CTID,$name,$description,$snapshotUUID,$host=""){
        $cmd = ("vzctl snapshot ".escapeshellarg($CTID).
                " --name ".escapeshellarg($name).
                " --description ".escapeshellarg($description).
                (($snapshotUUID!=NULL)?" --id ".escapeshellarg($snapshotUUID):""));
        $exitstatus = $this->Cli->execute($cmd,$host);
        return $exitstatus;
    }

    /**
    * delete a snapshot
    * 
    * @param string $UUID
    * @param string $snapshotID
    * @param string $host
    */
    public function deleteSnapshot($UUID,$snapshotID,$host=""){
        $cmd = ("vzctl snapshot-delete ".escapeshellarg($UUID).
                " --id ".escapeshellarg($snapshotID));
        $exitstatus = $this->Cli->execute($cmd,$host);
        return $exitstatus;
    }

    /**
    * switch to a snapshot
    * 
    * @param string $UUID
    * @param string $snapshotID
    * @param string $host
    */
    public function switchSnapshot($UUID,$snapshotID,$host=""){
        $cmd = ("vzctl snapshot-switch ".escapeshellarg($UUID).
                " --id ".escapeshellarg($snapshotID));
        $exitstatus = $this->Cli->execute($cmd,$host);
        return $exitstatus;
    }
      
    /**
    * mount a snapshot
    * 
    * @param string $CTID
    * @param string $snapshotID
    * @param string $target
    * @param string $host
    */
    public function mountSnapshot($CTID,$snapshotID,$target,$host=""){
        $cmd = ("vzctl snapshot-mount ".escapeshellarg($CTID).
                " --id ".escapeshellarg($snapshotID).
                " --target ".escapeshellarg($target));
        $exitstatus = $this->Cli->execute($cmd,$host);
        return $exitstatus;
    }
      
    /**
    * unmount a snapshot
    * 
    * @param string $CTID
    * @param string $snapshotID
    * @param string $target
    * @param string $host
    */
    public function umountSnapshot($CTID,$snapshotID,$host=""){
        $cmd = ("vzctl snapshot-umount ".escapeshellarg($CTID).
                " --id ".escapeshellarg($snapshotID));
        $exitstatus = $this->Cli->execute($cmd,$host);
        return $exitstatus;
    }
      
    
    
}
