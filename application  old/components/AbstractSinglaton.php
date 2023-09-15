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
abstract class AbstractSinglaton extends \yii\base\BaseObject{
    /**
     * @staticvar null $instance 
     * @return \static
     */
    public static function i(){
        static $instance = null;
        if (null === $instance) {            
            $instance = new static();            
            $instance->init();
        }
        return $instance;
    }
    protected function __construct() { }     
    public function init(){
        
    }
}

