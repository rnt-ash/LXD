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
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;

use RNTForest\ovz\controllers\VirtualServersControllerBase;
use RNTForest\ovz\models\PhysicalServers;

class ReplicaActivateForm extends \RNTForest\core\forms\FormBase
{
    
    public function initialize($entity = null, $userOptions = null)
    {
        $this->add(new Hidden("virtual_servers_id"));

        // physical Server
        $scope = $this->permissions->getScope("virtual_servers","filter_physical_servers");
        $customers_id = $this->session->get('auth')['customers_id'];
        $element = new Select(
            "physical_servers_id",
            PhysicalServers::findFromScope($scope,["conditions"=>"ovz = 1","order"=>"name ASC"]),
            array("using"=>array("id","name",),
                "useEmpty"   => true,
                "emptyText"  => $this->translate("virtualserver_choose_physicalserver"),
                "emptyValue" => "",            
            )
        );
        $element->setLabel($this->translate("virtualserver_physicalserver"));
        $element->setFilters(array('int'));
        $element->addValidators([
            new PresenceOfValidator([
                'message' => self::translate("virtualserver_physicalserver_required")
            ])
        ]);        
        $this->add($element);
    }

}