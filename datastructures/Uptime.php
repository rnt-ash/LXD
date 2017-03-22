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

/**
* Helperclass to manage loose Uptime data for a single month (without relation to MonRemoteJob or YearMonth).
* Ensures that the values in this object are valid to eachother.
* 
*/
class Uptime{
    private $MaxSeconds;
    private $UpSeconds;
    private $UpPercentage;
    
    /**
    * Constructor for a uptime of a single month.
    * 
    * @param integer $maxSeconds 
    * @param integer $upSeconds
    * @param float $upPercentage 
    */
    public function __construct($maxSeconds,$upSeconds,$upPercentage){
        try{
            $maxSeconds = $this->validateMaxSeconds($maxSeconds);
            $this->MaxSeconds = $maxSeconds;
            
            $upSeconds = $this->validateUpSeconds($upSeconds);
            $this->UpSeconds = $upSeconds;
            
            $upPercentage = $this->validateUpPercentage($upPercentage);
            $this->UpPercentage = $upPercentage;
        }catch(\Exception $e){
            throw new \Exception("Uptime object could not been instantiated: ".$e->getMessage());
        }
    }
    
    private function validateMaxSeconds($maxSeconds){
        $maxSeconds = intval($maxSeconds);
        $secondsOfATwentyEightDayMonth = 2419200;
        $secondsOfAThirtyOneDayMonth = 2678400;
        if(!($maxSeconds >= $secondsOfATwentyEightDayMonth && $maxSeconds <= $secondsOfAThirtyOneDayMonth)){
            throw new \Exception("validateMaxSeconds failed. MaxSeconds is ".$maxSeconds." but should be between ".$secondsOfATwentyEightDayMonth." and ".$secondsOfAThirtyOneDayMonth." .");
        }
        return $maxSeconds;
    }
    
    private function validateUpSeconds($upSeconds){
        $upSeconds = intval($upSeconds);
        if(!($upSeconds <= $this->MaxSeconds)){
            throw new \Exception("validateUpSeconds failed. UpSeconds is ".$upSeconds." but should be smaller than MaxSeconds ".$this->MaxSeconds."\n");    
        }
        return $upSeconds;
    }
    
    private function validateUpPercentage($upPercentage){
        $upPercentage = floatval($upPercentage);
        if(!($upPercentage >= 0 && $upPercentage <= 1)){
            throw new \Exception("validateUpPercentage failed. UpPercentage is ".$upPercentage." but should be between 0 and 1.");    
        }
        return $upPercentage;
    }
    
    public function getMaxSeconds(){
        return $this->MaxSeconds;
    }
    
    public function getUpSeconds(){
        return $this->UpSeconds;
    }
    
    public function getUpPercentage(){
        return $this->UpPercentage;
    }
}
