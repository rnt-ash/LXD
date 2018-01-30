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
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Validation\Validator\StringLength as StringLengthValidator;
use Phalcon\Validation\Validator\Regex as RegexValidator;

class RootPasswordChangeForm extends \RNTForest\core\forms\FormBase
{
    
    public function initialize($entity = null, $userOptions = null)
    {

        $this->add(new Hidden("virtual_servers_id"));

        // password
        $element = new Password("password");
        $message = $this->translate("virtualserver_root_password");
        $element->setLabel($message);
        $element->setFilters(array('striptags', 'string'));
        $message = $this->translate("virtualserver_password_required");
        $message1 = $this->translate("virtualserver_passwordmin");
        $message2 = $this->translate("virtualserver_passwordmax");
        $message3 = $this->translate("virtualserver_passwordregex");
        $element->addValidators(array(
            new PresenceOfValidator(array(
                'message' => $message
            )),
            new StringLengthValidator(array(
                'min' => 8,
                'max' => 12,
                'messageMinimum' => $message1,
                'messageMaximum' => $message2
            )),
            new RegexValidator(array(
                'pattern' => '/^[A-Za-z0-9_\.-]*$/',
                'message' => $message3,
                'allowEmpty' => true,
            )),
        ));
        $this->add($element);
    }
}