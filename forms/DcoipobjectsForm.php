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
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Radio;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

class DcoipobjectsForm extends \RNTForest\core\forms\FormBase
{

    public function initialize($dcoipobject = null, $options = array())
    {

        // get params from session
        $session = $this->session->get("DcoipobjectsForm");
        $op = $session['op'];

        // id
        $this->add(new Hidden("id"));

        if ($op == 'new'){
            // version
            $element = new Hidden("version");
            $element->setDefault(4);
            $this->add($element);
            
            // type
            $element = new Hidden("type");
            $element->setDefault(1);
            $this->add($element);
            
            // value1
            $element = new Text("value1");
            $element->setLabel("IP Address");
            $element->setAttribute("placeholder","192.168.1.1");
            $element->setFilters(array('striptags', 'string', 'trim'));
            $this->add($element);
            
            // value2
            $element = new Text("value2");
            $element->setLabel("Additional IP Value");
            $element->setAttribute("placeholder","Empty or Subnetmask if IP Address, End IP Address if Range or Prefix if Subnet");
            $element->setFilters(array('striptags', 'string', 'trim'));
            $this->add($element);

            // allocated
            $element = new Select("allocated",array(
                Dcoipobjects::ALLOC_RESERVED => "Reserved",
                Dcoipobjects::ALLOC_ASSIGNED => "Assigned"
            ));
            $element->setLabel("Allocated");
            $element->setFilters(array('int'));
            $this->add($element);
            
            // main
            $element = new Select("main",array(
                0 => "Is not main",
                1 => "Is main",
            ));
            $element->setLabel("Main IP");
            $element->setFilters(array('int'));
            $this->add($element);
            
        }
        
        // comment
        $element = new TextArea("comment");
        $element->setLabel("Comment");
        $element->setAttribute("placeholder","some additional information to IP Object");
        $element->setFilters(array('striptags', 'string', 'trim'));
        $this->add($element);
        
        // Validator
        //$validator = Dcoipobjects::generateValidator($op);
        //$this->setValidation($validator);

    }

}