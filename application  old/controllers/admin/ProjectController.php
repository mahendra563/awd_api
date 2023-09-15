<?php
namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\AjaxResponse;
use app\components\Helpers;
use app\models\Project;
use app\models\ProjectHistory;
use app\models\ProjectManager;
use app\models\ProjectAssociate;
use app\models\ProjectQBank;
use app\models\ProjectTask;
use Yii;
use yii\base\Event;

class ProjectController extends AdminController
{
    /*public function _reportLastDateExpired($month, $year){
        $month = \Yii::$app->request->get("month",date("m"));
        $year = \Yii::$app->request->get("year",date("Y"));
        return Project::find()
                ->where("proj_lasttaskdate LIKE :search AND proj_lasttaskdate < :lastdate",[
                    ":search"=>"$year-$month-%",
                    ":lastdate"=>date("Y-m-d")
                ])
    }*/
    
    public function _reportLastDateExpired($from, $to){
        $from = Helpers::i()->formatDate($from, "Y-m-d");
        $to = Helpers::i()->formatDate($to, "Y-m-d");
        /*return Project::find()
                ->where("proj_lasttaskdate >= :from AND proj_lasttaskdate <= :to AND proj_lasttaskdate < :lastdate",[
                    ":from"=>$from,
                    ":to"=>$to,
                    ":lastdate"=>date("Y-m-d")
                ]);*/
        return Project::find()
                ->where("proj_lasttaskdate >= :from AND proj_lasttaskdate <= :to",[
                    ":from"=>$from,
                    ":to"=>$to,
                ]); 
    }
   
