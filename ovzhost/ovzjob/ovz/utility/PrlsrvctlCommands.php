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

class PrlsrvctlCommands {

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
    * container for JSON retunvalues
    * 
    * @var string
    */
    private $Json = "";

    public function getJson(){
        return $this->Json;
    }

    public function __construct(Context $context){
        $this->Context = $context;
        $this->Logger = $this->Context->getLogger();
        $this->Cli = $this->Context->getCli();
    }
    
    /**
    * write infos of a VS into a public property
    * 
    * @param string $UUID
    */
    public function hostInfo(){
        $cmd = ("prlsrvctl info -j");
        $exitstatus = $this->Cli->execute($cmd);
        if ($exitstatus == 0) {
            $this->Json = implode("\n",$this->Cli->getOutput());
        }
        return $exitstatus;
    }
}
