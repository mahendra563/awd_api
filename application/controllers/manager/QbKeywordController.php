<?php

namespace app\controllers\manager;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\ManagerController;
use app\models\QbKeyword;
use Yii;
 

class QbKeywordController extends ManagerController
{
    public function actions()
    {
        return [
            
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>10000,
                'query'=> QbKeyword::find()
                ->where(["kw_type"=>\Yii::$app->request->get("type")])
                ->orderBy("kw_title ASC"),
            ],
        ];
    }
    
}
