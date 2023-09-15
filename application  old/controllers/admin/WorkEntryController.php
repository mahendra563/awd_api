<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\AjaxResponse;
use app\components\Helpers;
use app\components\TempData;
use app\models\WorkEntry;
use app\models\WorkEntryFileJoin;
use app\models\WorkFile;
use Yii;
use yii\base\Event;
use yii\db\Expression;
 

class WorkEntryController extends AdminController
{
    public function actions()
    {
        return [ 
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=> WorkEntry::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'modelClass'=> WorkEntry::className(),                                  
                'with' =>["project","cost","alottedBy","alottedTo", "taskNature"],
                'beforeServing'=>[$this,'beforeServing']
            ], 
            'save' => [
                'class' => Save::className(),
                'modelClass'=> WorkEntry::className(),  
                'pk'=>'w_id',
                
            ], 
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->loadList(
                        \Yii::$app->request->get("search"),
                        \Yii::$app->request->get("proj_id"), 
                        \Yii::$app->request->get("cost_id"),
                        \Yii::$app->request->get("alotted_by"), 
                        \Yii::$app->request->get("alotted_to"),                         
                        \Yii::$app->request->get("status"),
                        \Yii::$app->request->get("dateFilter"),
                        \Yii::$app->request->get("from"),
                        \Yii::$app->request->get("to")
                        ),
            ], 
            'amountoverlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->loadList(
                        \Yii::$app->request->get("search"),
                        \Yii::$app->request->get("proj_id"), 
                        \Yii::$app->request->get("cost_id"),
                        \Yii::$app->request->get("alotted_by"), 
                        \Yii::$app->request->get("alotted_to"),                         
                        \Yii::$app->request->get("status"),
                        \Yii::$app->request->get("dateFilter"),
                        \Yii::$app->request->get("from"),
                        \Yii::$app->request->get("to")
                        ),
            ], 
            'report-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->loadListForReport(
                        \Yii::$app->request->get("search"),
                        \Yii::$app->request->get("proj_id"),    
                        \Yii::$app->request->get("cost_id"),
                        \Yii::$app->request->get("alotted_by"), 
                        \Yii::$app->request->get("alotted_to"),
                        \Yii::$app->request->get("status"),
                        \Yii::$app->request->get("dateFilter"),
                        \Yii::$app->request->get("from"),
                        \Yii::$app->request->get("to")
                        ),
            ], 
            'file-save'=>[
                'class'=>Save::className(),
                'modelClass'=> WorkFile::class,
                'pk'=>'f_id',
            ],
            'file-get'=>[
                'class'=>Get::className(),
                'modelClass'=> WorkFile::class, 
            ],
            'file-delete'=>[
                'class'=>Delete::className(),
                'modelClass'=> WorkFile::class, 
            ],
            'file-loadlist'=>[
                'class'=> Loadlist::className(), 
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->fileLoadList(
                        \Yii::$app->request->get("search"),
                        \Yii::$app->request->get("w_id")
                        ),
            ], 
            'file-importer-loadlist'=>[
                'class'=> Loadlist::className(), 
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->fileLoadList(
                        \Yii::$app->request->get("search")                       
                        ),
            ],
            
            'history-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> \app\models\WorkHistory::find()                
                ->asArray()
                ->with(["user","employee"])
                ->where("w_id = :w_id AND his_description LIKE :search",[":search"=>"%".\Yii::$app->request->get("search")."%","w_id"=>Yii::$app->request->get("w_id")])->orderBy("his_id DESC"),
            ],
            'history-delete' => [
                'class' => Delete::className(),
                'modelClass'=> \app\models\WorkHistory::className(),                     
            ], 
            'history-get' => [
                'class' => Get::className(),                                
                'modelClass'=>  \app\models\WorkHistory::className(),
                'with'=>["user","employee"]
            ],
            
        ];
    }
     public function loadListForReport($search, $proj_id, $cost_id, $alotted_by, $alotted_to, $status, $datefilter, $from, $to){
        $w = $p = [];   
       
       if(trim($search)!==""){
            $w[] = "w_description LIKE :search";
            $p[":search"] = "%$search%";
        }
        if(trim($proj_id)!==""){
            $w[] = "proj_id = :proj_id";
            $p[":proj_id"] = $proj_id;
        }
        if(trim($cost_id)!==""){
            $w[] = "cost_id = :cost_id";
            $p[":cost_id"] = $cost_id;
        }
        if(trim($alotted_by)!==""){
            $w[] = "w_alotted_by = :alotted_by";
            $p[":alotted_by"] = $alotted_by;
        }
        if(trim($alotted_to)!==""){
            $w[] = "w_alotted_to = :alotted_to";
            $p[":alotted_to"] = $alotted_to;
        }
        if(trim($status)!==""){
            $w[] = "w_status = :status";
            $p[":status"] = $status;
        }
        if(trim($datefilter)!==""){
            if(in_array($datefilter, ["w_estimated_date","w_completion_date","w_lastupdated_on" ])){
                $w[] = "$datefilter >= :from AND $datefilter <= :to";
                $p[":from"] = Helpers::i()->formatDate($from,"Y-m-d");
                $p[":to"] = Helpers::i()->formatDate($to,"Y-m-d");
            }
        } 
        
        return WorkEntry::find()
                ->select([new Expression('COUNT(work_entries.w_status) as work_count'), 'work_entries.w_status'])
               // ->with(["project","cost","alottedBy","alottedTo","taskNature"])
                ->asArray()
                ->groupBy("w_status")
                ->where(implode(" AND ",$w),$p)->orderBy("work_count desc");
    }
    
    public function actionProjectStatusReport(){
        $page = \Yii::$app->request->get("page",1);
        $order = \Yii::$app->request->get("order","proj_id DESC");
        $perpage = \Yii::$app->request->get("perpage",10);
        $offset = ($page * $perpage)-$perpage;
        
        
        $rec_sql = "SELECT 
SUM(if(work_entries.w_status = 'Completed' || work_entries.w_status = 'Sent to AWD', 1, 0)) as work_count_completed, 
SUM(if(work_entries.w_status != 'Completed' && work_entries.w_status != 'Sent to AWD', 1, 0)) as work_count_pending,  
projects.proj_id, proj_title, proj_creation_date, proj_completion_date, proj_lasttaskdate, proj_file, proj_cost, proj_type, proj_type2
FROM work_entries

INNER JOIN projects ON projects.proj_id = work_entries.proj_id

WHERE proj_title LIKE :s

group by proj_id, proj_title, proj_creation_date, proj_completion_date, proj_lasttaskdate, proj_file, proj_cost, proj_type, proj_type2
ORDER BY $order
LIMIT $offset, $perpage
 ";
        
        $total_sql = "SELECT COUNT(proj_id) FROM (SELECT 
SUM(if(work_entries.w_status = 'Completed' || work_entries.w_status = 'Sent to AWD', 1, 0)) as work_count_completed, 
SUM(if(work_entries.w_status != 'Completed' && work_entries.w_status != 'Sent to AWD', 1, 0)) as work_count_pending,  
projects.proj_id
FROM  work_entries 
INNER JOIN projects ON projects.proj_id = work_entries.proj_id
WHERE proj_title LIKE :s 
group by proj_id ) AS t
 
 ";
   
        $p = [":s"=>"%".Yii::$app->request->get("search")."%"];
        $records = \Yii::$app->db->createCommand($rec_sql,$p)->queryAll();
        $total = \Yii::$app->db->createCommand($total_sql,$p)->queryScalar();
        
        /**
        $query = \app\models\Project::find()                
                ->asArray()
                ->where("proj_title LIKE :s",[":s"=>"%".Yii::$app->request->get("search")."%"])->orderBy("proj_id DESC");
        $countQuery = clone $query;
        
        $records = $query->offset($offset)->limit($perpage)->all();
        $total = $countQuery->count();*/
        
        $proj_ids = \yii\helpers\ArrayHelper::getColumn($records, "proj_id");
        
          
        $work_entries = WorkEntry::find()
                ->select([new Expression('COUNT(work_entries.w_status) as work_count, proj_id'), 'work_entries.w_status'])
               // ->with(["project","cost","alottedBy","alottedTo","taskNature"])
                ->asArray()
                ->groupBy("w_status, proj_id")
                ->where("proj_id IN (". implode(",", $proj_ids).")")->orderBy("work_count desc")->all();
        
        
        $getEntriesForProj = function($proj_id) use ($work_entries){
            $statusData = [];
            foreach($work_entries as $k){
                if($k["proj_id"] == $proj_id){
                    $statusData[$k["w_status"]] = $k["work_count"];
                }
            }
            return $statusData;
        };
        
        $newrecords = [];
        foreach($records as $r){
            $r["status"] = $getEntriesForProj($r["proj_id"]);
            $newrecords[] = $r;
        }
        
        return AjaxResponse::i()->setData([
            "records"=>$newrecords, "total"=>$total
        ])->send();
        
    }
    
    
    public function actionFileLinkDelete(){
        $f_id = \Yii::$app->request->post("f_id");
        $w_id = \Yii::$app->request->post("w_id");
        WorkEntryFileJoin::deleteAll(["f_id"=>$f_id,"w_id"=>$w_id]);
        return AjaxResponse::i()->setStatus(true)->send();
    }
    
    public function beforeServing($r){
        $r["w_estimated_date"] = Helpers::i()->formatDate($r["w_estimated_date"],"d M Y");
        $r["w_completion_date"] = Helpers::i()->formatDate($r["w_completion_date"],"d M Y");

        return $r;
    }
    
    public function loadList($search, $proj_id, $cost_id, $alotted_by, $alotted_to, $status, $datefilter, $from, $to){
        $w = $p = [];
        if(trim($search)!==""){
            $w[] = "w_description LIKE :search";
            $p[":search"] = "%$search%";
        }
        if(trim($proj_id)!==""){
            $w[] = "proj_id = :proj_id";
            $p[":proj_id"] = $proj_id;
        }
        if(trim($cost_id)!==""){
            $w[] = "cost_id = :cost_id";
            $p[":cost_id"] = $cost_id;
        }
        if(trim($alotted_by)!==""){
            $w[] = "w_alotted_by = :alotted_by";
            $p[":alotted_by"] = $alotted_by;
        }
        if(trim($alotted_to)!==""){
            $w[] = "w_alotted_to = :alotted_to";
            $p[":alotted_to"] = $alotted_to;
        }
        if(trim($status)!==""){
            $w[] = "w_status = :status";
            $p[":status"] = $status;
        }
        if(trim($datefilter)!==""){
            if(in_array($datefilter, ["w_estimated_date","w_completion_date","w_lastupdated_on"  ])){
                $w[] = "$datefilter >= :from AND $datefilter <= :to";
                $p[":from"] = Helpers::i()->formatDate($from,"Y-m-d");
                $p[":to"] = Helpers::i()->formatDate($to,"Y-m-d");
            }
        }
        return WorkEntry::find()
                ->with(["project","cost","alottedBy","alottedTo","taskNature"])
                ->asArray()
                ->where(implode(" AND ",$w),$p)->orderBy("w_id desc");
    }
  
    public function __construct($id, $module, $config = []) {
        parent::__construct($id, $module, $config);
        Event::on(WorkFile::class, WorkFile::EVENT_AFTER_INSERT, [$this,"linkFiles"]);
        Event::on(WorkFile::class, WorkFile::EVENT_AFTER_UPDATE, [$this,"linkFiles"]);
        /*Event::on(WorkEntry::class, WorkEntry::EVENT_AFTER_INSERT, function($e){
            $message = $model->alottedBy->emp_fullname." has alotted a Work: ".$model->w_description." Project: ".$model->project->proj_title;
            \app\models\PNotiEntry::add($message, "#", $model->w_alotted_to);
        });*/
        
    }
    
    public function linkFiles($e){
        $w_id = \Yii::$app->request->post("w_id");
        $file_id = $e->sender->f_id;
        
        WorkEntryFileJoin::deleteAll(["w_id"=>$w_id,"f_id"=>$file_id]);
         
        $m = new WorkEntryFileJoin();
        $m->f_id = $file_id;
        $m->w_id = $w_id;
        if($m->validate()){
            $m->save(); 
        }
    }
    
    public function fileLoadlist($search, $w_id=0){
        if($w_id == 0){ 
            return WorkFile::find()
                ->innerJoinWith("workEntryFileJoin.workEntry.project")                
                
                ->with(["employee","workEntryFileJoin.workEntry.project"])
                ->where(" ( f_title LIKE :search OR w_description LIKE :search OR proj_title LIKE :search ) ",[
                  
                    ":search"=>"%$search%"
                ])->asArray()->orderBy("join_id desc");
        }
        return WorkFile::find()
                ->innerJoinWith("workEntryFileJoin")
                ->with(["employee","workEntryFileJoin"])
                ->where("work_entry_file_joins.w_id = :w_id AND f_title LIKE :search",[
                    ":w_id"=>$w_id,
                    ":search"=>"%$search%"
                ])->asArray()->orderBy("join_id desc");
    }
    
    public function actionBulkFileLinking(){
        $w_id = \Yii::$app->request->post("w_id");
        $file_ids = \Yii::$app->request->post("file_ids",[]);
        
        foreach($file_ids as $file_id){
            WorkEntryFileJoin::deleteAll(["w_id"=>$w_id,"f_id"=>$file_id]); //Prevents duplicates
            $m = new WorkEntryFileJoin();
            $m->f_id = $file_id;
            $m->w_id = $w_id;
            if($m->validate()){
                $m->save();
                
                
            }
        }
        return AjaxResponse::i()->setStatus(true)->send();
    }
    
    public function actionUnlinkCheck(){
        $f_id = \Yii::$app->request->get("f_id");
        $models = WorkEntryFileJoin::findAll(["f_id"=>$f_id]);
        if(count($models)>1){
            return AjaxResponse::i()->setData("UNLINK")->send();
        } else {
            return AjaxResponse::i()->setData("DELETE")->send();
        }
    }
    
    public function actionTransfer(){        
        $w_id = \Yii::$app->request->post("w_id");
        $emp_id = \Yii::$app->request->post("emp_id");        
        $model = WorkEntry::findOne($w_id);
        $model->w_alotted_to = $emp_id;
        $model->w_status = WorkEntry::STATUS_TRANFERRED;
        if($model->validate()){
            $model->save();
            
            $message = $model->alottedBy->emp_fullname." Has Transferred Work Entry: ".$model->w_description." Project: ".$model->project->proj_title;
            \app\models\PNotiEntry::add($message, "/associate/work-entry", $model->w_alotted_to);
            
            return AjaxResponse::i()->setStatus(true)->send();
        }
        return AjaxResponse::i()
                ->setValidationStatus(false)
                ->setValidationError($model->getErrors())
                ->setStatus(true)->send(); 
    }
    
    public function actionActionApprove(){
        $w_id = \Yii::$app->request->post("w_id");
        $model = WorkEntry::findOne($w_id);
        if($model->w_status == "Completed"){
            $model->w_status = "Approved";
            if($model->validate()){
                $model->save();
                return AjaxResponse::i()->setStatus(true)->send();
            } else {
                return AjaxResponse::i()->
                setValidationStatus(false)->setValidationErrors($model->getErrors())->send();
            }
        } else {
            return AjaxResponse::i()->setError("This work entry should be completed by employee")->send();
        }
    }
    
    public function actionActionSendToAwd(){
        $w_id = \Yii::$app->request->post("w_id");
        $model = WorkEntry::findOne($w_id);
        if($model->w_status == "Approved"){
            
            //Create Task inside Project
            
            $task = new \app\models\ProjectTask();
            $task->cost_id = $model->cost_id;
            $task->task_id = $model->task_id;
            $task->user_id = 1;
            $task->ts_status = "Completed";
            $task->ts_alotted_by = $model->w_alotted_by;
            $task->ts_alotted_to = $model->w_alotted_to;
            $task->ts_estimated_date = $model->w_estimated_date;
            $task->ts_completion_date = $model->w_completion_date;
            $task->ts_rate = $model->w_rate;
            $task->ts_qty = $model->w_qty;
            $task->ts_amount = $model->w_amount;
            $task->ts_description = $model->w_description;
            
            if($task->validate()){
                $task->save();
                
                $model->w_status = "Sent to AWD";
                if($model->validate()){
                    $model->save();
                    return AjaxResponse::i()->setStatus(true)->send();
                } else {
                    return AjaxResponse::i()->
                    setValidationStatus(false)->setValidationErrors($model->getErrors())->send();
                }
                
            } else {
                
                return AjaxResponse::i()
                        ->setValidationStatus(false)
                        ->setValidationErrors($task->getErrors())
                        ->setError($task->getErrorSummary(true))->send();
            }
            
            //End Creating Task inside Project
            
        } else {
            return AjaxResponse::i()->setError("This work entry should be approved first")->send();
        }
    }
    
    public function actionBulkSave(){
        $proj_id = \Yii::$app->request->post("proj_id");
        $cost_id = \Yii::$app->request->post("cost_id");
        $w_ids =  \Yii::$app->request->post("w_ids");
        
        if(count($w_ids)==0){
            return AjaxResponse::i()
                ->setError("Please select some records")
                ->setStatus(true)->send();
        }
        
        if(trim($proj_id)==""){
            return AjaxResponse::i()
                ->setError("Please select any project")
                ->setStatus(true)->send();
        }
        
        if(trim($cost_id)==""){
            return AjaxResponse::i()
                ->setError("Please select a cost type")
                ->setStatus(true)->send();
        }
        
        $n = 0;
        foreach($w_ids as $w_id){
            $model = WorkEntry::findOne($w_id);
            if($model->w_status !== "Sent to AWD"){
                $model->proj_id = $proj_id;
                $model->cost_id = $cost_id;
                $model->save();
                $n++;
            }
        }
        return AjaxResponse::i()
                ->setMsg("$n Records Updated")
                ->setStatus(true)->send();
    }
}