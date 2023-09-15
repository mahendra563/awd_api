<?php

namespace app\controllers\manager;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
 
use app\components\AdminController;
use app\models\User;
use Yii;
use yii\base\Event;
 

class SuggestController extends \app\components\ManagerController
{
    public function actionEmployee(){
        $term = \Yii::$app->request->get("term");
        $role_id = \Yii::$app->request->get("role",0);
        $q = \app\models\Employee::find();
        if($role_id > 0){
            $w[] = "employee_roles.role_id = :role_id";
            $p[":role_id"] = $role_id;
            $q = $q->innerJoinWith("employeeRoles");
        }        
        $w[] = "emp_fullname LIKE :search OR emp_email LIKE :search";
        $p[":search"] = "$term%";
        $models = $q->where(implode(" AND ", $w),$p)->orderBy("emp_fullname DESC")->limit(10)->all();
        $records = array_map(function($r){
            return [
                "label"=>$r->emp_fullname,
                "value"=>$r->emp_id
            ];
        }, $models);
        echo \yii\helpers\Json::encode($records);
        exit();
    }
    
    public function actionProject(){
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