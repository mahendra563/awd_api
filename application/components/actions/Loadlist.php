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
use yii\data\Pagination;

class Loadlist extends Action{
    
    public $query;
    public $pageSize;
    public $beforeServingEachRecord;
    public function run(){     
        
        $countQuery = clone $this->query;
        
        if(isset($_GET["page"])){
            $page = $_GET["page"];
        } else if(isset($_POST["page"])){
            $page = $_POST["page"];
        } else {
            $page = 1;
        }
         
        $perpage = $this->pageSize;
        $offset = ($perpage * $page ) - $perpage;
        
        $models = $this->query->offset($offset)
            ->limit($perpage)->asArray()
            ->all();
        /*
        $pagination = new Pagination(
                ['totalCount' => $countQuery->count(),
                'defaultPageSize'=>$this->pageSize, 
                'pageSizeLimit'=>[1,500000]
                ]);
        
         
        $models = $this->query->offset($pagination->offset)
            ->limit($pagination->limit)->asArray()
            ->all();
        */
        
        $newmodels = [];
        foreach($models as $r){
            if(is_array($this->beforeServingEachRecord)){
                $obj = $this->beforeServingEachRecord[0];
                $method = $this->beforeServingEachRecord[1];
                $r = $obj->$method($r);
            }
            $newmodels[] = $r;
        }
    
        AjaxResponse::i()->setData(["records"=>$newmodels,"total"=>(int)$countQuery->count()])->send();
    }
}
