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

namespace RNTForest\OVZJOB\general\jobs;

class GeneralTestSlowJob extends AbstractJob{

    public static function usage(){
        return null;
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Asynchronous background job will be starting soon.");

        $cmd = "php JobSystemStarter.php background ".$this->Id." > /dev/null";
        $exitstatus = $this->Context->getCli()->executeBackground($cmd);
        
        $this->Done = -1;
    }
}
