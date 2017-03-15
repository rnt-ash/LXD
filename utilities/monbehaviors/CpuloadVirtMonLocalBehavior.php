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
  
namespace RNTForest\ovz\utilities\monbehaviors;

use RNTForest\ovz\interfaces\MonLocalBehaviorInterface;
use RNTForest\ovz\utilities\MonLocalValueStatus;

class CpuloadVirtMonLocalBehavior implements MonLocalBehaviorInterface{
    
    /**
    * Returns the Status and Value of a MonLocalLogs in use of the given arguments.
    * 
    * @param array $ovzStatistics
    * @param integer $warnvalue
    * @param integer $maxvalue
    * @return \RNTForest\ovz\utilities\MonLocalValueStatus
    */
    public function execute($ovzStatistics,$warnvalue,$maxvalue){                
        $valuestatus = null;
        if(is_array($ovz_Statistics) 
        && key_exists('guest',$ovzStatistics)
        && is_array($ovzStatistics['guest'])
        && key_exists('cpu',$ovzStatistics['guest'])
        && is_array($ovzStatistics['guest']['cpu'])
        && key_exists('usage',$ovzStatistics['guest']['cpu'])
        ){
            $value = $ovzStatistics['guest']['cpu']['usage'];
            $status = 'normal';
            if($value > $maxvalue){
                $status = 'max';
            }elseif($value > $warnvalue){
                $status = 'warn';
            }
            $valuestatus = new MonLocalValueStatus($value,$status);            
        }
        return $valuestatus;
    }
}
