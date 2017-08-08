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

class PloopCommands {

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
    * unmount a ploop device
    * 
    * @param string $mountPoint
    * @param string $host
    */
    public function umountMountPoint($mountPoint,$host=""){
        $cmd = ("ploop umount -m ".escapeshellarg($mountPoint));
        $exitstatus = $this->Cli->execute($cmd,$host);
        return $exitstatus;
    }
      
    
    
}
