<?php

namespace app\controllers\associate;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\EmployeeController;
use app\models\Page;
use Yii;
 

class PageController extends EmployeeController
{
    public function actions()
    {
        return [
            
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  Page::className(),                     
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> Page::find()->where("page_title LIKE :s AND page_status = 'Public'",[":s"=>"%".Yii::$app->request->get("search")."%"])->orderBy("page_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> Page::find()->where(["page_status"=>'Public'])->orderBy("page_title ASC"),
            ],
        ];
    }
   
}
