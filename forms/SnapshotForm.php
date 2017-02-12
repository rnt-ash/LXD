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
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValidator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

class SnapshotForm extends \RNTForest\core\forms\FormBase
{
    
    public function initialize($entity = null, $userOptions = null)
    {

        $this->add(new Hidden("virtual_servers_id"));

        // name
        $element = new Text("name");
        $element->setLabel("Name");
        $element->setAttribute("placeholder","Snapshotname");
        $element->setFilters(array('striptags', 'string'));
        $element->addValidators(array(
            new PresenceOfValidator(array(
                'message' => 'Name is required'
            )),
            new StringLengthValidator(array(
                'max' => 64,
                'min' => 3,
            )),
            new RegexValidator(array(
                'pattern' => '/^(?!.*replica).*$/',
                'message' => 'Name must not contain replica.'
            )),
            new RegexValidator(array(
                'pattern' => '/^[äöüÄÖÜ0-9a-zA-Z-_().!?\s]{3,64}$/',
                'message' => 'Name must be alphanumeric and may contain the characters -_().!? and space.'
            )),
        ));
        $this->add($element);

        // description
        $element = new Text("description");
        $element->setLabel("Description");
        $element->setAttribute("placeholder","Description");
        $element->setFilters(array('striptags', 'string'));
        $element->addValidators(array(
            new StringLengthValidator(array(
                'max' => 250,
                'message' => 'Name must not be longer than 250 characters.'
            ))
        ));
        $this->add($element);
    }

}
