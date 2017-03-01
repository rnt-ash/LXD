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

use RNTForest\ovz\models\Dcoipobjects;

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
            $message = $this->translate("ipobjects_ip");
            $element->setLabel($message);
            $element->setAttribute("placeholder","192.168.1.1");
            $element->setFilters(array('striptags', 'string', 'trim'));
            $this->add($element);
            
            // value2
            $element = new Text("value2");
            $message = $this->translate("ipobjects_ip_addition");
            $element->setLabel($message);
            $message = $this->translate("ipobjects_ip_additioninfo");
            $element->setAttribute("placeholder", $message);
            $element->setFilters(array('striptags', 'string', 'trim'));
            $this->add($element);

            // allocated
            $element = new Select("allocated",array(
                Dcoipobjects::ALLOC_RESERVED => "Reserved",
                Dcoipobjects::ALLOC_ASSIGNED => "Assigned"
            ));
            if($session['origin']['controller'] == 'colocations'){
                $element->setDefault(Dcoipobjects::ALLOC_RESERVED);
            } else {
                $element->setDefault(Dcoipobjects::ALLOC_ASSIGNED);
            }
            $message = $this->translate("ipobjects_allocated");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $this->add($element);
            
            // main
            $messagemain = $this->translate("ipobjects_ismain");
            $messagenotmain = $this->translate("ipobjects_isnotmain");
            $element = new Select("main",array(
                0 => $messagemain,
                1 => $messagenotmain,
            ));
            $message = $this->translate("ipobjects_ip_main");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $this->add($element);
            
        }
        
        // comment
        $element = new TextArea("comment");
        $message = $this->translate("ipobjects_comment");
        $element->setLabel($message);
        $message = $this->translate("ipobjects_commentinfo");
        $element->setAttribute("placeholder", $message);
        $element->setFilters(array('striptags', 'string', 'trim'));
        $this->add($element);
        
    }

}