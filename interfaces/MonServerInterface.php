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

namespace RNTForest\lxd\interfaces;

interface MonServerInterface
{
    /**
    * @return integer
    */
    public function getId();
    
    /**
    * @return string
    */
    public function getFqdn();
    
    /**
    * @return string
    */
    public function getName();
    
    /**
    * @return \RNTForest\lxd\models\IpObjects
    */
    public function getMainIp();
    
    /**
    * @return string
    */
    public function getParentClass();
    
    /**
    * @return integer
    */
    public function getParentId();
    
    /**
    * @return string
    */
    public function getOvzStatistics();
    
    /**
    * Refresh the entity object.
    */
    public function refresh();
}
