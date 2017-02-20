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
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

use RNTForest\core\models\Customers;
use RNTForest\ovz\models\Colocations;
use RNTForest\ovz\models\PhysicalServers;

class PhysicalServersForm extends \RNTForest\core\forms\FormBase
{

    public function initialize($entity = null, $options = array())
    {

        // get params from session
        $session = $this->session->get("PhysicalServersForm");
        $op = $session['op'];

        // id
        $this->add(new Hidden("id"));

        // name
        $element = new Text("name");
        $element->setLabel("Name");
        $element->setAttribute("placeholder","My Server");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // fqdn
        $element = new Text("fqdn");
        $element->setLabel("FQDN");
        $element->setAttribute("placeholder","host.domain.tld");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // customer
        $element = new Select(
            "customers_id",
            Customers::find(),
            array("using"=>array("id","company"),
                "useEmpty"   => true,
                "emptyText"  => "Please, choose a customer...",
                "emptyValue" => "0",            
            )
        );
        $element->setLabel("Customer");
        $element->setFilters(array('int'));
        $this->add($element);

        // colocation
        $element = new Select(
            "colocations_id",
            Colocations::find(),
            array("using"=>array("id","name",),
                "useEmpty"   => true,
                "emptyText"  => "Please, choose a colocation...",
                "emptyValue" => "0",            
            )
        );
        $element->setLabel("Colocation");
        $element->setFilters(array('int'));
        $this->add($element);

        // core
        $element = new Numeric("core");
        $element->setLabel("Cores");
        $element->setAttribute("placeholder","available cores  (e.g. 4)");
        $element->setFilters(array('int'));
        $this->add($element);

        // memory
        $element = new Numeric("memory");
        $element->setLabel("Memory");
        $element->setAttribute("placeholder","available memory in MB (e.g. 2048)");
        $element->setFilters(array('int'));
        $this->add($element);

        // space
        $element = new Numeric("space");
        $element->setLabel("Space");
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

        // Validator
        $validator = PhysicalServers::generateValidator();
        $this->setValidation($validator);
        
    }

}