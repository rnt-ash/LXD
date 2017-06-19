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

use RNTForest\ovz\models\MonJobs;
use RNTForest\ovz\models\MonLogs;
use RNTForest\ovz\models\MonUptimes;
use RNTForest\ovz\datastructures\Uptime;
use RNTForest\core\libraries\Helpers;

class MonUptimesGenerator{
    
    /**
    * Generates the MonUptimes from old MonLogs of a given remote MonJobs object.
    * Hereby the remote MonLogs will be fold and cleaned.
    * 
    * remote only
    * 
    * @param MonJobs $monJob
    */
    public static function genMonUptime(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
        MonUptimesGenerator::getLogger()->debug("start genMonUptime");
        
        // get all relevant MonRemoteLogs and sort them
        $preMonthStart = date("Y-m-d H:i:s", strtotime("first day of last month midnight"));
        $monLogs = MonLogs::find(
        [
             "mon_jobs_id = :id: AND modified < :premonthstart:",
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
            MonUptimesGenerator::getLogger()->debug('yearMonth: '.$yearMonth);
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
                "mon_jobs_id = :id: and year_month = :yearmonth:",
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
                "mon_jobs_id" => $monJob->getId(),
                "year_month" => $yearMonth,
                "max_seconds" => $uptime->getMaxSeconds(),
                "up_seconds" => $uptime->getUpSeconds(),
                "up_percentage" => $uptime->getUpPercentage(),  
            ]);
            $monUptime->save();
            
            // delete MonLogs of this $monJob and $yearMonth
            $monthStart = MonUptimesGenerator::genMonthStartByYearMonth($yearMonth);
            $monthEnd = MonUptimesGenerator::genMonthEndByYearMonth($yearMonth);
            
            $modelManager = MonUptimesGenerator::getModelManager();
            $endLog = $modelManager->executeQuery(
                "DELETE FROM \\RNTForest\\ovz\\models\\MonLogs ".
                    " WHERE mon_jobs_id = :id: ".
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
    * Sorts and splits up the MonLogs on YearMonths.
    * Memoryintense part if a lot of logs.
    * 
    * @param MonLogs[] $monLogs
    * @return MonLogs[][] $monLogs
    */
    private static function sortAndSplitMonLogs($monLogs){
        // sort on modified
        $sortedMonLogs = array();
        foreach($monLogs as $monLog){
            if($monLog instanceof MonLogs){
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
    * @param MonLogs[] $sortedMonLogs
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
    * @param MonLogs[] $sortedMonLogs
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
            if($monLog instanceof MonLogs){
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
            $downTimeInSeconds += (strtotime('+1 month', strtotime('first day of '.$year.'-'.($month).' midnight'))-1)-Helpers::createUnixTimestampFromDateTime($startDowntime);
        }
        // if the whole month was 0, then the whole month is one downtime...
        if($endDowntime = ''){
            $downTimeInSeconds += (strtotime('+1 month', strtotime('first day of '.$year.'-'.($month).' midnight'))-1)-Helpers::createUnixTimestampFromDateTime($startDowntime);
        }
        
        return $downTimeInSeconds;                
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
