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

class MonLocalDailyLogs extends \RNTForest\core\models\ModelBase
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
    * @var integer
    */
    protected $mon_jobs_local_id;
    
    /**
    * 
    * @var string
    */
    protected $day;
    
    /**
    * 
    * @var string
    */
    protected $value;
    
    /**
    * 
    * @var string
    */
    protected $modified;
    
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
    * Remote MonJob
    *
    * @param integer $mon_jobs_local_id
    * @return $this
    */
    public function setMonJobsLocalId($mon_jobs_local_id)
    {
        $this->mon_jobs_local_id = $mon_jobs_local_id;
        return $this;
    }
    
    /**
    * day in Y-m-d format.
    * e.g. 2017-03-22
    *
    * @param string $day
    * @return $this
    */
    public function setDay($day)
    {
        $this->day = $day;
        return $this;
    }
    
    /**
    * Value
    * 
    * @param string $value
    * @return MonLocalDailyLogs
    */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    /**
    * Modified
    *
    * @param integer $modified
    * @return $this
    */
    public function setModified($modified)
    {
        $this->modified = $modified;
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
    * @return integer
    */
    public function getMonJobsLocalId()
    {
        return $this->mon_jobs_local_id;
    }
    
    /**
    *
    * @return string
    */
    public function getDay()
    {
        return $this->day;
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
    *
    * @return integer
    */
    public function getModified()
    {
        return $this->modified;
    }
    
}
