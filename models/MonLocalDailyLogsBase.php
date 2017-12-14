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

class MonLocalDailyLogsBase extends \RNTForest\core\models\ModelBase
{   
    /**
    * 
    * @var integer
    */
    protected $mon_jobs_id;
    
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
    * Set id of local MonJob
    *
    * @param integer $mon_jobs_id
    */
    public function setMonJobsId($mon_jobs_id)
    {
        $this->mon_jobs_id = $mon_jobs_id;
    }
    
    /**
    * day in Y-m-d format.
    * e.g. 2017-03-22
    *
    * @param string $day
    */
    public function setDay($day)
    {
        $this->day = $day;
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
    *
    * @return integer
    */
    public function getMonJobsId()
    {
        return $this->mon_jobs_id;
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
}
