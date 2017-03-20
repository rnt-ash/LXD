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

namespace RNTForest\ovz\utilities\datastructures;

class MonLocalValueStatus
{    
    private $value;
    private $status;
    
    /**
    * 
    * @param string $value
    * @param string $status
    */
    public function __construct($value, $status){
        $this->value = $value;
        $this->status = $status;
    }   
    
    /**
    * 
    * @return string
    */
    public function getStatus(){
        return $this->status;
    }
    
    /**
    * 
    * @return string
    */
    public function getValue(){
        return $this->value;
    }
}
