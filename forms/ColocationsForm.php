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
        $message = $this->translate("colocations_choose_customer");
        $element = new Select(
            "customers_id",
            Customers::find(array("columns"=>"id,CONCAT(company,' (',lastname,' ' ,firstname,')',' ',city) as name","order"=>"name")),
            array("using"=>array("id","name"),
                "useEmpty"   => true,
                "emptyText"  => $message,
                "emptyValue" => "",            
            )
        );
        $message = $this->translate("colocations_customer");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $this->add($element);

        // name
        $element = new Text("name");
        $message = $this->translate("colocations_name");
        $element->setLabel($message);
        $message = $this->translate("colocations_colocationname");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // description
        $element = new TextArea("description");
        $message = $this->translate("colocations_description");
        $element->setLabel($message);
        $message = $this->translate("colocations_description_info");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('striptags', 'string', 'trim'));
        $this->add($element);
        
        // location
        $element = new Text("location");
        $message = $this->translate("colocations_location");
        $element->setLabel($message);
        $message = $this->translate("colocations_location_info");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // activation_date
        $element = new Date("activation_date");
        $message = $this->translate("colocations_activ_date");
        $element->setLabel($message);
        $element->setDefault(date("Y-m-d"));
        $element->setFilters(array('string', 'trim'));
        $this->add($element);
        
        // Validator
        $validator = Colocations::generateValidator();
        $this->setValidation($validator);
    }

}