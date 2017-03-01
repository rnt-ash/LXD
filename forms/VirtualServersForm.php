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
use Phalcon\Forms\Element\Password;
use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

use RNTForest\core\models\Customers;
use RNTForest\ovz\models\PhysicalServers;
use RNTForest\ovz\models\VirtualServers;

class VirtualServersForm extends \RNTForest\core\forms\FormBase
{

    public function initialize($virtualServer = null, $options = array())
    {

        // get params from session
        $session = $this->session->get("VirtualServersForm");
        $op = $session['op'];
        $vstype = $session['vstype'];
        
        // check if virtual server is ovz enabled
        !empty($virtualServer)?$ovz = $virtualServer->getOvz():$ovz = '';

        // id
        $this->add(new Hidden("id"));

        // name
        $element = new Text("name");
        $message = self::translate("virtualserver_name");
        $element->setLabel($message);
        $message = $this->translate("virtualserver_myserver");
        $element->setAttribute("placeholder",$message);
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
        $message = $this->translate("virtualserver_choose_customer");
        $element = new Select(
            "customers_id",
            Customers::find(array("columns"=>"id,CONCAT(company,' (',lastname,' ' ,firstname,')',' ',city) as name","order"=>"name")),
            array("using"=>array("id","name"),
                "useEmpty"   => true,
                "emptyText"  => $message,
                "emptyValue" => "",            
            )
        );
        $message = $this->translate("virtualserver_customer");
        $element->setLabel($message);
        $element->setFilters(array('int'));
        $this->add($element);

        if($op == 'new' || $ovz == 0){
            // physical servers
            $message = $this->translate("virtualserver_choose_physicalserver");
            $element = new Select(
                "physical_servers_id",
                PhysicalServers::find(),
                array("using"=>array("id","name",),
                    "useEmpty"   => true,
                    "emptyText"  => $message,
                    "emptyValue" => "",            
                )
            );
            $message = $this->translate("virtualserver_physicalserver");
            $element->setLabel($message);
            $element->setFilters(array('int'));
            $this->add($element);

            // core
            $element = new Numeric("core");
            $message = $this->translate("virtualserver_cores");
            $element->setLabel($message);
            $element->setDefault(4);
            $message = $this->translate("virtualserver_cores_example");
            $element->setAttribute("placeholder",$message);
            $element->setFilters(array('int'));
            $this->add($element);

            // memory
            $element = new Numeric("memory");
            $message = $this->translate("virtualserver_memory");
            $element->setLabel($message);
            $element->setDefault(1024);
            $message = $this->translate("virtualserver_memory_example");
            $element->setAttribute("placeholder",$message);
            $element->setFilters(array('int'));
            $this->add($element);

            // space
            $element = new Numeric("space");
            $message = $this->translate("virtualserver_space");
            $element->setLabel($message);
            $element->setDefault(102400);
            $message = $this->translate("virtualserver_space_example");
            $element->setAttribute("placeholder",$message);
            $element->setFilters(array('int'));
            $this->add($element);
        }

        // activation_date
        $element = new Date("activation_date");
        $message = $this->translate("virtualserver_activdate");
        $element->setLabel($message);
        $element->setDefault(date("Y-m-d"));
        $element->setFilters(array('string', 'trim'));
        $this->add($element);

        if($op == 'new' || $ovz == 0){
            // comment
            $element = new TextArea("description");
            $message = $this->translate("virtualserver_description");
            $element->setLabel($message);
            $message = $this->translate("virtualserver_description_info");
            $element->setAttribute("placeholder",$message);
            $element->setFilters(array('striptags', 'string', 'trim'));
            $this->add($element);
        }

        // root pwd
        if ($op == 'new' && ($vstype == 'CT' || $vstype == 'VM')) {
            $element = new Password("password");
            $message = $this->translate("virtualserver_rootpassword");
            $element->setLabel($message);
            $element->setAttribute("placeholder","1234");
            $element->setFilters(array('striptags', 'string'));
            $this->add($element);
        }
        $message = $this->translate("virtualserver_choose_ostemplate");
        if ($op == 'new' && $vstype == 'CT') {
            // OS templates
            
            $ostemplates = $session['ostemplates'];
            $element = new Select(
                "ostemplate",
                $ostemplates,
                array("using"=>array("id","name",),
                    "useEmpty"   => true,
                    "emptyText"  => $message,
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