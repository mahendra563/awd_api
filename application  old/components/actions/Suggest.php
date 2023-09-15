<?php
/*
Application Developed By Abhinav Software
Website: http://abhinavsoftware.com
Email: contact@abhinavsoftware.com
Developer: Ankur Gupta (ankurgupta555@gmail.com)
Copyright Ankur Gupta
*/

namespace app\components\actions;

use app\components\AjaxResponse;
use app\components\Helpers;
use Yii;
use yii\base\Action;

class Suggest extends Action{
    public $modelClass;
    public $searchField;
    public $valueField;
    public $limit = 10;
    public $exclude = [];
    public function run(){
        $modelClass = $this->modelClass;
        $searchField = $this->searchField;
        $valueField = $this->valueField;
        
        $term = Yii::$app->request->post("term");
        
        if(is_string($this->searchField)){
            $where[] = "$searchField LIKE :term";
            $orderBy = $searchField;
        } else if(is_array ($this->searchField)){
            foreach($this->searchField as $f){
                $where[] = "$f LIKE :term";                
            }
            $orderBy = $searchField[0];
        }
        
        
        $models = $modelClass::find()->where(implode(" OR ", $where),[":term"=>"%$term%"])->limit($this->limit)->orderBy("$orderBy ASC")->all();
        $data = [];
        
        if(is_string($this->searchField)){        
            foreach($models as $model){
                if(!in_array($model->$valueField,$this->exclude)){
                    $data[] = [
                      "label"=>$model->$searchField,
                      "id"=>$model->$valueField,              
                      "value"=>$model->$searchField
                    ];
                }
            }
        } else if(is_array ($this->searchField)){
            foreach($models as $model){
                if(!in_array($model->$valueField,$this->exclude)){
                $label = [];
                foreach($this->searchField as $s){
                    $label[] = $model->$s;
                }
                $data[] = [
                  "label"=> implode(" - ", $label),
                  "id"=>$model->$valueField,              
                  "value"=>$label
                ];
                }
            }            
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        \Yii::$app->response->data = $data;
    }
}
