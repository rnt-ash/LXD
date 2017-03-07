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

use RNTForest\ovz\interfaces\SendBehaviorInterface;

class MailSendBehavior implements SendBehaviorInterface{
    
    /**
    * Sends a mail to a recipient.
    * 
    * @param string $recipient
    * @param string $subject
    * @param string $message
    * @return boolean
    */
    public function send($recipient,$subject,$message){
        $header  = 'MIME-Version: 1.0' . "\r\n";
        $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $header .= 'From: EAT AlarmingSystem <alarm@admin.exenti.ch>' . "\r\n";
        
        return mail($recipient,$subject,$message,$header);
    }
}