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

class GeneralTestDeletefileJob extends AbstractJob {
    
    public static function usage(){
        return null;
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Try to delete testfile.");
        if(!file_exists('testfile')){
            $this->Error = "Testfile already exists.";
            $this->Context->getLogger()->error($this->Error);
            $this->Done = 2;    
        }else{
            unlink('testfile');    
            $this->Done = 1;
            $this->Retval = "Testfile successfully created.";
        }
    }
}
