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

class MonUptimes extends \RNTForest\core\models\ModelBase
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
    protected $remote_mon_jobs_id;
    
    /**
    * 
    * @var string
    */
    protected $year_month;
    
    /**
    * 
    * @var int
    */
    protected $max_seconds;
    
    /**
    * 
    * @var int
    */
    protected $up_seconds;
    
    /**
    * 
    * @var int
    */
    protected $up_percentage;
    
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
    * @param integer $remote_mon_jobs_id
    * @return $this
    */
    public function setRemoteMonJobsId($remote_mon_jobs_id)
    {
        $this->remote_mon_jobs_id = $remote_mon_jobs_id;
        return $this;
    }
    
    /**
    * YearMonth
    *
    * @param integer $year_month
    * @return $this
    */
    public function setYearMonth($year_month)
    {
        $this->year_month = $year_month;
        return $this;
    }
    
    /**
    * Max seconds
    *
    * @param integer $max_seconds
    * @return $this
    */
    public function setMaxSeconds($max_seconds)
    {
        $this->max_seconds = $max_seconds;
        return $this;
    }
    
    /**
    * Up seconds
    *
    * @param integer $up_seconds
    * @return $this
    */
    public function setUpSeconds($up_seconds)
    {
        $this->up_seconds = $up_seconds;
        return $this;
    }
    
    /**
    * Up percentage
    *
    * @param integer $up_percentage
    * @return $this
    */
    public function setUpPercentage($up_percentage)
    {
        $this->up_percentage = $up_percentage;
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
    public function getRemoteMonJobsId()
    {
        return $this->remote_mon_jobs_id;
    }
    
    /**
    *
    * @return integer
    */
    public function getYearMonth()
    {
        return $this->year_month;
    }
    
    /**
    *
    * @return integer
    */
    public function getMaxSeconds()
    {
        return $this->max_seconds;
    }
    
    /**
    *
    * @return integer
    */
    public function getUpSeconds()
    {
        return $this->up_seconds;
    }
    
    /**
    *
    * @return integer
    */
    public function getUpPercentage()
    {
        return $this->up_percentage;
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
