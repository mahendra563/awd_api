<?php

namespace app\controllers\associate;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
 
use app\components\AdminController;
use app\models\User;
use Yii;
use yii\base\Event;
 

class SuggestController extends \app\components\EmployeeController
{
     
    
    public function actionProject(){
        $term = \Yii::$app->request->get("term");
        $emp = \app\components\TempData::i()->get("loggedInUser"); 
        $q = \app\models\Project::find();
              
        $w[] = "proj_title LIKE :search";
        $p[":search"] = "$term%";
        $w[] = "work_entries.w_alotted_to = :emp_id";
        $p[":emp_id"] = $emp->emp_id;
        
        $models = $q->where(implode(" AND ", $w),$p)
                ->innerJoin("work_entries","work_entries.proj_id = projects.proj_id")
                ->orderBy("proj_title DESC")->limit(10)->all();
        $records = array_map(function($r){
            return [
                "label"=>$r->proj_title,
                "value"=>$r->proj_id
            ];
        }, $models);
        echo \yii\helpers\Json::encode($records);
        exit();
    }
    
    public function actionSelfProject(){
        $term = \Yii::$app->request->get("term");
        $emp = \app\components\TempData::i()->get("loggedInUser"); 
        $q = \app\models\Project::find();
              
        $w[] = "proj_title LIKE :search";
        $p[":search"] = "$term%";
        $w[] = "project_managers.emp_id = :emp_id";
        $p[":emp_id"] = $emp->emp_id;
        
        $models = $q->where(implode(" AND ", $w),$p)
                ->innerJoin("project_managers","project_managers.proj_id = projects.proj_id")
                ->orderBy("proj_title DESC")->limit(10)->all();
        $records = array_map(function($r){
            return [
                "label"=>$r->proj_title,
                "value"=>$r->proj_id
            ];
        }, $models);
        echo \yii\helpers\Json::encode($records);
        exit();
    }
    
}