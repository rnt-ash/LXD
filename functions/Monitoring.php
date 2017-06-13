<?php
namespace RNTForest\ovz\functions;

class Monitoring{

    /**
    * Get an array of classpath as key and name as value of the remote monbehaviors.
    * 
    * @return array
    */
    public static function getRemoteBehaviors(){
        return self::buildBehaviorArray('MonBehavior');
    }

    /**
    * Get an array of classpath as key and name as value of the local monbehaviors for virtual servers.
    * 
    * @return array
    */
    public static function getLocalBehaviors(){
        return self::buildBehaviorArray('MonLocalBehavior');
    }

    private static function buildBehaviorArray($needle){
        $behaviors = array();
        
        $directory = new \DirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/../vendor/rnt-forest/ovz/utilities/monbehaviors');
        foreach($directory as $fileInfo){
            if($fileInfo->isDot()) continue;
            $fileName = $fileInfo->getFilename();
            if(strpos($fileName,$needle) > 0){
                $namespace = "\\RNTForest\\ovz\\utilities\\monbehaviors\\";
                $class = str_replace('.php','',$fileName);
                $classpath = $namespace.$class;
                $shortname = substr($fileName,0,strpos($fileName,$needle));
                $behaviors[$classpath] = $shortname;        
            }
        }
        
        return $behaviors;
    }
}
