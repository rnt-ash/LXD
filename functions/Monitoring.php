<?php
namespace RNTForest\ovz\functions;

class Monitoring{

    private static function buildBehaviorArray(){
        $behaviors = array();
        
        $directory = new \DirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/../vendor/rnt-forest/ovz/utilities/monbehaviors');
        foreach($directory as $fileInfo){
            if($fileInfo->isDot()) continue;
            $fileName = $fileInfo->getFilename();
            $info = [];
            $namespace = "\\RNTForest\\ovz\\utilities\\monbehaviors\\";
            $class = str_replace('.php','',$fileName);
            $classpath = $namespace.$class;
            $info['classpath'] = $classpath;
            $info['classname'] = $class;
            $info['params'] = '';
            $behaviors[$classpath] = $info;        
        }
        
        return $behaviors;
    }
    
    /**
    * 
    * @param string $serverType 'virtual' or 'physical'
    */
    public static function getAllBehaviors($serverType){
        $cleanedBehaviors = [];
        
        $behaviors = self::buildBehaviorArray();

        foreach($behaviors as $key=>$val){
            $classPath = $val['classpath'];
            $className = $val['classname'];
            $params = $val['params'];
            
            // if it is local behavior, params have to be set
            if(strpos($className,'MonLocal') !== false){
                $info = [];
                $key = $classPath;
                
                // set params
                switch($className){
                    case 'DiskspacefreeMonLocalBehavior':
                        $info['shortname'] = 'Diskspace /';
                        if($serverType == 'virtual'){
                            $info['params'] = '["FsInfo","/","free_gb"]';
                        }elseif($serverType == 'physical'){
                            $info['params'] = '["FsInfo","/","free_gb"]';
                            $key = $classPath.'_root';
                        }
                        break;
                    case 'CpuloadMonLocalBehavior':
                        $info['shortname'] = 'CPU Load';
                        if($serverType == 'virtual'){
                            $info['params'] = '["guest","cpu","usage"]';
                        }elseif($serverType == 'physical'){
                            $info['params'] = '["cpu_load"]';
                        }
                        break;
                    case 'MemoryfreeMonLocalBehavior':
                        $info['shortname'] = 'RAM';
                        if($serverType == 'virtual'){
                            $info['params'] = '["guest","ram","memory_free_mb"]';
                        }elseif($serverType == 'physical'){
                            $info['params'] = '["memory_free_mb"]';
                        }
                        break;
                    default:
                        // no default spec is possible because of the needed params in local monitoring
                        break;
                }
                
                if(!empty($info)){
                    $info['classpath'] = $classPath;
                    $cleanedBehaviors[$key] = $info;
                }
                
                // physical has additional diskspace possibility
                if($className == 'DiskspacefreeMonLocalBehavior' && $serverType == 'physical'){
                    $additional = [];
                    $additional['classpath'] = $classPath;
                    $additional['shortname'] = 'Diskspace /vz';
                    $additional['params'] = '["FsInfo","/vz","free_gb"]';
                    $cleanedBehaviors[$classPath.'_vz'] = $additional;
                }
            }else{
                $info = [];
                $key = $classPath;
                
                switch($className){
                    case 'DnsMonBehavior':
                        $info['shortname'] = 'DNS (Port 53)';
                        break;
                    case 'FtpMonBehavior':
                        $info['shortname'] = 'FTP (Port 21)';
                        break;
                    case 'HttpMonBehavior':
                        $info['shortname'] = 'HTTP (Port 80)';
                        break;
                    case 'MysqlMonBehavior':
                        $info['shortname'] = 'MySQL (Port 3306)';
                        break;
                    case 'SmtpMonBehavior':
                        $info['shortname'] = 'SMTP (Port 25)';
                        break;
                    case 'SshMonBehavior':
                        $info['shortname'] = 'SSH (Port 22)';
                        break;
                    case 'PingMonBehavior':
                        $info['shortname'] = 'Ping (ICMP Echo Reply)';
                        break;
                    default:
                        $info['shortname'] = $className;
                        break;
                }
                
                if(!empty($info)){
                    $info['classpath'] = $classPath;
                    $info['params'] = null;
                    $cleanedBehaviors[$key] = $info;
                }
            }
            
            
        }
        
        return $cleanedBehaviors;
    }
}
