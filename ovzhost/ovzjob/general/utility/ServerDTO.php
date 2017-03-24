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

namespace RNTForest\OVZJOB\general\utility;

/**
* Not used at the moment...
*/
class ServerDTO {
    public $Uuid;
    public $Name;
    public $OvzSettings;
    public $Fqdn;
    public $Os;
    public $Modified;
    
    public function __construct($uuid,$name,$ovzSettings,$fqdn,$os = '',$modified = '0000-00-00 00:00:00'){
        $this->Uuid = $uuid;
        $this->Name = $name;
        $this->OvzSettings = $ovzSettings;
        $this->Fqdn = $fqdn;
        $this->Os = $os;
        $this->Modified = $modified;
    }
    
}
