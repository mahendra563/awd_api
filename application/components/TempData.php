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

class TempData extends AbstractSinglaton{
    
    private $_config;
    
    public function save($k,$v){
        $this->_config[$k] = $v;
    }
    public function get($k,$default=false){
        if(isset($this->_config[$k])){
            return $this->_config[$k];
        }
        return $default;
    }
    public function getArray(){
        return $this->_config;
    }
    
    public function saveOrGet($key,callable $function){
        $value = $this->get($key,null);        
        if(is_null($value)){
            $value = $function();
            $this->save($key, $value);
        }  
        
     
        
        return $this->get($key,null);
    }
}