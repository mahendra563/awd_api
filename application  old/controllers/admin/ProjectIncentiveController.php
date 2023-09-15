<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\Helpers;
use app\models\Project;
use app\models\ProjectIncentive;
use Yii;
use yii\base\Event;
 

class ProjectIncentiveController extends AdminController
{
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  ProjectIncentive::className(),     
                'pk'=>'inc_id'
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  ProjectIncentive::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'with' => ["projectTask.alottedTo","projectTask.task"],
                'modelClass'=>  ProjectIncentive::className(),                     
            ], 
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> ProjectIncentive::find()         
                ->asArray()->with(["projectTask.alottedBy","projectTask.alottedTo","projectTask.cost.project","projectTask.task"])
                ->where(["pr_id"=>Yii::$app->request->get("pr_id")])->orderBy("inc_id DESC"),
            ],
        ];
    }
     
    public function beforeSave(Event $e){
        $e->sender->inc_date = Helpers::i()->formatDate($e->sender->inc_date,"Y-m-d");        
    }
    
    public function __construct($id, $module) {
        parent::__construct($id, $module);
         
     
        Event::on(ProjectIncentive::class, ProjectIncentive::EVENT_BEFORE_INSERT, [$this,"beforeSave"]);
        Event::on(ProjectIncentive::class, ProjectIncentive::EVENT_BEFORE_UPDATE, [$this,"beforeSave"]);
    }
    
    
}
