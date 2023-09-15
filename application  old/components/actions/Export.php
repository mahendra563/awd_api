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

class Export extends Action{
    public $records;    
    public $labels = [];
    public $filename = "file";
     
    
    public function run(){ 
        
        header('Content-type: text/csv');
        header('Content-disposition: attachment;filename='.$this->filename.'.csv');
        if(count($this->labels)>0){
            echo '"'.implode('","', $this->labels).'"';
        }
        foreach($this->records as $r){
            echo '"'.implode('","', $r).'"';
            echo "\n";
        }
        exit();
    }
  
}