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

namespace RNTForest\lxd\utilities;

use RNTForest\core\libraries\RemoteSshConnection;

class JobSystemFilesSender extends \Phalcon\DI\Injectable
{
    private $PathsToJobsystemDirectoriesOnAdminServer = [
        BASE_PATH.'/vendor/rnt-forest/core/jobserver/',
        BASE_PATH.'/vendor/rnt-forest/lxd/jobserver/',
    ];
    
    private $HwsJobsystemRootDir;
    
    private $RemoteSshConnection;
    
    private $logger;
    
    /**
    * 
    * @param RemoteSshConnection $remoteSshConnection
    * @param string $remoteJobsystemRootDir target directory on RemoteServer
    */
    public function __construct(RemoteSshConnection $remoteSshConnection, $remoteJobsystemRootDir){
        $this->RemoteSshConnection = $remoteSshConnection;
        $this->HwsJobsystemRootDir = $remoteJobsystemRootDir;
        
        $this->logger = $this->getDI()['logger'];
    }
    
    public function sendFiles(){
        try{
            foreach($this->PathsToJobsystemDirectoriesOnAdminServer as $pathToJobsystemDirectoryOnAdminServer){
                // iterate recursively over the directory with source code files for jobsystem and store them in a array $files
                // this array consists of the source filepath in the key and the representative destination filepath in the value
                // the second array $directories is for previously creating the needed directories
                $directory = new \RecursiveDirectoryIterator($pathToJobsystemDirectoryOnAdminServer,\FilesystemIterator::SKIP_DOTS);
                $iterator = new \RecursiveIteratorIterator($directory);
                $files = array();
                $directories = array();
                foreach ($iterator as $info) {
                    $localFilepath = $info->getPathname();
                    $destinationFilepath = str_replace($pathToJobsystemDirectoryOnAdminServer,$this->HwsJobsystemRootDir,$localFilepath);
                    $files[$localFilepath] = $destinationFilepath;
                    $destinationDirectory = str_replace($pathToJobsystemDirectoryOnAdminServer,$this->HwsJobsystemRootDir,$info->getPath().'/');
                    $directories[$destinationDirectory] = true;
                }

                foreach($directories as $directory => $novalue){
                    $this->RemoteSshConnection->exec('mkdir -p '.$directory);
                }

                foreach($files as $source => $destination){
                    $this->RemoteSshConnection->sendFile($source, $destination);
                }
                
                // set permissions
                $this->RemoteSshConnection->exec("chmod 660 -R ".$this->HwsJobsystemRootDir.'*');
                $this->RemoteSshConnection->exec("chmod u+X,g+X -R ".$this->HwsJobsystemRootDir);
                // set make JobSystemStarter.php executable
                $this->RemoteSshConnection->exec("chmod 770 ".$this->HwsJobsystemRootDir."JobSystemStarter.php");
                   
            }
        }catch(\Exception $e){
            $error = 'Problem while sending jobsystem source code files: '.$e->getMessage();
            $this->Logger->error('JobSystemFilesSender: '.$error);
            throw new \Exception($error);  
        }  
    }
    
}
