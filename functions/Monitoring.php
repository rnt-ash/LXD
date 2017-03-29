<?php
namespace RNTForest\ovz\functions;

class Monitoring{

    public static function getRemoteBehaviors(){
        $behaviors = array();
        
        $directory = new \DirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/../vendor/rnt-forest/ovz/utilities/monbehaviors');
        foreach($directory as $fileInfo){
            if($fileInfo->isDot()) continue;
            $fileName = $fileInfo->getFilename();
            if(strpos($fileName,'MonBehavior') > 0){
                $namespace = "\\RNTForest\\ovz\\utilities\\monbehaviors\\";
                $class = str_replace('.php','',$fileName);
                $classpath = $namespace.$class;
                $shortname = substr($fileName,0,strpos($fileName,'MonBehavior'));
                $behaviors[$classpath] = $shortname;        
            }
        }
        
        return $behaviors;
    }
    
    public static function getLocalVirtualBehaviors(){
        $behaviors = array();
        
        $directory = new \DirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/../vendor/rnt-forest/ovz/utilities/monbehaviors');
        foreach($directory as $fileInfo){
            if($fileInfo->isDot()) continue;
            $fileName = $fileInfo->getFilename();
            if(strpos($fileName,'VirtMonLocalBehavior') > 0){
                $namespace = "\\RNTForest\\ovz\\utilities\\monbehaviors\\";
                $class = str_replace('.php','',$fileName);
                $classpath = $namespace.$class;
                $shortname = substr($fileName,0,strpos($fileName,'VirtMonLocalBehavior'));
                $behaviors[$classpath] = $shortname;        
            }
        }
        
        return $behaviors;
    }

    public static function getLocalPhysicalBehaviors(){
        $behaviors = array();
        
        $directory = new \DirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/../vendor/rnt-forest/ovz/utilities/monbehaviors');
        foreach($directory as $fileInfo){
            if($fileInfo->isDot()) continue;
            $fileName = $fileInfo->getFilename();
            if(strpos($fileName,'PhysMonLocalBehavior') > 0){
                $namespace = "\\RNTForest\\ovz\\utilities\\monbehaviors\\";
                $class = str_replace('.php','',$fileName);
                $classpath = $namespace.$class;
                $shortname = substr($fileName,0,strpos($fileName,'PhysMonLocalBehavior'));
                $behaviors[$classpath] = $shortname;        
            }
        }
        
        return $behaviors;
    }
}
