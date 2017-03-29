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
use RNTForest\ovz\datastructures\MonLocalValueStatus;
use RNTForest\ovz\models\MonLocalJobs;
use RNTForest\core\libraries\Helpers;

class DiskspacefreeVirtMonLocalBehavior implements MonLocalBehaviorInterface{
    
    /**
    * Returns the Status and Value of a MonLocalLogs in use of the given arguments.
    * 
    * @param string $ovzStatistics JSON
    * @param numeric $warnvalue
    * @param numeric $maxvalue
    * @return \RNTForest\ovz\utilities\MonLocalValueStatus
    */
    public function execute($ovzStatistics,$warnvalue,$maxvalue){                
        $valuestatus = null;
        $ovzStatistics = json_decode($ovzStatistics,true);
        if(is_array($ovzStatistics) 
        && key_exists('guest',$ovzStatistics)
        && is_array($ovzStatistics['guest'])
        && key_exists('fs0',$ovzStatistics['guest'])
        && is_array($ovzStatistics['guest']['fs0'])
        && key_exists('diskspace_free_gb',$ovzStatistics['guest']['fs0'])
        ){
            $value = $ovzStatistics['guest']['fs0']['diskspace_free_gb'];
            $status = MonLocalJobs::$STATENORMAL;
            if($value < $maxvalue){
                $status = MonLocalJobs::$STATEMAXIMAL;
            }elseif($value < $warnvalue){
                $status = MonLocalJobs::$STATEWARNING;
            }
            $valuestatus = new MonLocalValueStatus($value,$status);            
        }
        return $valuestatus;
    }
    
    /**
    * Returns a human readable string.
    * 
    * @param numeric $ovzStatistics JSON
    * @param numeric $warnvalue
    * @param numeric $maxvalue
    * @return string
    */
    public function genThresholdString($actvalue,$warnvalue,$maxvalue){
        $content = '';
        $content .= 'Value is now: '.Helpers::formatBytesHelper(Helpers::convertToBytes($actvalue.'GB')).'<br />';
        $content .= 'Warnvalue is: '.Helpers::formatBytesHelper(Helpers::convertToBytes($warnvalue.'GB')).'<br />';
        $content .= 'Maxvalue is: '.Helpers::formatBytesHelper(Helpers::convertToBytes($maxvalue.'GB')).'<br />';
        return $content;
    }
}
