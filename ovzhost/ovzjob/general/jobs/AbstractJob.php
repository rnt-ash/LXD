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

namespace RNTFOREST\OVZJOB\general\jobs;

use RNTFOREST\OVZJOB\general\utility\Context;

abstract class AbstractJob {
    protected $Id;
    protected $JobType;
    protected $Params = array();
    
    /**
    * @var Context
    */
    protected $Context;
    protected $Error = '';
    protected $Warning = '';
    protected $Retval = '';
    protected $Done = 0;
    
    public function __construct(Context $context) {
        $this->Context = $context;        
    }

    public final function getJobType() {
        return $this->JobType;
    }

    /**
     * Set params from a given json string.
     * 
     * @param string $jsonParams
     */
    public function setParams($jsonParams){
        $this->Params = json_decode($jsonParams,true);
    }
    
    public function setId($id){
        $this->Id = intval($id);
    }

    public function getError(){
        return $this->Error;
    }
    
    public function getWarning(){
        return $this->Warning;
    }
    
    public function getRetval(){
        return $this->Retval;
    }
    
    public function getDone(){
        return $this->Done;
    }
    
    /**
     * THE method which runs the job.
     * In this method the business logic should be implemented.
     * Override this method in the concrete job classes.
     * 
     * @return boolean
     */
    public abstract function run();
        
}
