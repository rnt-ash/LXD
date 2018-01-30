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
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

use RNTForest\core\models\Customers;
use RNTForest\lxd\models\Colocations;
use RNTForest\lxd\models\PhysicalServers;

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
        $message = $this->translate("physicalserver_name");
        $element->setLabel($message);
        $message = $this->translate("physicalserver_myserver");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);
        
        // fqdn
        $element = new Text("fqdn");
        $message = $this->translate("physicalserver_fqdn");
        $element->setLabel("FQDN");
        $message = $this->translate("physicalserver_hostdomaintld");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);

        // customer
        $this->add(new Hidden("customers_id"));
        
        $message = $this->translate("physicalserver_choose_customer");
        $element = new Text("customers");
        $message = $this->translate("physicalserver_customer");
        $element->setLabel($message);
        $customerText = '';
        if(!is_null($entity->customers_id)){
            $customerText = Customers::findFirst($entity->customers_id)->printAddressText('line');
        }
        $element->setAttribute("value",$customerText);
        $element->setFilters(array('string'));
        $message = $this->translate("physicalserver_customer_required");
        $element->addValidators(array(
            new PresenceOfValidator(array(
                'message' => $message
            ))
        ));
        $this->add($element);
        
        // colocation
        $message = $this->translate("physicalserver_choose_colocation");
        $element = new Select(
            "colocations_id",
            Colocations::find(array("order"=>"name")),
            array("using"=>array("id","name"),
                "useEmpty"   => true,
                "emptyText"  => $message,
                "emptyValue" => "0",            
            )
        );
        $message = $this->translate("physicalserver_colocation");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $this->add($element);

        // core
        $element = new Numeric("core");
        $message = $this->translate("physicalserver_cores");
        $element->setLabel($message);
        $message = $this->translate("physicalserver_cores_available");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('int'));
        $this->add($element);

        // memory
        $element = new Text("memory");
        $message = $this->translate("physicalserver_memory");
        $element->setLabel($message);
        $element->setDefault('16GB');
        $message = $this->translate("physicalserver_memory_available");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('string'));
        $this->add($element);

        // space
        $element = new Text("space");
        $message = $this->translate("physicalserver_space");
        $element->setLabel($message);
        $element->setDefault('1TB');
        $message = $this->translate("physicalserver_space_available");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('string'));
        $this->add($element);
        
        // activation_date
        $element = new Text("activation_date");
        $message = $this->translate("physicalserver_activ_date");
        $element->setLabel($message);
        $element->setDefault(date("Y-m-d"));
        $element->setFilters(array('string', 'trim'));
        $this->add($element);
        
        // comment
        $element = new TextArea("description");
        $message = $this->translate("physicalserver_discription");
        $element->setLabel($message);
        $message = $this->translate("physicalserver_discription_info");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('striptags', 'string', 'trim'));
        $this->add($element);
    }

}