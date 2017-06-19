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
use RNTForest\ovz\models\MonJobs;
use RNTForest\core\libraries\Helpers;

class DiskspacefreeMonLocalBehavior implements MonLocalBehaviorInterface{
    
    /**
    * Returns the Status and Value of a MonLocalLogs in use of the given arguments.
    * 
    * @param string $ovzStatistics JSON
    * @param string $monBehaviorParams JSON
    * @param numeric $warnvalue
    * @param numeric $maxvalue
    * @return \RNTForest\ovz\utilities\MonLocalValueStatus
    */
    public function execute($ovzStatistics,$monBehaviorParams,$warnvalue,$maxvalue){                
        $valuestatus = null;
        $ovzStatistics = json_decode($ovzStatistics,true);
        $monBehaviorParams = json_decode($monBehaviorParams,true);
        try{
            $value = Helpers::getSubPartOfArray($monBehaviorParams,$ovzStatistics);    
            $status = MonJobs::$LOCAL_STATENORMAL;
            if($value < $maxvalue){
                $status = MonJobs::$LOCAL_STATEMAXIMAL;
            }elseif($value < $warnvalue){
                $status = MonJobs::$LOCAL_STATEWARNING;
            }
            $valuestatus = new MonLocalValueStatus($value,$status);            

        }catch(\Exception $e){
            $this->getLogger()->error('Problem while getting value of ovzStatistics: '.$e->getMessage());        
        }
        
        return $valuestatus;
    }
    
    /**
    * Returns a human readable string.
    * 
    * @param numeric $ovzStatistics JSON
    * @param numeric $warnvalue
    * @param numeric $maxvalue
    * @param string $monBehaviorParams JSON
    * @return string
    */
    public function genThresholdString($actvalue,$warnvalue,$maxvalue,$monBehaviorParams){
        $content = '';
        $content .= 'MonBehaviorParams: '.$monBehaviorParams.'<br />';
        $content .= 'Value is now: '.Helpers::formatBytesHelper(Helpers::convertToBytes($actvalue.'GB')).'<br />';
        $content .= 'Warnvalue is: '.Helpers::formatBytesHelper(Helpers::convertToBytes($warnvalue.'GB')).'<br />';
        $content .= 'Maxvalue is: '.Helpers::formatBytesHelper(Helpers::convertToBytes($maxvalue.'GB')).'<br />';
        return $content;
    }
    
    /**
    * @return \Phalcon\Logger\AdapterInterface
    */
    private function getLogger(){
        return  \Phalcon\Di::getDefault()->getShared('logger');
    }
}
