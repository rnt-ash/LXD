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

class OvzUpdateAuthorizedkeysJob extends AbstractOvzJob {

    public static function usage(){
        return [
            "type" => "ovz_update_authorizedkeys",
            "description" => "updates the file /root/.ssh/authorized_keys with all the keys from managed PhysicalServers",
            "params" => [
                "ROOTKEYS" => [
                    "ssh key of the 1st server",
                    "ssh key of the 2nd server",
                    "ssh key of the 3rd server",
                    "ssh key of the 4th server",
                ],
            ],
            "params_example" => '{"SSHKEYS":["ssh-rsa s0m3k3y sometext","ssh-rsa s0m3key sometext"]}',
            "retval" => 'nothing specified',
            "warning" => "nothing specified",
            "error" => "different causes ()",
        ];
    }
    
    public function run() {
        $this->Context->getLogger()->debug("Update authorized_keys!");
        
        // read file line by line and skip those who does not have the substring [ovz] in it
        // (they have been added manually and thus should not be updatet from this job)
        $fileName = "/root/.ssh/authorized_keys";
        try{
            if(!file_exists($file)) $this->Context->getCli()->execute('touch '.$fileName);

            $newFileContent = "";
            
            if ($file = fopen($fileName, "r")) {
                while (($line = fgets($file)) !== false) {
                    $this->Context->getLogger()->debug("Line: ".$line);
                    if(strpos($line,'[ovz]')) {
                        $this->Context->getLogger()->debug("Skip Line: ".$line);
                        continue;
                    }
                    $newFileContent .= $line;
                }
                fclose($file);
            }
            
            $rootKeys = $this->Params["ROOTKEYS"];
            foreach($rootKeys as $key){
                $newFileContent .= trim($key)." [ovz]\n";
            }
            
            $this->Context->getLogger()->debug("write: '".$newFileContent."'");
            if(file_put_contents($fileName,$newFileContent) === false){
                throw new \Exception("Could not write ".$fileName);
            }
                    
            $this->Done = 1;    
            $this->Context->getLogger()->debug("Update authorized_keys Success.");
            
        }catch(\Exception $e){
            $this->Done = 2;
            $this->Error = $e->getMessage();
            $this->Context->getLogger()->error($this->Error);
        }
    }
}
