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

namespace RNTFOREST\OVZJOB\general\httpservice;

class JWT {
    
    /**
     * Decodes a JWSToken string into an object.
     *
     * @param string $jwsToken 
     * @param string $key symmetrical secret key
     * @return object payload as an object
     * @throws Exception
     */
    public static function decode($jwsToken, $key){
        $parts = explode('.', $jwsToken);
        
        if (count($parts) != 3) {
            throw new \Exception('Wrong number of parts');
        }
        list($headerBase64, $payloadBase64, $signatureBase64) = $parts;
        if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headerBase64)))) {
            throw new \Exception('Invalid segment encoding in Header: '.$header);
        }
        if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($payloadBase64))) {
            throw new \Exception('Invalid segment encoding in Payload: '.$payloadBase64);
        }
        $sig = JWT::urlsafeB64Decode($signatureBase64);
        if (empty($header->alg)) {
            throw new \Exception('No algorithm in JoseHeader');
        }
        if ($sig != JWT::sign("$headerBase64.$payloadBase64", $key, $header->alg)) {
            throw new \Exception('JwsSignature verification failed. Wrong key or JwsToken was altered during transport.');
        }
        
        return $payload;
    }

    /**
     * Converts and signs an object or array into a JWT string.
     *
     * @param object|array $josePayload bject or array
     * @param string $key symmetrical secret key
     * @return string signed JWS token
     */
    public static function encode($josePayload, $key){
        $alg = 'HS256';
        $joseHeader = array('typ' => 'JWT', 'alg' => $alg);

        $parts = array();
        $parts[] = JWT::urlsafeB64Encode(JWT::jsonEncode($joseHeader));
        $parts[] = JWT::urlsafeB64Encode(JWT::jsonEncode($josePayload));
        $joseHeaderAndPayload = implode('.', $parts);
        $jwsSignature = JWT::sign($joseHeaderAndPayload, $key, $alg);
        $parts[] = JWT::urlsafeB64Encode($jwsSignature);

        return implode('.', $parts);
    }
    
    /**
    * Decrypts a JWEToken string into an object.
    * 
    * @param string $jweToken
    * @param string $privKey asymmetrical private key
    * @return object payload as an object
    * @throws Exception
    */
    public static function decrypt($jweToken, $privKey){
        throw new \Exception('not implemented');
    }
    
    /**
    * Encrypts an object or array into a JWE string.
    * 
    * @param string $josePayload
    * @param string $publicKey asymmetrical public key of recipient
    * @return string encrypted JWE token
    * @throws Exception
    */
    public static function encrypt($josePayload, $publicKey){
        throw new \Exception('not implemented');
    }

    /**
     * Sign a string with a given symmetrical secret key and algorithm like HS256, HS384 or HS512.
     *
     * @param string $msg message to sign
     * @param string $key symmetrical secret key
     * @param string $method signing algorithm
     * @return string symmetrical encrypted message
     * @throws Exception
     */
    public static function sign($msg, $key, $method = 'HS256'){
        $methods = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        
        if (empty($methods[$method])) {
            throw new \Exception('Algorithm not supported');
        }
        
        return hash_hmac($methods[$method], $msg, $key, true);
    }

    /**
     * Decodes a JSON string into an object.
     *
     * @param string $input JSON
     * @return decoded object
     * @throws Exception
     */
    public static function jsonDecode($jsonInput){
        $object = json_decode($jsonInput);
        
        if (function_exists('json_last_error') && $errorNumber = json_last_error()) {
            JWT::_handleJsonError($errorNumber);
        }elseif($object === null && $jsonInput !== 'null') {
            throw new \Exception('Null result with non-null input');
        }
        
        return $object;
    }

    /**
     * Encode an object into a JSON string.
     *
     * @param object|array $input
     * @return string encoded JSON
     * @throws Exception
     */
    public static function jsonEncode($input){
        $json = json_encode($input);
        
        if (function_exists('json_last_error') && $errorNumber = json_last_error()) {
            JWT::_handleJsonError($errorNumber);
        }elseif($json === 'null' && $input !== null) {
            throw new \Exception('Null result with non-null input');
        }
        
        return $json;
    }

    /**
     * Decode a string with URL safe Base64.
     * Translate:
     * - to +
     * _ to /
     * fill up with '=' to match string lenght with mod 4 == 0
     *
     * @param string $input base64 encoded string
     * @return string decoded string
     */
    public static function urlsafeB64Decode($input){
        $remainder = strlen($input) % 4;
        
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Encode a string with URL safe Base64.
     * Translate:
     * + to -
     * / to _
     * = will be removed
     *
     * @param string $input string
     * @return string base64 encoded string
     */
    public static function urlsafeB64Encode($input){
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * Helper method to create a JSON error from PHP Function json_last_error()
     * View http://php.net/manual/en/function.json-last-error.php for further information.
     *
     * @param int $errorNumber
     */
    private static function _handleJsonError($errorNumber){
        // mapping of official error constants
        $errorMessages = array(
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',  
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
        );
        
        if(isset($errorMessages[$errorNumber])){
            $error = $errorMessages[$errorNumber];
        }else{
            $error = 'Unknown JSON error: ' . $errorNumber;
        }
        
        throw new \Exception($error);
    }
}
