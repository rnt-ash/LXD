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

use RNTForest\ovz\models\PhysicalServers;

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
}
?>