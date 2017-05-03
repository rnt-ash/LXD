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

class MonLocalLogs extends \RNTForest\core\models\ModelBase
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
    */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
    * ID of the monjob
    * 
    * @param integer $monJobsLocalId
    */
    public function setMonJobsLocalId($monJobsLocalId)
    {
        $this->mon_jobs_local_id = $monJobsLocalId;
    }
    
    /**
    * Value
    * 
    * @param string $value
    */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
    * Modified
    * 
    * @param string $modified
    */
    public function setModified($modified)
    {
        $this->modified = $modified;
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
    public function getValue()
    {
        return $this->value;
    }
    
    /**
    * 
    * @return string
    */
    public function getModified()
    {
        return $this->modified;
    }
}
