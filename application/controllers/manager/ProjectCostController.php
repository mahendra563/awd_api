<?php

namespace app\controllers\manager;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;    
use app\models\ProjectCost;
use Yii;

class ProjectCostController extends \app\components\ManagerController
{
    public function actions()
    {
        return [            
            
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
