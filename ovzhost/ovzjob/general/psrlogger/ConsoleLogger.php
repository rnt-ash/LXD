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

namespace RNTFOREST\OVZJOB\psrlogger;

use RNTFOREST\OVZJOB\psrlogger\AbstractLogger;
use RNTFOREST\OVZJOB\psrlogger\LogLevel;

class ConsoleLogger extends AbstractLogger{
    
    public function __construct(){
    }
        
    /**
     * Logs with an arbitary level.
     * 
     * 
     * @param string  $level
     * @param string $message
     * @param array  $context will be ignored here
     *
     * @return null
     * @throws Exception
     */
    public function log($level, $message, array $context = array()){
        if(!$this->checkLogLevelExists($level)){
            throw new \InvalidArgumentException("Das Level ".$level." ist kein valides Level.");
        }
        $logmessage = "[".$level."] ".date("Y-m-d H:i:s")." ".$message."\n";
        echo $logmessage;
    }
}
