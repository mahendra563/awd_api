<?php
/*
Application Developed By Abhinav Software
Website: http://abhinavsoftware.com
Email: contact@abhinavsoftware.com
Developer: Ankur Gupta (ankurgupta555@gmail.com)
Copyright Ankur Gupta

For licensing and terms of use please read license.txt file
*/

namespace app\components;

class MediaManager extends AbstractSinglaton{
    
    public $baseFolder = "";
    
    public function upload($uploadedFile,$existingFile=""){
        if(is_null($uploadedFile)){
            return $existingFile;
        } 
        $baseDir = \Yii::getAlias("@webroot/uploads")."/";  
        if(!file_exists($baseDir)){
            mkdir($baseDir);
        } 
        if(trim($this->baseFolder) == ""){
            $monthDir = date("Y-m")."/";  
            if(!file_exists($baseDir.$monthDir)){
                mkdir($baseDir.$monthDir);                   
            }
        } else {
            $monthDir = date("Y-m")."/";  
            if(!file_exists($baseDir.$this->baseFolder."/".$monthDir)){
                mkdir($baseDir.$this->baseFolder."/".$monthDir);                   
            }
        } 
     
        if(trim($existingFile)!==""){
            $this->delete($existingFile);
        }
        
        $filename = $this->baseFolder."/".$monthDir.$uploadedFile->baseName.".".$uploadedFile->getExtension();
        if(trim($this->baseFolder) == ""){
            $filename = $monthDir.$uploadedFile->baseName.".".$uploadedFile->getExtension();
        }
        if(file_exists($baseDir.$filename)){
        
            $filename = $this->baseFolder."/".$monthDir.$uploadedFile->baseName.'-'. uniqid().".".$uploadedFile->getExtension();
            if(trim($this->baseFolder) == ""){
                $filename = $monthDir.$uploadedFile->baseName.'-'. uniqid().".".$uploadedFile->getExtension();
            }
            
        }
        $uploadedFile->saveAs($baseDir.$filename);
        
        
        return $filename;
    }
    
    public function downloadFromUrl($url,$existingFile=""){
        if(trim($url) == ""){
            return $existingFile;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return $existingFile;
        }
        $baseDir = \Yii::getAlias("@webroot/uploads")."/".$this->baseFolder."/"; 
        if(trim($this->baseFolder) == ""){
            $baseDir = \Yii::getAlias("@webroot/uploads")."/"; 
        }
        
        $monthDir = date("Y-m")."/";  
        if(trim($this->baseFolder) == ""){            
            if(!file_exists($baseDir.$monthDir)){
                mkdir($baseDir.$monthDir);                   
            }
        } else {            
            if(!file_exists($baseDir.$this->baseFolder."/".$monthDir)){
                mkdir($baseDir.$this->baseFolder."/".$monthDir);                   
            }
        } 
        
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $newfile = $this->baseFolder."/".$monthDir.uniqid().".".$ext;
        if(trim($this->baseFolder) == ""){
            $filename = $monthDir.uniqid().".".$ext;
        }
        file_put_contents($baseDir.$filename, fopen($url, 'r'));
        return $filename;
    }


    public function duplicate($filename){
        if(trim($filename)!=""){                         
            $baseDir = \Yii::getAlias("@webroot/uploads")."/".$this->baseFolder."/"; 
            if(trim($this->baseFolder) == ""){
                $baseDir = \Yii::getAlias("@webroot/uploads")."/"; 
            }
            $monthDir = date("Y-m")."/"; 
            if(!file_exists($baseDir.$monthDir)){
                mkdir($baseDir.$monthDir);                   
            }

            $newfile = $this->baseFolder."/".$monthDir.pathinfo($filename,PATHINFO_FILENAME)."_copy.".pathinfo($filename,PATHINFO_EXTENSION);
            if(trim($this->baseFolder) == ""){
                $newfile = $monthDir.pathinfo($filename,PATHINFO_FILENAME)."_copy.".pathinfo($filename,PATHINFO_EXTENSION);
            }

            $origFilePath = $baseDir.$filename;
            $destFilePath = $baseDir.$newfile;

            if(file_exists($origFilePath)){
                copy($origFilePath, $destFilePath);
                return $newfile;
            }
        }
        return false;
    }
    
    public function delete($filename){
        if(trim($filename)!=""){  
             
                $file = \Yii::getAlias("@webroot/uploads")."/".$filename;
                if(trim($this->baseFolder) == ""){
                    $file = \Yii::getAlias("@webroot/uploads")."/".$filename;
                }                
                if(file_exists($file)){
                    unlink($file);
                    return true;
                }
            
            return true;
        }
        return false;
    }
  
}