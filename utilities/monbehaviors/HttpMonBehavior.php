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

use RNTForest\ovz\interfaces\MonBehaviorInterface;

class HttpMonBehavior implements MonBehaviorInterface{
    private $port = 80;
    private $timeout_in_seconds = 6;
    
    public function execute($target){                
        $status = "0";
        if(is_null($target)){
            $host = "nohost";      
        }else{
            $hots = $target;
        }
        $fp = @fsockopen($host, $this->port, $errno, $errstr, $this->timeout_in_seconds);
        
        if ($fp) {
            $header = "GET / HTTP/1.1\r\n";
            $header .= "Host: $host\r\n";
            $header .= "Connection: close\r\n\r\n";
            fputs($fp, $header);
            $str = "";
            while (!feof($fp)) {
                $str .= fgets($fp, 1024);
            }
            fclose($fp);
            if (strpos($str, "HTTP/1.1 200 OK") !== false) {
                $status = "1";
            } 
        }
        return $status;    
    }
}