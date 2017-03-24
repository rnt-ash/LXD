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

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/../ovzcp.local.config.php');

use RNTForest\OVZJOB\general\data\PdoFactory;
use RNTForest\OVZJOB\general\psrlogger\FileLogger;
use RNTForest\OVZJOB\general\cli\LoggingCli;
use RNTForest\OVZJOB\general\repository\DbJobDTORepository;
use RNTForest\OVZJOB\general\utility\Context;
use RNTForest\OVZJOB\general\httpservice\RestRouter;

class RestServiceStarter{
    private $Context;
    
    public function __construct(){
        $logger = new FileLogger(LOGFILE);
        $logger->setLogLevel(LOGLEVEL);
        $pdo = PdoFactory::createSqlitePdo();
        $cli = new LoggingCli($logger);
        $jobDtoRepo = new DbJobDTORepository($pdo, $logger);
        $this->Context = new Context($pdo,$logger,$cli, $jobDtoRepo);
    }
    
    public function startRest(){
        $restRouter = new RestRouter($this->Context);
        $this->Context->getLogger()->debug("RestService started...");
        $restRouter->handleRequest();
    }
}
            
$starter = new RestServiceStarter();
$starter->startRest();
