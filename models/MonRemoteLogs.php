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

class MonRemoteLogs extends \RNTForest\core\models\ModelBase
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
    protected $mon_jobs_remote_id;
    
    /**
    * 
    * @var string
    */
    protected $value;
    
    /**
    * 
    * @var integer
    */
    protected $healJob;
    
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
    * ID of the monjob
    * 
    * @param integer $monJobsRemoteId
    * @return $this
    */
    public function setMonJobsRemoteId($monJobsRemoteId)
    {
        $this->mon_jobs_remote_id = $monJobsRemoteId;
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
    * ID of the healJob
    * 
    * @param integer $healJob
    * @return $this
    */
    public function setHealJob($healJob)
    {
        $this->healJob = $healJob;
        return $this;
    }
    
    /**
    * Modified
    * 
    * @param string $modified
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
    public function getMonJobsRemoteId()
    {
        return $this->mon_jobs_remote_id;
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
    public function getHealJob()
    {
        return $this->healJob;
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
