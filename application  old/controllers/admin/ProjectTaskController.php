<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\DeleteMultiple;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\models\ProjectTask;
use Yii;
 

class ProjectTaskController extends AdminController
{
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  ProjectTask::className(),     
                'pk'=>'ts_id',                
            ],
            
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  ProjectTask::className(),                     
            ],
             
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  ProjectTask::className(),   
            ], 
            
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),  
                'query'=> $this->loadlist(
                        \Yii::$app->request->get("proj_id",0),
                        \Yii::$app->request->get("cost_id",0),
                        \Yii::$app->request->get("user_id",0),
                        \Yii::$app->request->get("emp_id",0),
                        \Yii::$app->request->get("status",""),
                        \Yii::$app->request->get("date_from",false),
                        \Yii::$app->request->get("date_to",false)
                        )
            ],   
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",100000),  
                //'query'=> $this->loadlist(\Yii::$app->request->get("proj_id",0))
                'query'=> $this->loadlist(\Yii::$app->request->get("proj_id",0),0,0,\Yii::$app->request->get("emp_id",0))                
            ],
        ];
    }
    
    
    
    public function actionReport(){
        
        $by = \Yii::$app->request->get("by");
        $costModel = null;
        $projModel = null;
        if($by == "project"){
            $proj_id = \Yii::$app->request->get("proj_id");
            $w[] = "proj_id = :proj_id";
            $p[":proj_id"] = $proj_id;
            $projModel  = \app\models\Project::findOne($proj_id);
            if(is_null($projModel)){
                throw new NotFoundHttpException();
            }
            $this->view->title = "Project Report - ".$projModel->proj_title;
        } else if($by == "cost"){
            $cost_id = \Yii::$app->request->get("cost_id");
            $w[] = "project_costs.cost_id = :cost_id";
            $p[":cost_id"] = $cost_id;
            $costModel  = \app\models\ProjectCost::findOne($cost_id);
            if(is_null($costModel)){
                throw new NotFoundHttpException();
            }
            $projModel = $costModel->project;
            $this->view->title = "Project Report - ".$costModel->project->proj_title." - ".$costModel->cost_title;
        }
         
        $taskModels = ProjectTask::find()
                ->with(["cost.project","alottedBy","alottedTo"])
                ->innerJoinWith("cost")             
                ->where(implode(" AND ",$w),$p)
                ->orderBy("ts_alotted_to asc")->all();
        
        return $this->render("report",[
            "projModel"=>$projModel,
            "costModel"=>$costModel,
            "taskModels"=>$taskModels,"by"=>$by]);
    }
    
    public function actionMonthlyReport(){
        $month = \Yii::$app->request->get("month");
        $year = \Yii::$app->request->get("year");
        $first_date = $year."-".$month."-01";
        $last_date = \app\components\Helpers::i()->formatDate($first_date,"Y-m-t");
        $projects = [];
            $tasks = [];
        foreach(["Hard","Soft"] as $type2){
            $w[] = "proj_type2 = '$type2' AND ts_completion_date >= :first_date AND ts_completion_date <= :last_date AND ts_status = :status";
            $p[":first_date"] = $first_date;
            $p[":last_date"] = $last_date;
            $p[":status"] = "Completed";

            $taskModels = ProjectTask::find()
                    ->with(["cost.project","alottedBy","alottedTo","user"])
                    ->innerJoinWith("cost.project")                
                    ->where(implode(" AND ",$w),$p)
                    ->orderBy("ts_id asc")->all();
            
           
            

            foreach($taskModels as $t){
                $projects[$type2][$t->cost->project->proj_id] = $t->cost->project;
                $tasks[$t->cost->proj_id][] = $t;
            }
             
        }
        
       
        $this->view->title = "Report - ".\app\components\Helpers::i()->formatDate($first_date, "M Y");
        return $this->render("monthly-report",[
            "projects"=>$projects,
            "tasks"=>$tasks,
           "first_date"=>$first_date,"last_date"=>$last_date]);
    }
    
   
    public function __construct($id, $module) {
        parent::__construct($id, $module);
        
        \yii\base\Event::on(ProjectTask::class,ProjectTask::EVENT_BEFORE_VALIDATE, function(\yii\base\Event $e){
            $e->sender->ts_completion_date = \app\components\Helpers::i()->formatDate($e->sender->ts_completion_date,"Y-m-d");
            $e->sender->ts_estimated_date = \app\components\Helpers::i()->formatDate($e->sender->ts_estimated_date,"Y-m-d");
        });
    }
    
    public function loadlist($proj_id=0,$cost_id=0,$user_id=0,$emp_id=0,$status="",$date_from=false,$date_to=false){
         
        $w = $p = [];
        if($proj_id > 0){
            $w[] = "proj_id = :proj_id";
            $p[":proj_id"] = $proj_id;
        } 
        if($cost_id > 0){
            $w[] = "project_tasks.cost_id = :cost_id";
            $p[":cost_id"] = $cost_id;
        } 
        if($user_id > 0){
            $w[] = "user_id = :user_id";
            $p[":user_id"] = $user_id;
        } 
        if($emp_id > 0){
            $w[] = "ts_alotted_to = :ts_alotted_to";
            $p[":ts_alotted_to"] = $emp_id;
        } 
        if($status != ""){
            $w[] = "ts_status = :status";
            $p[":status"] = $status;
        } 
        
        if($date_from !== false){
            $w[] = "ts_completion_date >= :from AND ts_completion_date <= :to";
            $p[":from"] = \app\components\Helpers::i()->formatDate($date_from,"Y-m-d");
            $p[":to"] = \app\components\Helpers::i()->formatDate($date_to,"Y-m-d");
        }
        
        return ProjectTask::find()
                ->innerJoinWith(["cost"])
                ->asArray()->with(["cost.project","alottedBy","alottedTo","user","task"])->where(implode(" AND ", $w), $p)->orderBy("ts_id desc");
    }
            
    
}