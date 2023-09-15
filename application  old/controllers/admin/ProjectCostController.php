<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\models\ProjectCost;
use Yii;

class ProjectCostController extends AdminController
{
    public function actions()
    {
        return [            
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  ProjectCost::className(),     
                'pk'=>'cost_id'
            ],            
            'get' => [
                'class' => Get::className(),                                
                'modelClass'=>  ProjectCost::className(),                     
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  ProjectCost::className(),                     
            ],            
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> ProjectCost::find()                
                ->asArray()->with(["project","projectTasks"])
                ->where(["proj_id"=>Yii::$app->request->get("proj_id")])->orderBy("cost_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> ProjectCost::find()                
                ->asArray()
                ->where(["proj_id"=>Yii::$app->request->get("proj_id")])->orderBy("cost_id DESC"),
            ],
        ];
    }
      
     
}
