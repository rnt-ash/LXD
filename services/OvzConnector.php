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

namespace RNTForest\ovz\services;

use RNTForest\ovz\models\PhysicalServers;
use RNTForest\core\libraries\RemoteSshConnection;

/**
* The OvzConnector is used to connect a preinstalled OpenVZ 7 server to the OVZCP.
* Consult the INSTALL-OVZ7LEMP.md to read about the correct usage.
* Connector will be called from the Phalcon App in a wizard like manier.
* 
* The Connector does the following to the OpenVZ 7 Server:
* - checks the hostsystem (openvz 7 system?, php >= 5.4?)
* - makes general configurations (postfix, ntp, ssh)
* - generates asymmetrical keypair, saves it locally on the host and saves the public key in the central db in phalcon app
* - puts the public key of the adminserver (where the phalcon app runs) as a file on the host
* - creates and configures a linux user/group for running the webservice
* - sends all files in the jobsystem directory to the host
* - creates directories for db and logs
* - installation and configuration of composer
* - sets file permissions
* - configures sudoers, so that the job-component can be started with root permissions
* - configures ovzcp.service (systemd)
* - checks OpenVZ 7 kernel, prlctl command, ploop and directories
* - configures OpenVZ 7
* - writes the ovzcp.local.config.php
* - sends a test job (general_test_sendmail to the specified rootalias)
* - sets the physical server to OVZ=1 if successfully connected
* 
* Can be executed more than once.
*/
class OvzConnector extends \Phalcon\DI\Injectable
{
    private $ConfigOvzJobsystemRootDir = '/srv/jobsystem/';
    private $ConfigMyPublicKeyFilePath = '/srv/jobsystem/keys/public.pem';
    private $ConfigMyPrivateKeyFilePath = '/srv/jobsystem/keys/private.key';
    private $ConfigAdminPublicKeyFilePath = '/srv/jobsystem/keys/adminpublic.key';

    private $PathsToJobsystemDirectoriesOnAdminServer = [
        BASE_PATH.'/vendor/rnt-forest/core/jobserver/',
        BASE_PATH.'/vendor/rnt-forest/ovz/jobserver/',
    ];

    /**
    * @var Dependency Injection
    */
    private $di;
    
    /**
    * @var mixed
    */
    private $Logger;
    
    /**
    * @var RemoteSshConnection
    */
    private $RemoteSshConnection; 
    
    /**
    * @var PhysicalServers
    */
    private $PhysicalServer;
    
    /**
    * Username for authenticate ssh connection
    * 
    * @var string
    */
    private $RootUsername;
    
    /**
    * Password for authenticate ssh connection
    * 
    * @var string
    */
    private $RootPassword;
    
    public function __construct(PhysicalServers $physicalServer, $rootUsername, $rootPassword){
        $this->di = $this->getDI();
        $this->Logger = $this->getDI()['logger'];
        $this->PhysicalServer = $physicalServer;
        $this->RootUsername = $rootUsername;
        $this->RootPassword = $rootPassword;
        $this->RemoteSshConnection = new RemoteSshConnection($this->di, $this->PhysicalServer->getFqdn(), $this->RootUsername, $this->RootPassword);
    }
    
    public function go(){
        if(!$this->RemoteSshConnection->isOpen()){
            throw new \Exception("Remote SSH Connection is not open. Aborting...");
        }
        $this->checkEnvironment();
        $this->preInstallation();
        
        $this->createAsymmetricKeys();
        $this->fetchPublicRootSshKey();
        $this->sendAdminPublicKeyToRemoteserver();
        $this->createLinuxUserAndGroup();
        $this->writeLocalConfig();
        $this->copyJobsystemScriptsToServer();
        $this->prepareFurtherJobsystemDirectories();
        $this->configureComposer();
        $this->cleanPermissionsInJobsystemDirectories();
        $this->configureSudoers();
        $this->configureOvzcpService();
        $this->configureOvz();
        
        $this->postInstallation();
        $this->testJobSystem();
    }
    
