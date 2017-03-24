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

namespace RNTForest\OVZJOB\ovz\jobs;

class OvzGetRootpublickeyJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_get_rootpublickey",
            "description" => "get a JSON of the root ssh public key uf the server (file /root/.ssh/id_rsa.pub)",
            "params" => [],
            "params_example" => '',
            "retval" => 'JSON with 1 string, e.g. "ssh-rsa s0m3publ1ck3y root@server.domain.tld"',
            "warning" => "nothing specified",
            "error" => "different causes (public key file not found)",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Get Root Public Key!");
        
        $pubKeyFile = '/root/.ssh/id_rsa.pub';
        try{
            if(!file_exists($pubKeyFile)) throw new \Exception("Public key file does not exist.");
            $pubKeyFileContent = file_get_contents($pubKeyFile);
            $this->Retval = json_encode($pubKeyFileContent);
            $this->Done = 1;
            $this->Context->getLogger()->debug("Get Root Public Key Success.");
        }catch(\Exception $e){
            $this->Done = 2;
            $this->Error = $e->getMessage();
            $this->Context->getLogger()->error($e->getMessage());
        }
    }
}
