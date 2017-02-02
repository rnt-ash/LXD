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
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

class VirtualServersForm extends Form
{

    public function initialize($virtualServer = null, $options = array())
    {

        // get params from session
        $session = $this->session->get("VirtualServersValidator");
        $op = $session['op'];
        $vstype = $session['vstype'];

        $this->add(new Hidden("id"));

        // name
        $element = new Text("name");
        $element->setLabel("Name");
        $element->setAttribute("placeholder","My Server");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // fqdn
        if ($op == 'edit') {
            $element = new Text("fqdn");
            $element->setLabel("FQDN");
            $element->setAttribute("placeholder","host.domain.tld");
            $element->setFilters(array('striptags', 'string'));
            $this->add($element);
        }

        // customer
        $element = new Select(
            "customers_id",
            Customers::find(),
            array("using"=>array("id","company"),
                "useEmpty"   => true,
                "emptyText"  => "Please, choose a customer...",
                "emptyValue" => "",            
            )
        );
        $element->setLabel("Customer");
        $element->setFilters(array('int'));
        $this->add($element);

        // physical servers
        $element = new Select(
            "physical_servers_id",
            PhysicalServers::find(),
            array("using"=>array("id","name",),
                "useEmpty"   => true,
                "emptyText"  => "Please, choose a physical Server...",
                "emptyValue" => "",            
            )
        );
        $element->setLabel("Physical Servers");
        $element->setFilters(array('int'));
        $this->add($element);

        // core
        $element = new Numeric("core");
        $element->setLabel("Cores");
        $element->setDefault(4);
        $element->setAttribute("placeholder","available cores  (e.g. 4)");
        $element->setFilters(array('int'));
        $this->add($element);

        // memory
        $element = new Numeric("memory");
        $element->setLabel("Memory");
        $element->setDefault(1024);
        $element->setAttribute("placeholder","available memory in MB (e.g. 2048)");
        $element->setFilters(array('int'));
        $this->add($element);

        // space
        $element = new Numeric("space");
        $element->setLabel("Space");
        $element->setDefault(100);
        $element->setAttribute("placeholder","available space in GB  (e.g. 100)");
        $element->setFilters(array('int'));
        $this->add($element);

        // activation_date
        $element = new Date("activation_date");
        $element->setLabel("Activation Date");
        $element->setDefault(date("Y-m-d"));
        $element->setFilters(array('string', 'trim'));
        $this->add($element);

        // comment
        $element = new TextArea("description");
        $element->setLabel("Description");
        $element->setAttribute("placeholder","some additional information to this server...");
        $element->setFilters(array('striptags', 'string', 'trim'));
        $this->add($element);

        // root pwd
        if ($op == 'new' && ($vstype == 'CT' || $vstype == 'VM')) {
            $element = new Password("password");
            $element->setLabel("Root Password");
            $element->setAttribute("placeholder","1234");
            $element->setFilters(array('striptags', 'string'));
            $this->add($element);
        }

        if ($op == 'new' && $vstype == 'CT') {
            // OS templates
            $ostemplates = $session['ostemplates'];
            $element = new Select(
                "ostemplate",
                $ostemplates,
                array("using"=>array("id","name",),
                    "useEmpty"   => true,
                    "emptyText"  => "Please, choose a OS template...",
                    "emptyValue" => "",            
                )
            );
            $element->setLabel("ostemplate");
            $element->setFilters(array('striptags', 'string'));
            $this->add($element);
        }
        
        // Validator
        $validator = VirtualServers::generateValidator($op,$vstype);
        $this->setValidation($validator);

    }

}