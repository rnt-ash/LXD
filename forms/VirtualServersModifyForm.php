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
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

use RNTForest\core\models\Customers;
use RNTForest\ovz\models\VirtualServers;

class VirtualServersModifyForm extends \RNTForest\core\forms\FormBase
{

    public function initialize($virtualServer = null, $options = array())
    {
        // id
        $this->add(new Hidden("id"));

        // name
        $element = new Text("name");
        $message = self::translate("virtualserver_name");
        $element->setLabel($message);
        $message = $this->translate("virtualserver_myserver");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // fqdn
        $element = new Text("fqdn");
        $message = $this->translate("virtualserver_hostname");
        $element->setLabel($message);
        $element->setAttribute("placeholder","host.domain.tld");
        $element->setFilters(array('striptags', 'string'));
        
        $this->add($element);
        
        // customer
        $this->add(new Hidden("customers_id"));
        
        $element = new Text("customers");
        $message = $this->translate("virtualserver_customer");
        $element->setLabel($message);
        $customerText = '';
        if(!is_null($virtualServer->customers_id)){
            $customerText = Customers::findFirst($virtualServer->customers_id)->printAddressText('line');
        }
        $element->setAttribute("value",$customerText);
        $element->setFilters(array('string'));
        $message = $this->translate("virtualserver_customer_required");
        $element->addValidators(array(
            new PresenceOfValidator(array(
                'message' => $message
            ))
        ));
        $this->add($element);
        

        // activation_date
        $element = new Text("activation_date");
        $message = $this->translate("virtualserver_activdate");
        $element->setLabel($message);
        $element->setDefault(date("Y-m-d"));
        $element->setFilters(array('string', 'trim'));
        $this->add($element);

        // description
        $element = new TextArea("description");
        $message = $this->translate("virtualserver_description");
        $element->setLabel($message);
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);
    }
}