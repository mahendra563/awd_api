<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\Helpers;
use app\models\ProjectFile;
use Yii;
use yii\base\Event;

class ProjectFileController extends AdminController
{
    public function actions()
    {
        return [            
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  ProjectFile::className(),     
                'pk'=>'file_id'
            ],            
            'get' => [
                'class' => Get::className(),                
                'beforeServing'=>[$this,'beforeServing'],
                'modelClass'=>  ProjectFile::className(),                     
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  ProjectFile::className(),                     
            ],            
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> ProjectFile::find()                
                ->asArray()
                ->where("proj_id = :id",[":id"=>Yii::$app->request->get("proj_id")])->orderBy("file_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> ProjectFile::find()->orderBy("proj_title ASC"),
            ],
        ];
    }
    
    public function beforeServing($r){ 
        //$r["proj_creation_date"] = \app\components\Helpers::i()->formatDate($r["proj_creation_date"],"d M Y");
        //$r["proj_completion_date"] = \app\components\Helpers::i()->formatDate($r["proj_completion_date"],"d M Y");
      //  $r["file_date"] = Helpers::i()->formatDate($r["file_date"],"d M Y H:i:s");
        return $r;
    }
      

    public function __construct($id, $module) {
        parent::__construct($id, $module);
            
        
    }
     
}
