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

use RNTFOREST\OVZJOB\general\jobs\AbstractJob;
use RNTFOREST\OVZJOB\general\utility\Context;

use RNTFOREST\OVZJOB\ovz\utility\PrlctlCommands;
use RNTFOREST\OVZJOB\ovz\utility\PrlsrvctlCommands;

abstract class AbstractOvzJob extends AbstractJob{

    /**
    * @var PrlctlCommands
    */
    protected $PrlctlCommands;

    /**
    * @var PrlsrvctlCommands
    */
    protected $PrlsrvctlCommands;

    public function __construct(Context $context) {
        parent::__construct($context);
        $this->PrlctlCommands = new PrlctlCommands($context);
        $this->PrlsrvctlCommands = new PrlsrvctlCommands($context);
    }

    /**
    * helper method
    *     
    * @param mixed $message
    */
    protected function commandSuccess($message){
        $this->Done = 1;    
        $this->Retval = $this->Context->getCli()->getOutput();
        $this->Context->getLogger()->debug($message);
    }

    /**
    * helper method
    * 
    * @param dtring $message
    */
    protected function commandFailed($message,$exitstatus){
        $this->Done = 2;
        $this->Error = $message." Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput());
        $this->Context->getLogger()->debug($this->Error);
        return $exitstatus;
    }

    /**
    * checks if a VS exists. Otherwise it generates an Error 
    * 
    * @param string $uuid
    */
    protected function vsExists($uuid){
        $found = false;
        $exitstatus = $this->PrlctlCommands->listVs();
        if($exitstatus == 0){
            $allVS = json_decode($this->PrlctlCommands->getJson(),true);
            foreach($allVS as $vs){
                if($vs['uuid'] == $uuid) $found = true;
            }
        }
        if(!$found){
            $this->Done = 2;
            $this->Error = "VS with UUID ".$uuid." not exists! ";
            $this->Context->getLogger()->debug($this->Error);
            return false;
        }
        return true;
    }

}
