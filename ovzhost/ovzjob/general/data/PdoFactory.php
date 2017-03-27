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

namespace RNTForest\OVZJOB\general\data;

class PdoFactory{
    
    /**
    * @return \PDO
    */
    public static function createSqlitePdo(){
        $pdo = new \PDO('sqlite:/srv/ovzhost/db/ovzcp.db');
        $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (".
            "id INTEGER UNIQUE,".
            "type TEXT,".
            "params TEXT,".
            "executed NUMERIC,".
            "done INTEGER,".
            "error TEXT,".
            "warning TEXT,".
            "retval TEXT".
            ");");
        return $pdo;
    }
}
