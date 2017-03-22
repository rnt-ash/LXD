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

namespace RNTForest\ovz\utilities;

use RNTForest\ovz\models\MonRemoteJobs;
use RNTForest\ovz\models\MonRemoteLogs;
use RNTForest\ovz\models\MonUptimes;
use RNTForest\ovz\datastructures\Uptime;
use RNTForest\core\libraries\Helpers;

class MonUptimesGenerator{
    
    /**
    * Generates the MonUptimes from old MonRemoteLogs of a given MonRemoteJobs.
    * Hereby the MonRemoteLogs will be fold and cleaned.
    * 
    * @param MonRemoteJobs $monJob
    */
    public static function genMonUptime(MonRemoteJobs $monJob){
        MonUptimesGenerator::getLogger()->debug("start genMonUptime");
        $persistedMonUptimes = MonUptimes::find(
            [
                "mon_remote_jobs_id = :id:",
                "bind" => [
                    "id" => $monJob->getId(),
                ],
            ]
        );
        
        // get all relevant MonRemoteLogs and sort them
        $preMonthStart = date("Y-m-d H:i:s", strtotime("first day of last month midnight"));
        $monLogs = MonRemoteLogs::find(
        [
             "mon_remote_jobs_id = :id: AND modified < :premonthstart:",
             "bind" => [
                "id" => $monJob->getId(),
                "premonthstart" => $preMonthStart,
             ],    
        ]
        );

        $splittedMonLogs = array();
        $splittedMonLogs = MonUptimesGenerator::sortAndSplitMonLogs($monLogs);
        
        // compute uptimes from the logs
        $computedUptimes = array();
        foreach($splittedMonLogs as $yearMonth => $sortedMonLogs){
            $computedUptimes[$yearMonth] = MonUptimesGenerator::computeUptime($yearMonth,$sortedMonLogs);
        }
        
        // filter $computedUptimes so that only valide will be created (e.g. throw null away)
        // normally only 1 uptime is in $computedUptimes, because it will be created monthly
        $cleanedUptimes = array();
        foreach($computedUptimes as $yearMonth => $uptime){
            if($uptime != null){
                $cleanedUptimes[$yearMonth] = $uptime;    
            }
        }

        foreach($cleanedUptimes as $yearMonth => $uptime){
            // delete existing MonUptimes of this $monJob with this $yearMonth first
            $oldMonUptime = MonUptimes::findFirst(
            [
                "mon_remote_jobs_id = :id: and year_month = :yearmonth:",
                "bind" => [
                    "id" => $monJob->getId(),
                    "yearmonth" => $yearMonth,
                ]
            ]
            );
            if ($oldMonUptime !== false) {
                 if ($oldMonUptime->delete() === false) {
                    // Log?
                 }
            }

            // create the new MonUptime entry
            $monUptime = new MonUptimes();
            $monUptime->create([
                "mon_remote_jobs_id" => $monJob->getId(),
                "year_month" => $yearMonth,
                "max_seconds" => $uptime->getMaxSeconds(),
                "up_seconds" => $uptime->getUpSeconds(),
                "up_percentage" => $uptime->getUpPercentage(),  
            ]);
            $monUptime->save();
            
            // delete MonRemoteLogs of this $monJob and $yearMonth
            $monthStart = MonUptimesGenerator::genMonthStartByYearMonth($yearMonth);
            $monthEnd = MonUptimesGenerator::genMonthEndByYearMonth($yearMonth);
            
            $modelManager = MonUptimesGenerator::getModelManager();
            $endLog = $modelManager->executeQuery(
                "DELETE FROM \\RNTForest\\ovz\\models\\MonRemoteLogs ".
                    " WHERE mon_remote_jobs_id = :id: ".
                    " AND modified BETWEEN :monthstart: AND :monthend: ",
                [
                    "id" => $monJob->getId(),
                    "monthstart" => $monthStart,
                    "monthend" => $monthEnd,
                ]
            );
        }
    }
    
    /**
    * Sorts and splits up the MonRemoteLogs on YearMonths.
    * Memoryintense part if a lot of logs.
    * 
    * @param \RNTForest\ovz\models\MonRemoteLogs[] $monLogs
    * @return \RNTForest\ovz\models\MonRemoteLogs[][] $monLogs
    */
    private static function sortAndSplitMonLogs($monLogs){
        // sort on modified
        $sortedMonLogs = array();
        foreach($monLogs as $monLog){
            if($monLog instanceof MonRemoteLogs){
                $sortedMonLogs[$monLog->getModified()] = $monLog;
            }
        }
        ksort($sortedMonLogs);
        // split up on YearMonth
        $splittedMonLogs = array();
        foreach($sortedMonLogs as $datetime => $monLog){
            $yearMonth = date("Ym",Helpers::createUnixTimestampFromDateTime($datetime));
            $splittedMonLogs[$yearMonth][] = $monLog;
        }
        return $splittedMonLogs;
    }
    
