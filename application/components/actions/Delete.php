<?php
/*
Application Developed By Abhinav Software
Developer: Ankur Gupta
Email: contact@abhinavsoftware.com
Mobile: 09977139265
*/
namespace app\components\actions;


use app\components\AjaxResponse;
use Yii;
use yii\base\Action;

class Delete extends Action{
    public $modelClass;    
    public function run(){
        $modelClass = $this->modelClass;        
        $id = Yii::$app->request->post("id");        
        $modelClass::findOne($id)->delete();
        AjaxResponse::i()->send();
    }
    
}