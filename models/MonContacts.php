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
    protected $sendBehaviorClass;
    
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
    * Classname 
    *
    * @param string $class
    * @return $this
    */
    public function setSendBehaviorClass($class)
    {
        $this->sendBehaviorClass = $class;
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
    public function getSendBehaviorClass()
    {
        return $this->sendBehaviorClass;
    }
    
    /**
    *
    * @return string
    */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
    * Notifys a contact with the given subject and content.
    * 
    * @param string $subject
    * @param string $content
    */
    public function notify($subject,$content){
        $this->makeSendBehaviorInstance()->send($this->Value,$subject,$content);
    }
    
    /**
    * 
    * @return \RNTForest\ovz\interfaces\SendBehaviorInterface
    */
    private function makeSendBehaviorInstance(){
       return new $this->sendBehaviorClass(); 
    }
}
