<?php

namespace app\controllers\manager;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
 
use app\components\AjaxResponse;
use app\components\Helpers;
use app\models\Project;
use app\models\ProjectHistory;
use app\models\ProjectManager;
use app\models\ProjectQBank;
use app\models\ProjectTask;
use Yii;
use yii\base\Event;

class ProjectController extends \app\components\ManagerController
{
    public function actions()
    {
        return [       
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->loadList(),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> $this->loadList(),
            ],
            'assigned-projects-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>\Yii::$app->request->get("perpage",10),
                'query'=>Project::find()
                ->with(["projectCosts"])
                ->innerJoin("project_managers","project_managers.proj_id = projects.proj_id")
                ->asArray()
                ->where("project_managers.emp_id = :emp_id AND proj_completion_date >= :today",[
                    ":today"=> date("Y-m-d H:i:s"),
                    ":emp_id"=> \app\components\TempData::i()->get("loggedInUser")->emp_id ])->orderBy("proj_id DESC")
            ]
        ];
    }
     
    function loadList(){ 
        return Project::find()
                ->with(["projectCosts"])
                ->innerJoin("project_managers","project_managers.proj_id = projects.proj_id")
                ->asArray()
                ->where("project_managers.emp_id = :emp_id",[":emp_id"=> \app\components\TempData::i()->get("loggedInUser")->emp_id ])->orderBy("proj_id DESC");
    }
    
     
}
