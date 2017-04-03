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
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Validation\Validator\Digit as DigitValidator;

use RNTForest\ovz\models\MonLocalJobs;
use RNTForest\core\models\Logins;
use RNTForest\ovz\functions\Monitoring;

class MonLocalJobsForm extends \RNTForest\core\forms\FormBase
{
    public function initialize($monLocalJob = null, $options = array())
    {
        // server id
        $this->add(new Hidden("server_id"));

        // get behaviors and customer id of the server
        if (strpos($monLocalJob->getServerClass(), 'Physical') !== false) {
            $behaviors = Monitoring::getLocalPhysicalBehaviors();
        }elseif(strpos($monLocalJob->getServerClass(), 'Virtual') !== false){
            $behaviors = Monitoring::getLocalVirtualBehaviors();
        }
        $server = $monLocalJob->getServerClass()::findFirstById($monLocalJob->getServerId());
        $serverCustomerId = $server->getCustomersId();
        
        // behavior
        $message = self::translate("monitoring_monlocaljobs_choose_behavior");
        $element = new Select(
            "mon_behavior_class",
            $behaviors,
            array("using"=>array("id","name"))
        );
        $message = self::translate("monitoring_monlocaljobs_behavior");
        $element->setLabel($message);
        $element->setFilters(array('string'));
        $message = self::translate("monitoring_monlocaljobs_behavior_required");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message
            ]),
        ));
        $this->add($element);
        
        // period
        $element = new Numeric("period");
        $message = self::translate("monitoring_monlocaljobs_period");
        $element->setLabel($message);
        $message = $this->translate("monitoring_monlocaljobs_period_placeholder");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('int'));
        $message1 = self::translate("monitoring_monlocaljobs_period_required");
        $message2 = self::translate("monitoring_monlocaljobs_period_digit");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message1
            ]),
            new DigitValidator([
                'message' => $message2
            ])
        ));
        $this->add($element);
        
        // alarm period
        $element = new Numeric("alarm_period");
        $message = self::translate("monitoring_monlocaljobs_alarm_period");
        $element->setLabel($message);
        $message = $this->translate("monitoring_monlocaljobs_alarm_period_placeholder");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('int'));
        $message1 = self::translate("monitoring_monlocaljobs_alarm_period_required");
        $message2 = self::translate("monitoring_monlocaljobs_alarm_period_digit");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message1
            ]),
            new DigitValidator([
                'message' => $message2
            ])                       
        ));
        $this->add($element);
        
        // message contact
        $logins = Logins::find(array("columns"=>"id,CONCAT(firstname, ' ', lastname, ' (',email, ')') as name","order"=>"name","customers_id = ".$serverCustomerId));
        $message = self::translate("monitoring_monlocaljobs_choose_message_contacts");
        $element = new Select(
            "mon_contacts_message",
            $logins,
            array("using"=>array("id","name"),
                "multiple"   => true,
                "name"       => "mon_contacts_message[]"
            )
        );
        $message = self::translate("monitoring_monlocaljobs_message_contacts");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $message = self::translate("monitoring_monlocaljobs_message_contacts_required");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message
            ]),
        ));
        $this->add($element);
        
        // alarm contact
        $message = self::translate("monitoring_monlocaljobs_choose_alarm_contacts");
        $element = new Select(
            "mon_contacts_alarm",
            $logins,
            array("using"=>array("id","name"),
                "multiple"   => true,
                "name"       => "mon_contacts_alarm[]"
            )
        );
        $message = self::translate("monitoring_monlocaljobs_alarm_contacts");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $message = self::translate("monitoring_monlocaljobs_alarm_contacts_required");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message
            ]),
        ));
        $this->add($element);
    }
}