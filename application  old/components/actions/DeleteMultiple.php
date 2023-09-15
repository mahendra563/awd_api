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

class DeleteMultiple extends Action{
    public $modelClass;    
    public function run(){
        $modelClass = $this->modelClass;        
        $ids = Yii::$app->request->post("ids",[]);   
       
        foreach($ids as $id){
            $modelClass::findOne($id)->delete();
        }
        AjaxResponse::i()->send();
    }
    
}