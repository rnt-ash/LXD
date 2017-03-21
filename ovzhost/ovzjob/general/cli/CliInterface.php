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

interface CliInterface {
    /**
    * Execute a Shell Command.
    * suffix '2>&1' is added by method.
    * 
    * @param string $command Input Parameter
    * @return int ExitStatus
    */
    public function execute($command,$host='');
    
    /**
    * Execute a Shell Command in background.
    * prefix 'nohup' and suffix '2>&1 &' is set by method.
    * 
    * @param string $command Input Parameter
    * @return int ExitStatus
    */
    public function executeBackground($command,$host='');

    /**
    * getter    
    */
    public function getOutput();
}