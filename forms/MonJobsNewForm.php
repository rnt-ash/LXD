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

namespace RNTForest\ovz\forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;

use RNTForest\ovz\models\MonJobs;
use RNTForest\ovz\functions\Monitoring;

class MonJobsNewForm extends \RNTForest\core\forms\FormBase
{
    public function initialize($monJob = null, $options = array())
    {
        // server id
        $this->add(new Hidden("server_id"));

        // behaviors
        // get behaviors depending on server class
        if($monJob->getServerClass() == '\RNTForest\ovz\models\VirtualServers'){
            $serverType = 'virtual';
        }elseif($monJob->getServerClass() == '\RNTForest\ovz\models\PhysicalServers'){
            $serverType = 'physical';
        }
        
        // create new array for select
        $behaviorsSelect = array();
        $server = $monJob->getServerClass()::findFirst($monJob->getServerId());
        $allBehaviors = Monitoring::getAllBehaviors($serverType);
        foreach($allBehaviors as $key=>$behavior){
            // show MonLocal Jobs only if the physical server is ovz enabled 
            // or if the host of virtual server is ovz enabled
            if($server->getOvz() != 1 && strpos($key,'MonLocal')){
                if($serverType == 'virtual'){
                    if($server->PhysicalServers->getOvz() != 1) continue;
                }else{
                     continue;
                }
            }
            
            // save behavior in new array
            $behaviorsSelect[$key] = $behavior['shortname'];
        }
        
        $element = new Select(
            "mon_behavior",
            $behaviorsSelect,
            array("using"=>array("id","name"))
        );
        $message = self::translate("monitoring_monjobs_behavior");
        $element->setLabel($message);
        $element->setFilters(array('string'));
        $this->add($element);
        
        // get config
        $config = $this->getDI()->get('config');
        
        // check if default contacts are set in config
        $showContacts = true;
        if(key_exists('contacts',$config->monitoring) && empty($monJob->getMonContactsMessage() && empty($monJob->getMonContactsAlarm()))){
            $contacts = json_decode($config->monitoring['contacts'],true);
            if(key_exists('default',$contacts)){
                $showContacts = false;
            }
        }
        
        if($showContacts){
            // get all logins from the same customer as the logged in user
            $customerId = \Phalcon\Di::getDefault()->get("session")->get("auth")['customers_id'];
            $logins = \RNTForest\core\models\Logins::find(array("columns"=>"id,CONCAT(firstname, ' ', lastname, ' (',email, ')') as name","order"=>"name","customers_id = ".$customerId));
            
            // message contact
            $element = new Select(
                "mon_contacts_message",
                $logins,
                array("using"=>array("id","name"),
                    "multiple"   => true,
                    "name"       => "mon_contacts_message[]"
                )
            );
            $message = self::translate("monitoring_monjobs_message_contacts");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $message = self::translate("monitoring_monjobs_message_contacts_required");
            $element->addValidators(array(
                new \Phalcon\Validation\Validator\PresenceOf([
                    'message' => $message
                ]),
            ));
            $this->add($element);
            
            // alarm contact
            $element = new Select(
                "mon_contacts_alarm",
                $logins,
                array("using"=>array("id","name"),
                    "multiple"   => true,
                    "name"       => "mon_contacts_alarm[]"
                )
            );
            $message = self::translate("monitoring_monjobs_alarm_contacts");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $message = self::translate("monitoring_monjobs_alarm_contacts_required");
            $element->addValidators(array(
                new \Phalcon\Validation\Validator\PresenceOf([
                    'message' => $message
                ]),
            ));
            $this->add($element);
        }
    }
}