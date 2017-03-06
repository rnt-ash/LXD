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

namespace RNTForest\ovz\models;

class MonContacts extends \RNTForest\core\models\ModelBase
{
    /**
    * 
    * @var integer
    * @Primary
    * @Identity
    */
    protected $id;
    
    /**
    * 
    * @var string
    */
    protected $name;
    
    /**
    * 
    * @var string
    */
    protected $type;
    
    /**
    * 
    * @var string
    */
    protected $value;
    
    /**
    * Unique ID
    *
    * @param integer $id
    * @return $this
    */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
    * Name
    *
    * @param string $name
    * @return $this
    */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
    * Type: mail or sms
    *
    * @param string $type
    * @return $this
    */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
    * Value
    *
    * @param string $value
    * @return $this
    */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    /**
    *
    * @return integer
    */
    public function getId()
    {
        return $this->id;
    }
    
    /**
    *
    * @return string
    */
    public function getName()
    {
        return $this->name;
    }
    
    /**
    *
    * @return string
    */
    public function getType()
    {
        return $this->type;
    }
    
    /**
    *
    * @return string
    */
    public function getValue()
    {
        return $this->value;
    }
}
?>