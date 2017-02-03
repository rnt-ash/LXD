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

namespace RNTFOREST\OVZJOB\general\cli;

use RNTFOREST\OVZJOB\general\psrlogger\LoggerInterface;

class LoggingCli implements CliInterface {
    
    /**
    * @var LoggerInterface
    */
    private $Logger;
    
    public function __construct(LoggerInterface $logger){
        $this->Logger = $logger;
    }

    /**
    * setter
    * 
    * @param string $host fqdn of remotehost or localhost
    */
    public function setHost($host){
        //do nothing
    }
    
    
    /**
    * getter
    *     
    */
    public function getOutput(){
        return array();
    }
    
    /**
    * Execute a Shell Command.
    * 
    * @param string $command Input Parameter
    * @return int ExitStatus
    */
    public function execute($command){
        $this->Logger->notice("LoggingCli: ".$command. " 2>&1");
        return 0;
    }

    /**
    * Execute a Shell Command in background.
    * prefix 'nohup' and suffix '2>&1 &' is set by method.
    * 
    * @param string $command Input Parameter
    * @return int ExitStatus
    */
    public function executeBackground($command){
        $this->Logger->notice("LoggingCli: nohup ".$command. " 2>&1 &");
        return 0;    
    }
}
