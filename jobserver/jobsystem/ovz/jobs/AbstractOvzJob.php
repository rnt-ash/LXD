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

namespace RNTForest\jobsystem\ovz\jobs;

use RNTForest\jobsystem\general\jobs\AbstractJob;
use RNTForest\jobsystem\general\utility\Context;

use RNTForest\jobsystem\ovz\utility\PrlctlCommands;
use RNTForest\jobsystem\ovz\utility\VzctlCommands;
use RNTForest\jobsystem\ovz\utility\PrlsrvctlCommands;

abstract class AbstractOvzJob extends AbstractJob{

    /**
    * @var PrlctlCommands
    */
    protected $PrlctlCommands;

    /**
    * @var VzctlCommands
    */
    protected $VzctlCommands;

    /**
    * @var PrlsrvctlCommands
    */
    protected $PrlsrvctlCommands;

    public function __construct(Context $context) {
        parent::__construct($context);
        $this->PrlctlCommands = new PrlctlCommands($context);
        $this->VzctlCommands = new VzctlCommands($context);
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
    * @param string $message
    */
    protected function commandFailed($message,$exitstatus){
        $this->Done = 2;
        $this->Error = $message." Exit Code: ".$exitstatus.", Output:\n".implode("\n",$this->Context->getCli()->getOutput());
        $this->Context->getLogger()->error($this->Error);
        return $exitstatus;
    }

    /**
    * checks if a VS exists. Otherwise it generates an Error 
    * 
    * @param string $uuid
    */
    protected function vsExists($uuid,$host=''){
        $found = false;
        $exitstatus = $this->PrlctlCommands->listVs($host);
        if($exitstatus == 0){
            $allVS = json_decode($this->PrlctlCommands->getJson(),true);
            foreach($allVS as $vs){
                if($vs['uuid'] == $uuid) $found = true;
            }
        }

        $this->Context->getLogger()->debug('VS with UUID '.$uuid.' was '.($found?'':'not ').'found.');
        return $found;
    }

}
