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

class MonServices extends \RNTForest\core\models\ModelBase
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
    protected $description;
    
    /**
    * 
    * @var integer
    */
    protected $graph;
    
    /**
    * 
    * @var string
    */
    protected $statusType;
    
    /**
    * 
    * @var string
    */
    protected $checkType;
    
    /**
    * 
    * @var string
    */
    protected $logValueFormat;
    
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
    * Description
    * 
    * @param string $description
    * @return $this
    */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    /**
    * Set if graph exists or not
    * 
    * @param integer $graph
    * @return $this
    */
    public function setGraph($graph)
    {
        $this->graph = $graph;
        return $this;
    }
    
    /**
    * Status types of the service
    * 
    * @param string $statusType
    * @return $this
    */
    public function setStatusType($statusType)
    {
        $this->statusType = $statusType;
        return $this;
    }
    
    /**
    * Checktype
    * 
    * @param string $chechType
    * @return $this
    */
    public function setCheckType($chechType)
    {
        $this->checkType = $checkType;
        return $this;
    }
    
    /**
    * Value format of the logs
    * 
    * @param string $logValueFormat
    * @return $this
    */
    public function setLogValueFormat($logValueFormat)
    {
        $this->logValueFormat = $logValueFormat;
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
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
    *
    * @return integer
    */
    public function getGraph()
    {
        return $this->graph;
    }
    
    /**
    *
    * @return string
    */
    public function getStatusType()
    {
        return $this->statusType;
    }
    
    /**
    *
    * @return string
    */
    public function getCheckType()
    {
        return $this->checkType;
    }
    
    /**
    *
    * @return string
    */
    public function getLogValueFormat()
    {
        return $this->logValueFormat;
    }
}
