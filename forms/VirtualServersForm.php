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
            $message = $this->translate("virtualserver_hostname");
            $element->setLabel($message);
            $element->setAttribute("placeholder","host.domain.tld");
            $element->setFilters(array('striptags', 'string'));
            $message = $this->translate("virtualserver_hostname_valid");
            $element->addValidators(array(
                new RegexValidator([
                    'pattern' => '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/',
                    'message' => $messages,
                    'allowEmpty' => true,
                ])
            ));
            $this->add($element);
        }

        // customer
        $this->add(new Hidden("customers_id"));
        
        $element = new Text("customers");
        $message = $this->translate("virtualserver_customer");
        $element->setLabel($message);
        $customerText = '';
        if(!is_null($virtualServer->customers_id)){
            $customerText = Customers::findFirst($virtualServer->customers_id)->printAddressText('line');
        }
        $element->setAttribute("value",$customerText);
        $element->setFilters(array('string'));
        $message = $this->translate("virtualserver_customer_required");
        $element->addValidators(array(
            new PresenceOfValidator(array(
                'message' => $message
            ))
        ));
        $this->add($element);
        
        // physical servers
        $physicalServersSelect = array();
        $scope = $this->permissions->getScope("virtual_servers","new"); 
        $findParameters = array("order"=>"name");
        $physicalServers = PhysicalServers::findFromScope($scope,$findParameters);
        foreach($physicalServers as $physicalServer){
            // show only ovz servers for CT's and VM's
            if(($vstype == 'CT' || $vstype == 'VM') && $physicalServer->getOvz() != 1) continue;
            $physicalServersSelect[$physicalServer->getId()] = $physicalServer->getName()." ";
            $physicalServersSelect[$physicalServer->getId()] .= " [".count(VirtualServers::find("physical_servers_id = ".$physicalServer->getId()))."]";
        }
        
        $message = $this->translate("virtualserver_choose_physicalserver");
        $element = new Select(
            "physical_servers_id",
            $physicalServersSelect,
            array("using"=>array("id","name",),
                "useEmpty"   => true,
                "emptyText"  => $message,
                "emptyValue" => 0,            
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
        $element = new Text("memory");
        $message = $this->translate("virtualserver_memory");
        $element->setLabel($message);
        $element->setDefault('1GB');
        $message = $this->translate("virtualserver_memory_example");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('string'));
        $element->setAttribute("value",'17GB');
        $this->add($element);
        
        // space
        $element = new Text("space");
        $message = $this->translate("virtualserver_space");
        $element->setLabel($message);
        $element->setDefault('100GB');
        $message = $this->translate("virtualserver_space_example");
        $element->setAttribute("placeholder",$message);
        $element->setFilters(array('string'));
        $this->add($element);

        // activation_date
        $element = new Text("activation_date");
        $message = $this->translate("virtualserver_activdate");
        $element->setLabel($message);
        $element->setDefault(date("Y-m-d"));
        $element->setFilters(array('string', 'trim'));
        $this->add($element);

        // root pwd
        if ($op == 'new' && ($vstype == 'CT' || $vstype == 'VM')) {
            $element = new Password("password");
            $message = $this->translate("virtualserver_rootpassword");
            $element->setLabel($message);
            $element->setAttribute("placeholder","1234");
            $element->setFilters(array('striptags', 'string'));
            $element->addValidators(array(
                new \Phalcon\Validation\Validator\PresenceOf([
                    'message' => $this->translate("virtualserver_password_required")
                ]),
                new \Phalcon\Validation\Validator\StringLength([
                    'min' => 8,
                    'messageMinimum' => $this->translate("virtualserver_passwordmin")
                ])
            ));
            $this->add($element);
        }
        $message = $this->translate("virtualserver_choose_ostemplate");
        if ($op == 'new' && $vstype == 'CT') {
            // OS templates
            $element = new Select(
                "ostemplate",
                array(),
                array(
                    "using"      => array("id","name"),
                    "useEmpty"   => true,
                    "emptyText"  => $message,
                    "emptyValue" => "0",            
                )
            );
            $element->setLabel("OS Template");
            $element->setFilters(array('striptags', 'string'));
            $element->addValidators(array(
                new \Phalcon\Validation\Validator\PresenceOf([
                    'message' => $this->translate("virtualserver_ostemplate_required")
                ])
            ));
            $this->add($element);
        }
        
        // description
        $element = new TextArea("description");
        $message = $this->translate("virtualserver_description");
        $element->setLabel($message);
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);
    }
}