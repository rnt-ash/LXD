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

class ByteConverter
{
    public static function convertByteStringToBytes($byteString) { 
        // sanitize
        $byteString = trim($byteString);
        while (strpos($byteString,'  ')!==false){
            $byteString = str_replace('  ', ' ',$byteString);
        }
        if (empty($byteString)) throw new \Exception("Bytes are empty");
        // split
        $haystack = '';
        $temp = preg_split( '`([kKmMgGtT])`',$byteString, NULL, 
                            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                          );
        if (isset ($temp[1]))$haystack = $temp[1];
        $bytes = trim($temp[0]);
        // check if bytes only inlcude numbers
        if (!preg_match('`^([\d]*)(\.[\d]*)?$`',$bytes)){
            throw new \Exception("Bytes may only contain numbers");
        }
        // set exponent for 1024
        $pow = 0;
        if ($pos = stripos($haystack,'k')!== false)     $pow = 1;
        elseif ($pos = stripos($haystack,'m')!== false) $pow = 2;
        elseif ($pos = stripos($haystack,'g')!== false) $pow = 3;
        elseif ($pos = stripos($haystack,'t')!== false) $pow = 4;
        // calculate and return
        $factorForFloats = 1;
        while (!ctype_digit(strval($bytes))){
            if ($factorForFloats >1000000000){
                $bytes = intval($bytes);
                break;
            }
            $bytes *= 10;
            $factorForFloats *= 10;
        }
        $bytesAsString = gmp_strval(gmp_mul(strval($bytes),gmp_pow(1024,$pow)));    
        if ($factorForFloats > 1){
            $bytesAsString = gmp_strval(gmp_div($bytesAsString,strval($factorForFloats)));
        }
        return $bytesAsString;
    }
    
    public static function convertBytesToMibiBytes($bytes) {
        return gmp_strval(gmp_div($bytes,gmp_pow('1024','2')));
    }
    
    public static function convertBytesToGibiBytes($bytes) {
        return gmp_strval(gmp_div($bytes,gmp_pow('1024','3')));
    }
    
    /**
    * Returns a human readable string with Byte, KB, MB, ... to a given bytes number.
    * e.g. 123GB or 10MB
    * 
    * @param int $bytes
    * @return string
    */
    public static function getHumanReadableStringFromBytes($bytes){
        $result = $bytes.' Byte';
             
        if($bytes >= 1024 and $bytes < pow(1024, 2)) { 
            $result = round($bytes/1024, 2).' KB'; 
        }elseif($bytes >= pow(1024, 2) and $bytes < pow(1024, 3)) { 
            $result = round($bytes/pow(1024, 2), 2).' MB'; 
        }elseif($bytes >= pow(1024, 3) and $bytes < pow(1024, 4)) { 
            $result = round($bytes/pow(1024, 3), 2).' GB'; 
        }elseif($bytes >= pow(1024, 4) and $bytes < pow(1024, 5)) { 
            $result = round($bytes/pow(1024, 4), 2).' TB'; 
        }
        
        return $result;
    }
}