    private function checkEnvironment(){
        try{
            $output = $this->RemoteSshConnection->exec('cat /etc/system-release');    
            if(!preg_match('`^(OpenVZ release 7.0).*$`',$output)){
                throw new \Exception("Wrong linux distribution. Accepted: OpenVZ 7.0.x");       
            }
            
            unset($output);
            $output = $this->RemoteSshConnection->exec('php -v');
            if(!preg_match('`^(PHP 5.4).*`',$output)){
                throw new \Exception("No accepted PHP version found. Accepted: PHP 5.4.x (yum install php-cli)");
            }
            
            unset($output);
            $output = $this->RemoteSshConnection->exec('php -m');
            if(!preg_match('`PDO`',$output)){
                throw new \Exception("PHP PDO Extension not found. (yum install php-pdo)");
            }
            
            unset($output);
            $output = $this->RemoteSshConnection->exec('rpm -qa | grep mailx');
            if(!preg_match('`mailx`',$output)){
                throw new \Exception("Mailx not found. (yum install mailx)");
            }
            
        }catch(\Exception $e){
            $error = 'System is not supported: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);
        }
    }
    
    private function preInstallation(){
        try{
            // control if SSH Key exists, if not create a new pair
            $files = array('/root/.ssh/id_rsa','/root/.ssh/id_rsa.pub');
            $id_rsa = true;
            foreach($files as $file) {
                $output = $this->RemoteSshConnection->exec('if [ -f "'.$file.'" ]; then echo "1"; else echo 0; fi');
                if(intval($output)!=1) $id_rsa = false;
            }
            if(!$id_rsa){
                $this->RemoteSshConnection->exec('rm -f /root/.ssh/id_rsa');
                $this->RemoteSshConnection->exec('rm -f /root/.ssh/id_rsa.pub');
                $this->RemoteSshConnection->exec('ssh-keygen -b 2048 -t rsa -f /root/.ssh/id_rsa -q -N ""');
            }
            
            // configure Postfix
            $relayhost = $this->di['config']->mail['relayhost'];
            $rootalias = $this->di['config']->mail['rootalias'];
            if(!empty($relayhost) && !empty($rootalias)){
                $sshCommandToSetRelayhost = 'sed -i \'s/.*#relayhost\\x20\\x3D\\x20\[gateway.*/relayhost = ['.$relayhost.']/\' /etc/postfix/main.cf';
                $sshCommandToSetAdminAddress = 'sed -i \'s/.*#root:.*/root:'.$rootalias.'/\' /etc/aliases';
                $output = $this->RemoteSshConnection->exec($sshCommandToSetRelayhost);
                $output = $this->RemoteSshConnection->exec($sshCommandToSetAdminAddress);
                $output = $this->RemoteSshConnection->exec('newaliases');
            }
            
            // configure ntp
            $output = $this->RemoteSshConnection->exec('systemctl enable ntpd');
            $output = $this->RemoteSshConnection->exec('ntpdate pool.ntp.org');
            $output = $this->RemoteSshConnection->exec('systemctl start ntpd');
                
        }catch (\Exception $e) {                                                                                 
            $error = 'Error while preInstallation: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);
        }
    }
    
