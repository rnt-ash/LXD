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

namespace RNTForest\ovz\datastructures;

use RNTForest\core\libraries\Helpers;

class DowntimePeriod
{    
    private $start;
    private $end;
    private $duration;
    
    /**
    * 
    * @param integer $start unixtimestamp 
    * @param integer $end unixtimestamp
    */
    public function __construct($start, $end){
        $this->start = intval($start);
        $this->end = intval($end);
        $this->duration = $this->end - $this->start;
    }   
    
    /**
    * getter start human readable.
    * 
    * @return string
    */
    public function getStartString(){
        return Helpers::createDateTimeFromUnixTimestamp($this->start);
    }
    
    /**
    * getter end human readable.
    * 
    * @return string 
    */
    public function getEndString(){
        return Helpers::createDateTimeFromUnixTimestamp($this->end);
    }
    
    /**
    * 
    * @return string
    */
    public function getDurationString(){
        return $this->duration." seconds";
    }
    
    /**
    * 
    * @return int
    */
    public function getDurationInSeconds(){
        return $this->duration;
    }
}
