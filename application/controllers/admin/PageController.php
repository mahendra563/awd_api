<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\models\Page;
use Yii;
 

class PageController extends AdminController
{
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  Page::className(),     
                'pk'=>'page_id'
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  Page::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  Page::className(),                     
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> Page::find()->where("page_title LIKE :s",[":s"=>"%".Yii::$app->request->get("search")."%"])->orderBy("page_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> Page::find()->orderBy("page_title ASC"),
            ],
        ];
    }
   
}
