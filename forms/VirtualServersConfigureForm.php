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
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\TextArea;

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Validation\Validator\Between as BetweenValidator;

use RNTForest\ovz\models\VirtualServers;

class VirtualServersConfigureForm extends \RNTForest\core\forms\FormBase
{

    public function initialize($virtualServer = null, $options = array())
    {
        $this->add(new Hidden("virtual_servers_id"));

        // core
        $element = new Numeric("cores");
        $message = $this->translate("virtualserver_cores");
        $element->setLabel($message);
        $message = $this->translate("virtualserver_cores_example");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('int'));
        $message = $this->translate("virtualserver_core_required");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message,
            ]),
        ));
        $this->add($element);

        // memory
        $element = new Text("memory");
        $message = $this->translate("virtualserver_memory");
        $element->setLabel($message);
        $message = $this->translate("virtualserver_memory_example");
        $element->setAttribute("placeholder",$message);
        $message = $this->translate("virtualserver_memory_required");
        $message1 = $this->translate("virtualserver_memory_specify");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message,
            ]),
            /* allows numbers (first number can't be 0), then an optional dot, then numbers after the dot, 
            an optional whitespace and at last it has to be either MB or GB (not case sensitive)*/
            new RegexValidator([
                'pattern' => '/^[1-9][0-9]*[\.]?\d*\s?[mMgG][bB]$/',
                'message' => $message1,
                'allowEmpty' => true,
            ])
        ));
        $this->add($element);

        // space
        $element = new Text("diskspace");
        $message = $this->translate("virtualserver_discspace");
        $element->setLabel($message);
        $message = $this->translate("virtualserver_discspace_example");
        $element->setAttribute("placeholder",$message);
        $message = $this->translate("virtualserver_discspace_required");
        $message1 = $this->translate("virtualserver_discspace_specify");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => $message,
            ]),
            /* allows numbers (first number can't be 0), then an optional dot, then numbers after the dot, 
            an optional whitespace and at last it has to be either MB, GB or TB (not case sensitive)*/
            new RegexValidator([
                'pattern' => '/^[1-9][0-9]*[\.]?\d*\s?[mMgGtT][bB]$/',
                'message' => $message1,
                'allowEmpty' => true,
            ])
        ));
        $this->add($element);
        
        // dns
        $element = new Text("dns");
        $message = $this->translate("virtualserver_dnsserver");
        $element->setLabel($message);
        $element->setAttribute("placeholder","8.8.8.8");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);
        
        // start on boot
        $element = new Check("startOnBoot",array(
            'value' => 1,
        ));
        $message = $this->translate("virtualserver_startonboot");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $message = $this->translate("virtualserver_startonboot_info");
        $element->addValidators(array(
            new BetweenValidator([
                'minimum' => 0,
                'maximum' => 1,
                'message' => $message,
            ]),
        ));
        $this->add($element);
    }
}