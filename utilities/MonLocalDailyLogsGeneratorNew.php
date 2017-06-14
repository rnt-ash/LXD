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
use RNTForest\ovz\models\MonLocalDailyLogs;
use RNTForest\core\libraries\Helpers;

class MonLocalDailyLogsGeneratorNew{
    
    /**
    * Generates the MonLocalDailyLogs from old MonLocalLogs of a given MonLocalJobs.
    * Hereby the MonLocalLogs will be fold and cleaned.
    * 
    * @param MonJobs $monJob
    */
    public static function genLocalDailyLogs(MonJobs $monJob){
        if($monJob->getMonType() != 'remote') throw new \Exception($this->translate('monitoring_monjobs_montype_remote_expected'));
        
        MonLocalDailyLogsGenerator::getLogger()->debug("start genLocalDailyLogs");
        
        // get all relevant MonLocalLogs and sort them
        $preMonthStart = date("Y-m-d H:i:s", strtotime("first day of last month midnight"));
        $monLogs = MonLocalLogs::find(
        [
             "mon_local_jobs_id = :id: AND modified < :premonthstart:",
             "bind" => [
                "id" => $monJob->getId(),
                "premonthstart" => $preMonthStart,
             ],    
        ]
        );

        $splittedMonLogs = array();
        $splittedMonLogs = MonLocalDailyLogsGenerator::sortAndSplitMonLogsByDay($monLogs);
        
        // compute daily average from the logs
        $dayAverages = array();
        foreach($splittedMonLogs as $day => $sortedMonLogs){
            $dayAverages[$day] = MonLocalDailyLogsGenerator::computeAverage($day,$sortedMonLogs);
        }
        
        // filter $dayAverages so that only valide will be created (e.g. throw null away)
        // normally only 1 average is in $dayAverages, because it will be created monthly
        $cleanedAverages = array();
        foreach($dayAverages as $day => $average){
            MonLocalDailyLogsGenerator::getLogger()->debug($day." ".$average);
        
            if($average !== null){
                $cleanedAverages[$day] = $average;    
            }
        }

        foreach($cleanedAverages as $day => $average){
            // delete existing MonLocalDailyLogs of this $monJob with this day first
            $oldMonLocalDailyLog = MonLocalDailyLogs::findFirst(
            [
                "mon_local_jobs_id = :id: and day = :day:",
                "bind" => [
                    "id" => $monJob->getId(),
                    "day" => $day,
                ]
            ]
            );
            if ($oldMonLocalDailyLog !== false) {
                MonLocalDailyLogsGenerator::getLogger()->notice(MonLocalDailyLogsGenerator::translate("monitoring_monlocaldailylogsgenerator_delete_old_daily_log").$day." MonJob ".$monJob->getId()); 
                if ($oldMonLocalDailyLog->delete() === false) {
                    // Log?
                }
            }

            // create the new MonLocalDailyLog entry
            $monLocalDailyLog = new MonLocalDailyLogs();
            $monLocalDailyLog->create([
                "mon_local_jobs_id" => $monJob->getId(),
                "day" => $day,
                "value" => $average,  
            ]);
            $monLocalDailyLog->save();
            
            // delete MonLocalLogs of this $monJob and $day
            $modelManager = MonLocalDailyLogsGenerator::getModelManager();
            $endLog = $modelManager->executeQuery(
                "DELETE FROM \\RNTForest\\ovz\\models\\MonLocalLogs ".
                    " WHERE mon_local_jobs_id = :id: ".
                    " AND modified BETWEEN :daystart: AND :dayend: ",
                [
                    "id" => $monJob->getId(),
                    "daystart" => $day.' 00:00:00',
                    "dayend" => $day.' 23:59:59',
                ]
            );
        }
    }
    
    /**
    * Sorts and splits up the MonRemoteLogs on days.
    * Memoryintense part if a lot of logs.
    * 
    * @param \RNTForest\ovz\models\MonRemoteLogs[] $monLogs
    * @return \RNTForest\ovz\models\MonRemoteLogs[][] $monLogs
    */
    private static function sortAndSplitMonLogsByDay($monLogs){
        // sort on modified
        $sortedMonLogs = array();
        foreach($monLogs as $monLog){
            if($monLog instanceof MonLocalLogs){
                $sortedMonLogs[$monLog->getModified()] = $monLog;
            }
        }
        ksort($sortedMonLogs);
        // split up on day
        $splittedMonLogs = array();
        foreach($sortedMonLogs as $datetime => $monLog){
            $day = Helpers::createDateFromDateTime($datetime);
            $splittedMonLogs[$day][] = $monLog;
        }
        return $splittedMonLogs;
    }
    
    /**
    * Computes the average of a given day.
    * 
    * @param string $day
    * @param \RNTForest\ovz\models\MonRemoteLogs[] $sortedMonLogs
    * @return Uptime
    */
    private static function computeAverage($day,$sortedMonLogs){
        $average = 0;
        try{
            $sum = 0;
            $count = 0;
            foreach($sortedMonLogs as $monLog){
                if(is_numeric($monLog->getValue())){
                    $sum += $monLog->getValue();
                    $count++;
                }
            }
            $average = $sum/$count;
        }catch(\Exception $e){
            MonLocalDailyLogsGenerator::getLogger()->error(MonLocalDailyLogsGenerator::translate("monitoring_monlocaldailylogsgenerator_computefailed").$e->getMessage());
        }
        return $average;    
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
