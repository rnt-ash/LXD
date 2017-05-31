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

use RNTForest\ovz\models\PhysicalServers;
use RNTForest\ovz\models\VirtualServers;
use RNTForest\core\services\Push;
use RNTForest\core\libraries\Helpers;

class AllInfoUpdater{
    
    /**
    * Updates the ovz_statistics and ovz_settings from all Servers.
    * Sends a Job to all Physicals which gets all data and saves it to the model.
    * 
    */
    public static function updateAllServers(){
        $physicals = PhysicalServers::find('ovz = 1');
        foreach($physicals as $physical){
            AllInfoUpdater::getLogger()->debug('start for physical '.$physical->getName());
            try{
                if(AllInfoUpdater::isStatisticTimestampToOld($physical)){
                    AllInfoUpdater::updatePhysical($physical);
                }else{
                    AllInfoUpdater::getLogger()->debug('statistics timestamp of '.$physical->getName().' not old enough to make an update');
                }
            }catch(\Exception $e){
                // catch Exceptions separate so that problem with one physical does not have impact to the rest
                AllInfoUpdater::getLogger()->error('Error while updatePhysical '.$physical->getName().': '.$e->getMessage());
            }
            AllInfoUpdater::getLogger()->debug('end for physical '.$physical->getName());
        }
    }
    
    /**
    * 
    * @param PhysicalServers $physicalServer
    * @return boolean
    */
    private static function isStatisticTimestampToOld(PhysicalServers $physicalServer){
        // per default is timestamp to old, so if no statistic is present or statistics are not in correct format...
        $result = true;
        
        $statistics = json_decode($physicalServer->getOvzStatistics(),true);
        if(is_array($statistics)
        && key_exists('Timestamp',$statistics)
        ){
            $statisticsUnixTimestamp = Helpers::createUnixTimestampFromDateTime($statistics['Timestamp']);    
            $nowUnixTimestamp = time();
            $tenMinutesInSeconds = 10*60;
            
            $result = (($statisticsUnixTimestamp + $tenMinutesInSeconds) < $nowUnixTimestamp);
        }
        
        return $result;
    }
    
    private static function updatePhysical(PhysicalServers $physicalServer){
        // execute ovz_all_info job        
        $push = AllInfoUpdater::getPushService();
        
        $beforeJob = microtime(true);
        $job = $push->executeJob($physicalServer,'ovz_all_info',array());
        $durationJob = (microtime(true))-$beforeJob;
        if($durationJob > 5){
            AllInfoUpdater::getLogger()->warning('duration of for job '.$durationJob.' seconds. this seems to be very long.');
        }else{
            AllInfoUpdater::getLogger()->debug('duration of for job '.$durationJob.' seconds');
        }
        
        $beforeUpdate = microtime(true);
        $message = AllInfoUpdater::translate("physicalserver_job_failed");
        if(!$job) throw new \Exception($message."(ovz_all_info) !");
        // mark Job as failed and write error if it was not sent to prevent accumulation of needless jobs (a new one is created every time)
        if($job->getSent() == '0000-00-00 00:00:00'){
            $job->setDone(2);
            $job->setError(AllInfoUpdater::translate('monitoring_allinfoupdater_mark_failed'));
            $job->save();
            throw new \Exception($message."(ovz_all_info) !");
        }
        
        // get infos as array
        $infos = $job->getRetval(true);
        $message = AllInfoUpdater::translate("physicalserver_info_not_valid_array");
        if(!is_array($infos)) throw new \Exception($message);

        // save host settings and statistics
        $physicalServer->setOvzSettings(json_encode($infos['HostInfo']));
        $physicalServer->setOvzStatistics(json_encode($infos['HostStatistics']));
        if ($physicalServer->save() === false) {
            $message = AllInfoUpdater::translate("physicalserver_update_failed");
            throw new \Exception($message . $physicalServer->getName());
        }

        // save guest settings and statistics
        foreach($infos['GuestInfo'] as $key=>$info){
            // find virtual server
            $virtualServer = VirtualServers::findFirst("ovz_uuid = '".$key."'");
            if (!$virtualServer){
                $message = AllInfoUpdater::translate("virtualserver_does_not_exist");
                AllInfoUpdater::getLogger()->warning($message . "UUID: " . $key);   
            }else{
                $virtualServer->setOvzSettings(json_encode($infos['GuestInfo'][$key]));
                $virtualServer->setOvzStatistics(json_encode($infos['GuestStatistics'][$key]));
                if ($virtualServer->save() === false) {
                    $message = AllInfoUpdater::translate("virtualserver_update_failed");
                    throw new \Exception($message . $virtualServer->getName());
                }
            }
        }
        $durationUpdate = (microtime(true))-$beforeUpdate;
        AllInfoUpdater::getLogger()->debug('duration of for update '.$durationUpdate.' seconds');
        
        // delete Job if it was successful to cleanup db
        if($job->getDone() == 1){
            $job->delete();
        }
    }
    
    /**
    * @return \Phalcon\Logger\AdapterInterface
    */
    private static function getLogger(){
        return  \Phalcon\Di::getDefault()->getShared('logger');
    }
    
    private static function translate($token,$params=array()){
        return \Phalcon\Di::getDefault()->getShared('translate')->_($token,$params);
    }
    
    private static function getModelManager(){
        return \Phalcon\Di::getDefault()->get('modelsManager');
    } 
    
    /**
    * @return \RNTForest\core\services\Push
    */
    private static function getPushService(){
        return \Phalcon\Di::getDefault()->get('push');
    }
}