    /**
    * Computes the uptime of a given YearMonth.
    * 
    * @param string $yearMonth
    * @param \RNTForest\ovz\models\MonRemoteLogs[] $sortedMonLogs
    * @return Uptime
    */
    private static function computeUptime($yearMonth,$sortedMonLogs){
        $uptime = null;
        try{
            if(strlen($yearMonth) == 6){
                $year = substr($yearMonth,0,4);
                $month = substr($yearMonth,4,2);
                $days = date('j',strtotime('last day of '.$year.'-'.$month));
                $maxSeconds = $days * 24 * 60 * 60;
                
                $downTimeInSeconds = MonUptimesGenerator::computeDowntimeInSeconds($yearMonth,$sortedMonLogs);
                $upSeconds = $maxSeconds - $downTimeInSeconds;
                
                $upPercentage = $upSeconds / $maxSeconds;
                
                $uptime = new Uptime($maxSeconds, $upSeconds, $upPercentage);
            
            }    
        }catch(\Exception $e){
            MonUptimesGenerator::getLogger()->error(MonUptimesGenerator::translate("monitoring_monuptimesgenerator_computefailed").$e->getMessage());
        }
        return $uptime;    
    }
    
    /**
    * Computes the downtime in seconds of a YearMonth.
    * 
    * @param string $yearMonth
    * @param \RNTForest\ovz\models\MonRemoteLogs[] $sortedMonLogs
    * @return int
    */
    private static function computeDowntimeInSeconds($yearMonth,$sortedMonLogs){
        // optimistic, begin with status 'up'
        $lastValue = '1';
        // set downtime initially on start of month
        // because if the first log would be 'down' then no startdowntime would exist
        $year = substr($yearMonth,0,4);
        $month = substr($yearMonth,4,2);
        $startDowntime = Helpers::createDateTimeFromUnixTimestamp(strtotime('first day of '.$year.'-'.$month.' midnight'));
        $endDowntime = '';
        $downTimeInSeconds = 0;
        foreach($sortedMonLogs as $monLog){
            if($monLog instanceof MonLog){
                $curValue = $monLog->getValue();
                // Negative statuschange
                if($curValue == 0 && $lastValue == 1){
                    $startDowntime = $monLog->getModified();
                }
                // Positive statuschange
                if($curValue == 1 && $lastValue == 0){
                    $endDowntime = $monLog->getModified();
                    $downTimeInSeconds += Helpers::createUnixTimestampFromDateTime($endDowntime)-Helpers::createUnixTimestampFromDateTime($startDowntime);
                }
                $lastValue = $curValue;
            }
        }
        // if the last log is not up, then the downtime reaches until the end of the month
        if($lastValue == 0){
            $downTimeInSeconds += (strtotime('+1 month', strtotime('first day of '.$year.'-'.($month).' midnight'))-1)-DateConverter::createUnixTimestampFromDateTime($startDowntime);
        }
        // if the whole month was 0, then the whole month is one downtime...
        if($endDowntime = ''){
            $downTimeInSeconds += (strtotime('+1 month', strtotime('first day of '.$year.'-'.($month).' midnight'))-1)-DateConverter::createUnixTimestampFromDateTime($startDowntime);
        }
        
        return $downTimeInSeconds;                
    }
    
    /**
    * Trys to give an existing MonUptime to a given YearMonth. 
    * If none exists, false is given.
    * 
    * @param string $yearMonth
    * @param Uptime $uptime
    * @param \RNTForest\ovz\models\MonUptimes[] $monUptimes
    * @return \RNTForest\ovz\models\MonUptimes or false
    */
    private static function getExistingMonUptime($yearMonth,Uptime $uptime,$monUptimes){
        $found = false;
        foreach($monUptimes as $monUptime){
            if($monUptime instanceof MonUptime){
                // does it already exists?
                if($monUptime->getYearMonth() == $yearMonth){
                    // has it changed? currently only needed for logging reasons
                    if($monUptime->getMaxSeconds() == $uptime->getMaxSeconds()
                    && $monUptime->getUpSeconds() == $uptime->getUpSeconds()
                    && $monUptime->getUpPercentage() == $uptime->getUpPercentage()){
                        MonUptimesGenerator::getLogger()->notice(MonUptimesGenerator::translate("monitoring_monuptimesgenerator_uptimealreadyexists").$yearMonth);
                    }else{
                        MonUptimesGenerator::getLogger()->notice(MonUptimesGenerator::translate("monitoring_monuptimesgenerator_uptimealreadyexistsdifferent").$yearMonth);
                    }
                    $found = $monUptime;    
                }   
            }
        }
        return $found;
    }
    
    private static function genMonthStartByYearMonth($yearMonth){
        $year = substr($yearMonth,0,4);
        $month = substr($yearMonth,4,2);
        return date("Y-m-d H:i:s", strtotime('first day of '.$year.'-'.$month.' midnight'));
    }
    
    private static function genMonthEndByYearMonth($yearMonth){
        $year = substr($yearMonth,0,4);
        $month = substr($yearMonth,4,2);
        return date("Y-m-d H:i:s", strtotime('+1 month', strtotime('first day of '.$year.'-'.($month).' midnight'))-1);
    }
    
    /**
    * @return \Phalcon\Logger\AdapterInterface
    */
    private static function getLogger(){
        return  \Phalcon\Di::getDefault()->getShared('logger');
    }
    
    private function translate($token,$params=array()){
        return \Phalcon\Di::getDefault()->getShared('translate')->_($token,$params);
    }
    
    private function getModelManager(){
        return \Phalcon\Di::getDefault()->get('modelsManager');
    }
}
