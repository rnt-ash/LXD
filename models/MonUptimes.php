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
    protected $mon_jobs_remote_id;
    
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
    */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
    * Remote MonJob
    *
    * @param integer $mon_jobs_remote_id
    */
    public function setMonJobsRemoteId($mon_jobs_remote_id)
    {
        $this->mon_jobs_remote_id = $mon_jobs_remote_id;
    }
    
    /**
    * YearMonth
    *
    * @param integer $year_month
    */
    public function setYearMonth($year_month)
    {
        $this->year_month = $year_month;
    }
    
    /**
    * Max seconds
    *
    * @param integer $max_seconds
    */
    public function setMaxSeconds($max_seconds)
    {
        $this->max_seconds = $max_seconds;
    }
    
    /**
    * Up seconds
    *
    * @param integer $up_seconds
    */
    public function setUpSeconds($up_seconds)
    {
        $this->up_seconds = $up_seconds;
    }
    
    /**
    * Up percentage
    *
    * @param integer $up_percentage
    */
    public function setUpPercentage($up_percentage)
    {
        $this->up_percentage = $up_percentage;
    }
    
    /**
    * Modified
    *
    * @param integer $modified
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
    public function getMonJobsRemoteId()
    {
        return $this->mon_jobs_remote_id;
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
    
        
    public function getYear(){
        return substr($this->YearMonth,0,4);
    }
    
    public function getMonth(){
        return substr($this->YearMonth,4,2);
    }
}