    public function actionReportLowBalance($from, $to){
        
        $page = \Yii::$app->request->get("page",1);
        $perpage = \Yii::$app->request->get("perpage",10);
        $offset = ($page * $perpage) - $perpage;
        $from = \Yii::$app->request->get("from");
        $to = \Yii::$app->request->get("to");
        $params[":from"] = Helpers::i()->formatDate($from, "Y-m-d");
        $params[":to"] = Helpers::i()->formatDate($to, "Y-m-d");         
    

        $sql = "SELECT 
        *,(  proj_cost - sum_awd ) as balance , (sum_approved + sum_Completed) as required_amount ,((  proj_cost - sum_awd ) -  (sum_approved + sum_Completed)  ) as actual_required_amount  from 
       (
        SELECT projects.* ,
        sum(case when w_status = 'Sent to AWD' then w_amount else 0 end ) as sum_awd,
        sum(case when w_status = 'Approved'  then w_amount else 0  end) as sum_approved,
        sum(case when w_status = 'Completed'  then w_amount else 0  end) as sum_Completed
        FROM projects 
        LEFT JOIN project_costs on project_costs.proj_id = projects.proj_id     
        LEFT JOIN work_entries on project_costs.cost_id  = work_entries.cost_id
        where  work_entries.w_completion_date >=:from AND work_entries.w_completion_date <= :to
        group by projects.proj_id) as t
        order by actual_required_amount asc ";



    //     $sql = "SELECT 
    //     *, (proj_cost - cost_spent) as balance, ( - actual_amount ) as actual_amount from 
    //    (
    //    SELECT projects.*, sum(ts_amount) as cost_spent , sum(ts_amount) as actual_amount FROM projects 
    //    inner join project_costs on project_costs.proj_id = projects.proj_id
    // inner join project_tasks on project_costs.cost_id = project_tasks.cost_id
    // --    inner join work_entries on project_costs.cost_id = work_entries.cost_id and w_status = 'Approved'

    //    where projects.proj_lasttaskdate >= :from AND projects.proj_lasttaskdate <= :to
    //    group by projects.proj_id) as t
    //    order by actual_amount asc      
    //     ";
        $sql_records = $sql." LIMIT $offset, $perpage";
        $records = \Yii::$app->db->createCommand($sql_records, $params)->queryAll();
        $all_records = \Yii::$app->db->createCommand($sql, $params)->queryAll();
        
        return AjaxResponse::i()
                ->setData([
                    "records"=>$records,
                    "total"=>count($all_records)
                ])->send(); 
    }
    public function actions()
    {
        return [  
            'report-lastdate-expired'=>[
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                //'query'=> $this->_reportLastDateExpired(Yii::$app->request->get("month",date("m")), Yii::$app->request->get("year",date("Y")))
                'query'=> $this->_reportLastDateExpired(Yii::$app->request->get("from",date("d M Y")), Yii::$app->request->get("to",date("d M Y")))
            ],
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  Project::className(),     
                'pk'=>'proj_id'
            ],            
            'get' => [
                'class' => Get::className(),                
                'beforeServing'=>[$this,'beforeServing'],
                'modelClass'=>  Project::className(),                     
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  Project::className(),                     
            ],            
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> Project::find()
                ->with(["projectCosts"])
                ->asArray()
                ->where("proj_title LIKE :s",[":s"=>"%".Yii::$app->request->get("search")."%"])->orderBy("proj_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> Project::find()->orderBy("proj_title ASC"),
            ],
            
            'history-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> ProjectHistory::find()                
                ->asArray()
                ->where("proj_id = :proj_id AND his_description LIKE :search",[":search"=>"%".\Yii::$app->request->get("search")."%",":proj_id"=>Yii::$app->request->get("proj_id")])->orderBy("his_id DESC"),
            ],
            'history-delete' => [
                'class' => Delete::className(),
                'modelClass'=> ProjectHistory::className(),                     
            ], 
            'history-get' => [
                'class' => Get::className(),                                
                'modelClass'=>  ProjectHistory::className(),                     
            ],
            'qbank-save' => [
                'class' => Save::className(),
                'modelClass'=> ProjectQBank::className(),     
                'pk'=>'q_id'
            ],
        ];
    }
    
    public function actionDeleteAttachedFile(){
        $proj_id = \Yii::$app->request->post("proj_id");
        $model = Project::findOne($proj_id);
        \app\components\MediaManager::i()->delete($model->proj_file);
        $model->proj_file = "";
        $model->save();
        return AjaxResponse::i()->setStatus(true)->send();
    }
    
    public function actionListManagers(){
        $proj_id = \Yii::$app->request->get("proj_id",0);
        $sql = "SELECT employees.* FROM project_managers "
        . " INNER JOIN employees ON employees.emp_id = project_managers.emp_id "        
        . " WHERE  project_managers.proj_id = :proj_id AND  project_managers.user_type= 'manager' ";
        
        if($proj_id == 0){
            $sql = "SELECT employees.* FROM employees ";
        
        }
        $models = \Yii::$app->db->createCommand($sql,[":proj_id"=>$proj_id])->queryAll();
        AjaxResponse::i()->setData($models)->send();
    }
    // Associates
    public function actionListAssociates(){
        $proj_id = \Yii::$app->request->get("proj_id",0);
        $sql = "SELECT employees.* FROM project_managers "
        . " INNER JOIN employees ON employees.emp_id = project_managers.emp_id "        
        . " WHERE  project_managers.proj_id = :proj_id";
        
        if($proj_id == 0){
                    $sql = "SELECT employees.* FROM employees ";
                       }
        $models = \Yii::$app->db->createCommand($sql,[":proj_id"=>$proj_id])->queryAll();
        AjaxResponse::i()->setData($models)->send();
    }
    
    public function beforeServing($r){ 
        $r["proj_creation_date"] = \app\components\Helpers::i()->formatDate($r["proj_creation_date"],"d M Y");
        $r["proj_completion_date"] = \app\components\Helpers::i()->formatDate($r["proj_completion_date"],"d M Y");
        $r["proj_lasttaskdate"] = \app\components\Helpers::i()->formatDate($r["proj_lasttaskdate"],"d M Y");
        
        $r["proj_cost_used"] = ProjectTask::find()
                            ->innerJoinWith(["cost"])->where(["proj_id"=>$r["proj_id"]])->sum("ts_amount");
        return $r;
    }
     /*
    public function actionSave(){                
        $model = new \app\forms\Project();    
    
        if($model->load(Yii::$app->request->post()) && $model->validate()){
            $model->save(); 
            AjaxResponse::i()->setStatus(true);
        } else {
            AjaxResponse::i()->setValidationStatus(false)->setValidationErrors($model->getErrors());
        }
        AjaxResponse::i()->send();
    }*/
    public function __construct($id, $module) {
        parent::__construct($id, $module);
        if(isset($_GET["page"])){
            $_GET["page"];
        }
        Event::on(Project::class, Project::EVENT_BEFORE_INSERT, [$this,"beforeSave"]);
        Event::on(Project::class, Project::EVENT_BEFORE_UPDATE, [$this,"beforeSave"]);
        Event::on(Project::class, Project::EVENT_AFTER_INSERT, [$this,"afterSave"]);
        Event::on(Project::class, Project::EVENT_AFTER_UPDATE, [$this,"afterSave"]);
    }
    public function beforeSave(Event $e){
        $e->sender->proj_creation_date = Helpers::i()->formatDate($e->sender->proj_creation_date,"Y-m-d");
        $e->sender->proj_completion_date = Helpers::i()->formatDate($e->sender->proj_completion_date,"Y-m-d");
        $e->sender->proj_lasttaskdate = Helpers::i()->formatDate($e->sender->proj_lasttaskdate,"Y-m-d");
       }
    
    public function afterSave(Event $e){
        $managers = \Yii::$app->request->post("managers",[]);
         ProjectManager::deleteAll(["proj_id"=>$e->sender->proj_id]);
        foreach($managers as $emp_id){
            $model = new ProjectManager();
            $model->emp_id = $emp_id;
            $model->proj_id = $e->sender->proj_id;
            if($model->validate()){
              $model->save();
            }
        }

      
    }
    
    public function actionListTypes(){
        $types = [ Project::TYPE_DEFAULT,
             Project::TYPE_QBANK,
        ];
        return AjaxResponse::i()->setData($types)->send();
    }
    
    public function actionQbankGet($id){
        $model = ProjectQBank::findOne(["proj_id"=>$id]);
        if(is_null($model)){
            $model = new ProjectQBank(); 
        
            $r = [];
            foreach($model->attributes as $key=>$value){
                $r[$key] = " ";
            }
            $r["proj_id"] = $id;
            $r["q_id"] = "";
            return AjaxResponse::i()->setData($r)->send();
        }
        
        foreach($model->attributes as $field=>$value){
            if(in_array($field,[ "q_admit_card_date","q_exam_date","q_anskey_start_date","q_anskey_end_date","q_anskey_last_date" ])){
                $model->$field = Helpers::i()->formatDate($model->$field,"d M Y");
            }
        }
        return AjaxResponse::i()->setData($model)->send();
    }
    
    
}