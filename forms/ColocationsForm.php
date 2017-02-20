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

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Date;

use RNTForest\core\models\Customers;
use RNTForest\ovz\models\Colocations;

class ColocationsForm extends \RNTForest\core\forms\FormBase
{

    public function initialize($entity = null)
    {
        // get params from session
        $session = $this->session->get("PhysicalServersForm");
        $op = $session['op'];

        // id
        $this->add(new Hidden("id"));

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

        // name
        $element = new Text("name");
        $element->setLabel("Name");
        $element->setAttribute("placeholder","My Colocation Name");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // description
        $element = new TextArea("description");
        $element->setLabel("Description");
        $element->setAttribute("placeholder","some additional information to this colocation...");
        $element->setFilters(array('striptags', 'string', 'trim'));
        $this->add($element);
        
        // location
        $element = new Text("location");
        $element->setLabel("Location");
        $element->setAttribute("placeholder","My Location");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // activation_date
        $element = new Date("activation_date");
        $element->setLabel("Activation Date");
        $element->setDefault(date("Y-m-d"));
        $element->setFilters(array('string', 'trim'));
        $this->add($element);
        
        // Validator
        $validator = Colocations::generateValidator();
        $this->setValidation($validator);
    }

}