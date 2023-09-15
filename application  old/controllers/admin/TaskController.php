<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\models\Task;
use Yii;
 
class TaskController extends AdminController
{
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  Task::className(),     
                'pk'=>'task_id'
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  Task::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  Task::className(),                     
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> Task::find()->where("task_title LIKE :s",[":s"=>"%".Yii::$app->request->get("search")."%"])->orderBy("task_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> Task::find()->orderBy("task_title ASC"),
            ],
        ];
    }
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);        
        
        \yii\base\Event::on(Task::class,Task::EVENT_BEFORE_DELETE, function(\yii\base\Event $e){
            $models = \app\models\ProjectTask::find()->where(["task_id"=>$e->sender->task_id])->all();
            if(count($models)>0){
                return \app\components\AjaxResponse::i()->setError("You have used this task on various projects. You can not delete it.")->display();
            }            
        });
    }
    
}
