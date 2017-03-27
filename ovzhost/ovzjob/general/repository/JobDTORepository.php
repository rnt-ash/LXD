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

namespace RNTForest\OVZJOB\general\repository;

use RNTForest\OVZJOB\general\utility\JobDTO;

interface JobDTORepository{
    
    /**
    * Returns the JobDTO element with the given id.
    * 
    * @param int $id
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function get($id);
    
    /**
    * Creates a new JobDTO element in db and returns this created element.
    * 
    * @param JobDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function create(JobDTO $jobDTO);
    
    /**
    * Updates a given JobDTO element in db and returns the updated element.
    * 
    * @param JobDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function update(JobDTO $jobDTO);
    
    /**
    * Deletes the JobDTO element and returns the last state in db of this element.
    * 
    * @param JobDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function delete(JobDTO $jobDTO);
    
    /**
    * Deletes the JobDTO element with specified id and returns the last state in db of this element.
    * 
    * @param int $id
    * @return \RNTForest\OVZJOB\general\utility\JobDTO
    */
    public function deleteById($id);    
}
