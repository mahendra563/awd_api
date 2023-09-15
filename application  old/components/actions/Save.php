<?php
/*
Application Developed By Abhinav Software
Developer: Ankur Gupta
Email: contact@abhinavsoftware.com
Mobile: 09977139265
*/

namespace app\components\actions;

use app\components\AjaxResponse;
use app\components\Helpers;
use Yii;
use yii\base\Action;

class Save extends Action{
    public $modelClass;
    public $pk;
    public function run(){
        $modelClass = $this->modelClass;
        $pk = $this->pk;
        $params = Yii::$app->request->post(Helpers::i()->getClassShortName($modelClass));
        $model = new $modelClass();
        if($params[$pk]>0){
            $model = $modelClass::findOne($params[$pk]);            
        }  
        if($model->load(Yii::$app->request->post()) && $model->validate()){
            $model->save(); 
            AjaxResponse::i()->setStatus(true);
        } else {
            AjaxResponse::i()->setValidationStatus(false)->setValidationErrors($model->getErrors());
        }
        AjaxResponse::i()->send();
    }
    
     
}