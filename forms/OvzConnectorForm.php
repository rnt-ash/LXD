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

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValidator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

class OvzConnectorForm extends Form
{
    
    public function initialize($entity = null, $userOptions = null)
    {

        $this->add(new Hidden("physical_servers_id"));

        // name
        $element = new Text("username");
        $element->setLabel("Username");
        $element->setAttribute("placeholder","root");
        $element->setFilters(array('striptags', 'string'));
        $element->addValidators(array(
            new PresenceOfValidator(array(
                'message' => 'username is required'
            ))
        ));
        $this->add($element);

        // password
        $element = new Password("password");
        $element->setLabel("Password");
        $element->setAttribute("placeholder","1234");
        $element->setFilters(array('striptags', 'string'));
        $element->addValidators(array(
            new PresenceOfValidator(array(
                'message' => 'password is required'
            ))
        ));
        $this->add($element);
    }

}