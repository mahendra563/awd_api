<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\models\QbKeyword;
use Yii;
 

class QbKeywordController extends AdminController
{
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  QbKeyword::className(),     
                'pk'=>'kw_id'
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  QbKeyword::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  QbKeyword::className(),                     
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> QbKeyword::find()->where("kw_title LIKE :s AND kw_type = :type",[
                    ":type"=>\Yii::$app->request->get("type"),
                    ":s"=>"%".Yii::$app->request->get("search")."%"])->orderBy("kw_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>10000,
                'query'=> QbKeyword::find()
                ->where(["kw_type"=>\Yii::$app->request->get("type")])
                ->orderBy("kw_title ASC"),
            ],
        ];
    }
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);        
        
        
    }
    
}
