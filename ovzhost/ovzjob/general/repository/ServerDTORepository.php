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

use RNTForest\OVZJOB\general\utility\ServerDTO;

/**
* AKTUELL NICHT VERWENDET
*/
interface ServerDTORepository{
    
    /**
    * Returns the ServerDTO element with the given id.
    * 
    * @param string $uuid
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function get($uuid);
    
    /**
    * Creates a new ServerDTO element in db and returns this created element.
    * 
    * @param ServerDTO $serverDTO
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function create(ServerDTO $serverDTO);
    
    /**
    * Updates a given ServerDTO element in db and returns the updated element.
    * 
    * @param ServerDTO $jobDTO
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function update(ServerDTO $serverDTO);
    
    /**
    * Deletes the ServerDTO element and returns the last state in db of this element.
    * 
    * @param ServerDTO $jserverDTO
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function delete(ServerDTO $serverDTO);
    
    /**
    * Deletes the ServerDTO element with specified id and returns the last state in db of this element.
    * 
    * @param string $uuid
    * @return \RNTForest\OVZJOB\general\utility\ServerDTO
    */
    public function deleteByUuid($id);    
}
