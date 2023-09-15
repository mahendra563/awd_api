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

class Get extends Action{
    public $modelClass;    
    public $with;
    public $asArray = true;
    public $encode_fields =[];
    public $purify_fields = [];
    public $beforeServing;
    
    public function run(){
        $modelClass = $this->modelClass;
        $id = Yii::$app->request->get("id");
        
        $model = $modelClass::find()->where($modelClass::primaryKey()[0]." = :id",[":id"=>$id]);
        if($this->asArray){
            $model = $model->asArray();
        }
        if(!is_null($this->with)){
           $model = $model->with($this->with);
        }
        
        $model = $model->one();
        if(is_null($model)){
            AjaxResponse::i()->setError("Not found")->send(); return;
        }
         
        if(!$this->asArray){
            foreach($this->purify_fields as $r){
                if(isset($model->$r)){
                    $model->$r = \yii\helpers\HtmlPurifier::process($model->$r);
                }
                if(is_array($this->with)){
                    foreach($this->with as $w){
                        if(isset($model->$w->$r)){
                            $model->$w->$r = \yii\helpers\HtmlPurifier::process($model->$w->$r);
                        }                        
                    }
                }
            }
            foreach($this->encode_fields as $r){
                if(isset($model->$r)){
                    $model->$r = \yii\helpers\Html::encode($model->$r);
                }
                if(is_array($this->with)){
                    foreach($this->with as $w){
                        if(isset($model->$w->$r)){
                            $model->$w->$r = \yii\helpers\Html::encode($model->$w->$r);
                        }                        
                    }
                }
            } 
        }
        
        if($this->asArray){
            foreach($this->purify_fields as $r){
                if(isset($model[$r])){
                    $model[$r] = \yii\helpers\HtmlPurifier::process($model[$r]);
                }
                if(is_array($this->with)){
                    foreach($this->with as $w){
                        if(isset($model[$w][$r])){
                            $model[$w][$r] = \yii\helpers\HtmlPurifier::process($model[$w][$r]);
                        }                        
                    }
                }
            }
            foreach($this->encode_fields as $r){
                if(isset($model[$r])){
                    $model[$r] = \yii\helpers\Html::encode($model[$r]);
                }
                if(is_array($this->with)){
                    foreach($this->with as $w){
                        if(isset($model[$w][$r])){
                            $model[$w][$r] = \yii\helpers\Html::encode($model[$w][$r]);
                        }                        
                    }
                }
            } 
        }
        
        
        if(!is_null($this->beforeServing)){
            $obj = $this->beforeServing[0];
            $method = $this->beforeServing[1];
            $model = $obj->$method($model);
        }
        
        AjaxResponse::i()->setData($model)->send();
    }
    
     
}