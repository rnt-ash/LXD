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
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\TextArea;

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLengthValitator;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOfValidator;
use Phalcon\Validation\Validator\Between as BetweenValidator;

use RNTForest\ovz\models\VirtualServers;

class ConfigureVirtualServersForm extends \RNTForest\core\forms\FormBase
{

    public function initialize($virtualServer = null, $options = array())
    {
        $this->add(new Hidden("virtual_servers_id"));

        // fqdn
        $element = new Text("hostname");
        $element->setLabel("Hostname");
        $element->setAttribute("placeholder","host.domain.tld");
        $element->setFilters(array('striptags', 'string'));
        $element->addValidators(array(
            new RegexValidator([
                'pattern' => '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/',
                'message' => 'must be a string separated by points',
                'allowEmpty' => true,
            ])
        ));
        $this->add($element);

        // core
        $element = new Numeric("cores");
        $element->setLabel("Cores");
        $element->setAttribute("placeholder","available cores (e.g. 4)");
        $element->setFilters(array('int'));
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => 'Cores is required',
            ]),
        ));
        $this->add($element);

        // memory
        $element = new Text("memory");
        $element->setLabel("Memory");
        $element->setAttribute("placeholder","available memory in MB (e.g. 2048)");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => 'Memory is required',
            ]),
            new RegexValidator([
                'pattern' => '/^[1-9][0-9]*.?\d*[mMgG][bB]$/',
                'message' => 'please specify if it\'s either GB or MB',
                'allowEmpty' => true,
            ])
        ));
        $this->add($element);

        // space
        $element = new Text("diskspace");
        $element->setLabel("Diskspace");
        $element->setAttribute("placeholder","available space in GB  (e.g. 100)");
        $element->addValidators(array(
            new PresenceOfValidator([
                'message' => 'Diskspace is required',
            ]),
            new RegexValidator([
                'pattern' => '/^[1-9][0-9]*.?\d*[mMgGtT][bB]$/',
                'message' => 'please specify if it\'s either TB,GB or MB',
                'allowEmpty' => true,
            ])
        ));
        $this->add($element);
        
        // dns
        $element = new Text("dns");
        $element->setLabel("DNS-Server");
        $element->setAttribute("placeholder","8.8.8.8");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);
        
        // start on boot
        $element = new Check("startOnBoot",array(
            'value' => 1,
        ));
        $element->setLabel("Start on boot");
        $element->setFilters(array('int'));
        $element->addValidators(array(
            new BetweenValidator([
                'minimum' => 0,
                'maximum' => 1,
                'message' => 'Start on boot can either be 0 or 1',
            ]),
        ));
        $this->add($element);
        
        // description
        $element = new TextArea("description");
        $element->setLabel("Description");
        $element->setFilters(array('striptags', 'string'));
        $this->add($element);
    }
}