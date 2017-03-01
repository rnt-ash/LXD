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
namespace RNTForest\ovz\libraries;

class DateConverter
{
    /**
    * Creates a unix timestamp from a given date-time value.
    * e.g. a datetime value of this format "2016-09-09 08:10:55"
    * 
    * @param string $dateTime in format Y-m-d H:i:s
    * @return int unixTimestamp
    */
    public static function createUnixTimestampFromDateTime($dateTime){
        list($date, $time) = explode(' ', $dateTime);
        list($year, $month, $day) = explode('-', $date);
        list($hour, $minute, $second) = explode(':', $time);

        $timestamp = mktime($hour, $minute, $second, $month, $day, $year); 
        return $timestamp;
    }
    
    /**
    * Creates a date-time value from a given unix timestamp.
    * 
    * @param int $unixTimestamp
    * @return string $dateTime in format Y-m-d H:i:s
    */
    public static function createDateTimeFromUnixTimestamp($unixTimestamp){
        return date("Y-m-d H:i:s", $unixTimestamp);
    }
}