    private function createAsymmetricKeys(){
        try{
            $this->createDirectoryIfNotExists($this->ConfigOvzJobsystemRootDir.'keys');
            
            // keys are generated each time because it is cheap and more reliable
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );
                
            // Create the private and public key
            $res = openssl_pkey_new($config);
            // Extract the private key from $res to $privKey
            openssl_pkey_export($res, $privKey);

            // Extract the public key from $res to $pubKey
            $pub = openssl_pkey_get_details($res);
            $pubKey = $pub['key'];
            
            // save the keypair on the server in files
            $this->RemoteSshConnection->exec('echo "'.$pubKey.'" > '.$this->ConfigMyPublicKeyFilePath);
            $this->RemoteSshConnection->exec('echo "'.$privKey.'" > '.$this->ConfigMyPrivateKeyFilePath);
                    
            // and update the public key in the model 
            $this->PhysicalServer->setJobPublicKey($pubKey);
            $this->PhysicalServer->save();
        }catch(\Exception $e){
            $error = 'Problem while creating asymmetric keypair: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function fetchPublicRootSshKey(){
        try{
            $output = $this->RemoteSshConnection->exec("cat /root/.ssh/id_rsa.pub");
            $this->PhysicalServer->setRootPublicKey($output);
        }catch(\Exception $e){               
            $error = 'Problem while receiving root public key: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function sendAdminPublicKeyToRemoteserver(){
        try{
            $source = $this->di['config']->push['adminpublickeyfile'];
            if(!file_exists($source)){
                throw new \Exception("Public key file of adminserver does not exist in path '".$source."'. Please create this file (see INSTALL)");
            }
            $destination = $this->ConfigAdminPublicKeyFilePath;
            $this->RemoteSshConnection->sendFile($source, $destination);
        }catch(\Exception $e){               
            $error = 'Problem while sending admin public key: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function createLinuxUserAndGroup(){
        try{
            // --system means no shell and no password
            // --user-group means that it creates a group with same name
            $this->RemoteSshConnection->exec('useradd --system --user-group ovzcp');
        }catch(\Exception $e){
            $error = 'Problem while creating linux user and group: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function copyJobsystemScriptsToServer(){
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
                    $destinationFilepath = str_replace($pathToJobsystemDirectoryOnAdminServer,$this->ConfigOvzJobsystemRootDir,$localFilepath);
                    $files[$localFilepath] = $destinationFilepath;
                    $destinationDirectory = str_replace($pathToJobsystemDirectoryOnAdminServer,$this->ConfigOvzJobsystemRootDir,$info->getPath().'/');
                    $directories[$destinationDirectory] = true;
                }

                foreach($directories as $directory => $novalue){
                    $this->RemoteSshConnection->exec('mkdir -p '.$directory);
                }

                foreach($files as $source => $destination){
                    $this->RemoteSshConnection->sendFile($source, $destination);
                }   
            }
        }catch(\Exception $e){
            $error = 'Problem while sending jobsystem source code files: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }        
    }
    
    private function prepareFurtherJobsystemDirectories(){
        try{
            $folders = array(
                $this->ConfigOvzJobsystemRootDir.'db',
                $this->ConfigOvzJobsystemRootDir.'log',
            );
            foreach($folders as $folder) {
                $this->createDirectoryIfNotExists($folder);
            }
        }catch(\Exception $e){
            $error = 'Problem while creating further jobsystem directories: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function configureComposer(){
        try{
            $output = $this->RemoteSshConnection->exec('curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 2>&1');
            if($this->RemoteSshConnection->getLastExitStatus() != 0){
                throw new \Exception('Could not install composer. Got exitcode '.$exitCode.' and output: "'.$output.'"');
            }
            $output = $this->RemoteSshConnection->exec('(cd '.$this->ConfigOvzJobsystemRootDir.'; composer update 2>&1)');
            if($this->RemoteSshConnection->getLastExitStatus() != 0){
                throw new \Exception('Could not update composer. Got exitcode '.$exitCode.' and output: "'.$output.'"');
            }
        }catch(\Exception $e){
            $error = 'Problem while configuring composer: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function cleanPermissionsInJobsystemDirectories(){
        try{
            $this->RemoteSshConnection->exec('chown root:ovzcp -R '.$this->ConfigOvzJobsystemRootDir.'*');
            $this->RemoteSshConnection->exec('chmod 640 -R '.$this->ConfigOvzJobsystemRootDir.'*');
            $this->RemoteSshConnection->exec('chmod 660 -R '.$this->ConfigOvzJobsystemRootDir.'log');
            $this->RemoteSshConnection->exec('chmod 660 -R '.$this->ConfigOvzJobsystemRootDir.'db');
            $this->RemoteSshConnection->exec('chmod u+X,g+X -R '.$this->ConfigOvzJobsystemRootDir.'*');
            $this->RemoteSshConnection->exec('chmod 750 '.$this->ConfigOvzJobsystemRootDir.'JobSystemStarter.php');
        }catch(\Exception $e){
            $error = 'Problem while cleaning permissions in jobsystem directories: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function configureSudoers(){
        try{
            $output = $this->RemoteSshConnection->exec('cat /etc/sudoers | grep \'ovzcp ALL=(ALL)\'');
            if(empty($output)){
                $sudoersConfig = "ovzcp ALL=(ALL) NOPASSWD: /srv/jobsystem/JobSystemStarter.php\n".
                    "Defaults!/srv/jobsystem/JobSystemStarter.php !requiretty\n";
                $this->RemoteSshConnection->exec('echo "'.$sudoersConfig.'" >> /etc/sudoers');
            }         
        }catch(\Exception $e){
            $error = 'Problem while configuring sudoers: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        }
    }
    
    private function configureOvzcpService(){
        try{
            $serviceFile = '/usr/lib/systemd/system/ovzcp.service';
            $ovzcpServiceFileConfig = 
                "[Unit]\n".
                "Description=OpenVZ Control Panel Remoteserver Service\n".
                "After=network.target\n".
                "\n".
                "[Service]\n".
                "Type=simple\n".
                "ExecStart=/usr/bin/php -S ".$this->PhysicalServer->getFqdn().":8000 /srv/jobsystem/RestServiceStarter.php\n".
                "WorkingDirectory=/srv/jobsystem\n".
                "User=ovzcp\n".
                "Restart=on-failure\n".
                "\n".
                "[Install]\n".
                "WantedBy=multi-user.target\n";
            
            // try to stop the service (if it exists already)
            $this->RemoteSshConnection->exec('systemctl stop ovzcp.service 2>&1');
            
            // write complete file new
            $this->RemoteSshConnection->exec('echo "'.$ovzcpServiceFileConfig.'" > '.$serviceFile);
            
            // link the init file
            $this->RemoteSshConnection->exec('ln -s '.$serviceFile.' /etc/systemd/system/multi-user.target.wants/ovzcp.service');
            
            // start the service
            $this->RemoteSshConnection->exec('systemctl daemon-reload');
            $output = $this->RemoteSshConnection->exec('systemctl start ovzcp.service');
            if($this->RemoteSshConnection->getLastExitStatus() != 0){
                throw new \Exception('Could not start ovzcp.service. Got exitcode '.$exitCode.' and output: "'.$output.'"');
            }
            
        }catch(\Exception $e){
            $error = 'Problem while configuring ovzcp.service: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);  
        } 
    }
    
    private function configureOvz(){
        try{
            // check kernel
            $output = $this->RemoteSshConnection->exec('uname -r');
            // kernel should be 3.10 with keyword vz7 in it
            if(!preg_match('`^(3.10).*(vz7).*`',$output)){
                throw new \Exception("Wrong linux kernel. Accepted: 3.10");       
            }
            
            //  check prlctl
            $output = $this->RemoteSshConnection->exec('whereis prlctl');
            // whereis output is searched binary followed with a : and the location
            // if the output string is not longer than "prlctl:" it was not found and will be missing
            if(!(strlen($output) > strlen("prlctl:"))){
                throw new \Exception("prlctl not found/installed. there may be not a proper openvz installation. please consider the official openvz installation guide.");  
            }
            
            // check ploop
            $output = $this->RemoteSshConnection->exec('whereis ploop');
            if(!(strlen($output) > strlen("ploop:"))){
                throw new \Exception("ploop not found/installed. there may be not a proper openvz installation. please consider the official openvz installation guide.");  
            } 
            
            $this->createOvzDirAndFilesIfNotExists();
            $this->modifyVzConfFile();
            
            // temporary not needed
            // $this->RemoteSshConnection->exec('systemctl restart vz');
                  
        }catch(\Exception $e){
            $error = 'Problem while configuring OpenVz: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);
        }    
    } 
    
    private function createOvzDirAndFilesIfNotExists(){
        $folders = array(
            '/vz',
            '/vz/private',
            '/vz/root',
            '/vz/template',
            '/vz/mnt',
            '/etc/vz',
            '/etc/vz/conf',
        );
        foreach($folders as $folder) {
            $this->createDirectoryIfNotExists($folder);
        }
    }
    
    private function modifyVzConfFile(){
        // Define default os template -> debian stretch
        $this->RemoteSshConnection->exec('sed -i -r '.
            '\'s/^#?DEF_OSTEMPLATE.*/DEF_OSTEMPLATE="debian-8.0-x86_64-minimal"/\' /etc/vz/vz.conf'); 
    }
    
    private function writeLocalConfig(){
        try{
            $config = ''.
                "<?php\n".
                "\t"."// Server"."\n".
                "\t"."define('SERVERFQDN','".$this->PhysicalServer->getFqdn()."');"."\n".
                "\t".""."\n".
                "\t"."// FileLogger"."\n".
                "\t"."define('LOGFILE','".$this->ConfigOvzJobsystemRootDir."log/filelogger.log');"."\n".
                "\t"."define('LOGLEVEL','NOTICE');"."\n".
                "\t".""."\n".
                "\t"."// OVZ"."\n".
                "\t"."define('OVZ_PRIVATE_PATH','/vz/private');"."\n".
                "\t".""."\n".
                "\t"."// Key Pair"."\n".
                "\t"."define('PUBLIC_KEY_FILE','".$this->ConfigMyPublicKeyFilePath."');"."\n".
                "\t"."define('PRIVATE_KEY_FILE','".$this->ConfigMyPrivateKeyFilePath."');"."\n".
                "\t"."define('ADMIN_PUBLIC_KEY_FILE','".$this->ConfigAdminPublicKeyFilePath."');"."\n".
                "\t"."define('SYSTEMWIDE_SECRETKEY','".$this->di['config']->push['jwtsigningkey']."');"."\n".
                '';
            $configFilepath = '/srv/local.config.php';
            $this->RemoteSshConnection->exec('echo "'.$config.'" > '.$configFilepath);
            $this->RemoteSshConnection->exec('chown root:ovzcp '.$configFilepath);    
            $this->RemoteSshConnection->exec('chmod 640 '.$configFilepath);
        }catch(\Exception $e){
            $error = 'Problem while writing ovzcp local config: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error);
        }    
    }
    
    private function postInstallation(){
        try{
            $this->PhysicalServer->setOvz(1);
            $this->PhysicalServer->save(); 
        }catch(\Exception $e){
            $error = 'Problem in post installation: '.$this->MakePrettyException($e);
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error); 
        }
    }
    
    private function testJobSystem(){
        $params = array("TO"=>$this->di['config']->mail['rootalias'],"MESSAGE"=>'This is a test message generated from the Connector while connecting to '.$this->PhysicalServer->getFqdn());
        $push = $this->getPushService();
        $job = $push->executeJob($this->PhysicalServer,'general_test_sendmail',$params);

        if($job->getDone() != 1){
            $error = 'Problem in testing job system: '.$job->getError();
            $this->Logger->error('OvzConnector: '.$error);
            throw new \Exception($error); 
        }
    }
    
    /**
    * dummy method only for auto completion purpose
    * 
    * @return \RNTForest\core\services\Push
    */
    private function getPushService(){
        return $this->di['push'];
    }
    
    /**
    * Create the specified path in the remoteconnection.
    * 
    * @param string $path
    * @throws Exception
    */
    private function createDirectoryIfNotExists($path){
        // create directories and check afterwards if they exists
        // -p option "no error if existing, make parent directories as needed"
        $this->RemoteSshConnection->exec('mkdir -p '.$path);
        $output = $this->RemoteSshConnection->exec('if [ -d "'.$path.'" ]; 
                                                then echo "1"; else echo 0; fi');
        if(intval($output)!=1){
            throw new \Exception("Directory ".$path." not found after creation (could not be created...)");   
        }        
    }
    
    /**
    * Hilfsfunktion um schöne Exception-Strings erstellen zu können. Anstatt $e->getMessage() im catch-Block aufzurufen
    * 
    * @param Exception $e
    * @return String
    */
    private function MakePrettyException(\Exception $e) {
        $trace = $e->getTrace();

        $result = 'Exception: "';
        $result .= $e->getMessage();
        $result .= '" @ ';   
        if($trace[0]['class'] != '') {
            $result .= $trace[0]['class'];
            $result .= '->';
        }
        $result .= $trace[0]['function'];
        $result .= '(); on Line '.$e->getLine().'<br />';

        return $result;
    }
}
