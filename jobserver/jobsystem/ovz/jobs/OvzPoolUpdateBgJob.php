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

namespace RNTForest\jobsystem\ovz\jobs;

class OvzPoolUpdateBgJob extends AbstractOvzJob{

    public static function usage(){
        return [
            "type" => "ovz_pool_update",
            "description" => "update the jobsystem from OVZ pool",
            "params" => [
            ],
            "params_example" => '',
            "retval" => "",
            "warning" => "",
            "error" => "",
        ];
    }


    /**
    * Background-Job zum holen des EATPool-Ordners OVZ
    * 
    * @param mixed $params
    *
    */
    public function run() {
        $this->Context->getLogger()->debug("Update pool background!");

        try{
            // run RSync
            $exclude = "--exclude=db/ --exclude=keys/ --exclude=log/ --exclude=vendor/ --exclude=statistics/ ";
            $cmd = "rsync -apvlz --stats --delete ".$exclude." ".POOLSERVER."::ECPPool/OVZ/ /srv/jobsystem";
            $exitstatus = $this->Cli->execute($cmd);
            if($exitstatus > 0) return $this->commandFailed("problems with RSync ECPPool/OVZ transfer. (RSync code:".$exitstatus.")");
        } catch(\Exception $e) {
            // error
            $this->Done = 2;
            $this->Error = "Update pool failed: ".$e->getMessage();
            $this->Context->getLogger()->debug($this->Error);
            return 1;
        }

        // everything ok
        $this->Done = 1;    
        $this->Context->getLogger()->debug("Update Pool success.");
        return 0;
    }
}
