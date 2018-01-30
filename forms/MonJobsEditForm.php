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

namespace RNTForest\lxd\forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Check;

use RNTForest\lxd\models\MonJobs;
use RNTForest\lxd\functions\Monitoring;

class MonJobsEditForm extends \RNTForest\core\forms\FormBase
{
    public function initialize($monJob = null, $options = array())
    {
        // id
        $this->add(new Hidden("id"));

        if($monJob->getMonType() == 'local'){
            // mon_behavior_params
            $element = new Text("mon_behavior_params");
            $message = $this->translate("monitoring_monjobs_mon_behavior_params");
            $element->setLabel($message);
            $element->setFilters(array('striptags'));
            $this->add($element);
            
            // warning_value
            $element = new Numeric("warning_value");
            $message = $this->translate("monitoring_monjobs_warning_value");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $this->add($element);
            
            // maximal_value
            $element = new Numeric("maximal_value");
            $message = $this->translate("monitoring_monjobs_maximal_value");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $this->add($element);
        }elseif($monJob->getMonType() == 'remote'){
            // healing
            $element = new Check("healing",array(
                'value' => 1,
            ));
            $message = $this->translate("monitoring_monjobs_healing");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $this->add($element);
        }
        
        // period
        $element = new Numeric("period");
        $message = $this->translate("monitoring_monjobs_period");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $this->add($element);
        
        // alarm_period
        $element = new Numeric("alarm_period");
        $message = $this->translate("monitoring_monjobs_alarm_period");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $this->add($element);
        
        // active
        $element = new Check("active",array(
            'value' => 1,
        ));
        $message = $this->translate("monitoring_monjobs_active");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $this->add($element);
        
        // alarm
        $element = new Check("alarm",array(
            'value' => 1,
        ));
        $message = $this->translate("monitoring_monjobs_alarm");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $this->add($element);
        
        // get all logins from the same customer as the logged in user
        $customerId = \Phalcon\Di::getDefault()->get("session")->get("auth")['customers_id'];
        $logins = \RNTForest\core\models\Logins::find(array("columns"=>"id,CONCAT(firstname, ' ', lastname, ' (',email, ')') as name","order"=>"name","customers_id = ".$customerId));
        
        // message contact
        $monJob->setMonContactsMessage(explode(",",$monJob->getMonContactsMessage()));
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
        $monJob->setMonContactsAlarm(explode(",",$monJob->getMonContactsAlarm()));
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