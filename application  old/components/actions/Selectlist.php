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

class Selectlist extends Action{
    public $query;
    public $valueField;
    public $labelField;
    public $selectedValue = null;
    
    public function run(){
        $models = $this->query->all();
        
        
        $html = "";
        $valueField = $this->valueField;
        $labelField = $this->labelField;
        
        foreach($models as $model){            
            $selected = "";
            if(!is_null($this->selectedValue)){
                if($this->selectedValue == $model->$valueField){
                    $selected = "selected";
                }            
            }
           
                $html .= "<option $selected value='".$model->$valueField."'>".$model->$labelField."</option>";
            
        }
        
        AjaxResponse::i()->setHtml($html)->send();
    }
}