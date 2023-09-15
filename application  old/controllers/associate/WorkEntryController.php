<?php

namespace app\controllers\associate;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AjaxResponse;
use app\components\EmployeeController;
use app\components\Helpers;
use app\components\TempData;
use app\models\Project;
use app\models\WorkEntry;
use app\models\WorkEntryFileJoin;
use app\models\WorkFile;
use Yii;
use yii\base\Event;
 

class WorkEntryController extends EmployeeController
{
    public function actions(){
        return [
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->loadList(
                        \Yii::$app->request->get("search"),
                        \Yii::$app->request->get("proj_id"),   
                        \Yii::$app->request->get("status"),
                        
                        \Yii::$app->request->get("dateFilter"),
                        \Yii::$app->request->get("from"),
                        \Yii::$app->request->get("to")
                        
                        ),
               
            ],
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
                'beforeServingEachRecord'=>[$this,"files_beforeServingEachRecord"]
            ],
            'save' => [
                'class' => Save::className(),
                'modelClass'=> WorkEntry::className(),  
                'pk'=>'w_id',
                
            ], 
        ];
    }
    
    public function files_beforeServingEachRecord($r){
        $emp  = TempData::i()->get("loggedInUser"); 
        
        $w_id = \Yii::$app->request->get("w_id");
        $work = \Yii::$app->cache->getOrSet("work-".$w_id, function() use ($w_id){
            return WorkEntry::findOne($w_id);
        }, 2);
        
        $r["readonly"] = false;
 
        if(strtotime($r["f_uploaded_on"]) < strtotime(date("Y-m-d H:i:s"))-3600-3600){
           
            $r["readonly"] = true;
        }
        if((int)$r["emp_id"]!==(int)$emp->emp_id){
     
            $r["readonly"] = true;
        }
        if(!is_null($work)){
            if($work->w_status !== "Accepted"){
                $r["readonly"] = true;
            }
        }
        
        return $r;
    }
    
    public function __construct($id, $module, $config = []) {
        parent::__construct($id, $module, $config);
        Event::on(WorkFile::class, WorkFile::EVENT_AFTER_INSERT, [$this,"WorkFile_linkFiles"]);
        Event::on(WorkFile::class, WorkFile::EVENT_AFTER_UPDATE, [$this,"WorkFile_linkFiles"]);
        Event::on(WorkFile::class, WorkFile::EVENT_BEFORE_VALIDATE, [$this,"WorkFile_beforeValidate"]);        
        Event::on(WorkFile::class, WorkFile::EVENT_BEFORE_DELETE, [$this,"WorkFile_beforeDelete"]); 
        
        
    //    Event::on(WorkEntry::class, WorkEntry::EVENT_BEFORE_DELETE, [$this,"WorkEntry_beforeDelete"]); 
    }
    
    public function WorkEntry_beforeDelete($e){
        /*$emp_list = [];
        foreach($e->sender->project->managerJoins as $join){
            $emp_list[] = $join->emp_id;
        }
        $emp  = TempData::i()->get("loggedInUser"); 
        if(!in_array($emp->emp_id, $emp_list)){
            echo AjaxResponse::i()->setError("You are not authorized to delete this entry")->display();
            exit();
        }*/
    }
    
    public function WorkFile_beforeDelete($e){
        $emp  = TempData::i()->get("loggedInUser"); 
        if($e->sender->emp_id !== $emp->emp_id){
            AjaxResponse::i()->setAuthorization(false)->display();
        }
        if( strtotime($e->sender->f_uploaded_on) < (strtotime(date("Y-m-d H:i:s"))-3600-3600) ){
            AjaxResponse::i()->setError("You can not delete this file anymore")->display();
        }
    }
    
    public function WorkFile_linkFiles($e){
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
    
    public function WorkFile_beforeValidate(Event $e){   
        $emp  = TempData::i()->get("loggedInUser"); 
        $e->sender->emp_id = $emp->emp_id;
        if(!$e->sender->isNewRecord){
            if( strtotime($e->sender->f_uploaded_on) < (strtotime(date("Y-m-d H:i:s"))-3600-3600) ){
                AjaxResponse::i()->setError("You can not edit this file anymore")->display();
            }
        }
    }
    
    
    public function beforeServing($r){
        $r["w_estimated_date"] = Helpers::i()->formatDate($r["w_estimated_date"],"d M Y");
        $r["w_completion_date"] = Helpers::i()->formatDate($r["w_completion_date"],"d M Y");
        return $r;
    }
    public function loadList($search, $proj_id, $status,  $datefilter, $from, $to){
        $w = $p = [];
        
        $emp  = TempData::i()->get("loggedInUser");
       
        $alotted_to = $emp->emp_id;
        
        if(trim($search)!==""){
            $w[] = "w_description LIKE :search";
            $p[":search"] = "%$search%";
        }
        if(trim($proj_id)!==""){
            $w[] = "proj_id = :proj_id";
            $p[":proj_id"] = $proj_id;
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
            if(in_array($datefilter, ["w_estimated_date","w_completion_date","w_lastupdated_on"])){
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
    
    public function actionChangeStatus(){
        $emp  = TempData::i()->get("loggedInUser");
        $status = \Yii::$app->request->post("status");
        $w_id = \Yii::$app->request->post("w_id");
        if(!in_array($status,["Accepted","Rejected","Completed"])){
            return AjaxResponse::i()->setAuthorization(false)->setError("You can only accept, reject or complete any work")->display();
        }
        $model = WorkEntry::findOne($w_id);
        if($model->w_alotted_to !== $emp->emp_id){
            return AjaxResponse::i()->setAuthorization(false)->display();
        }
        $model->w_status = $status;
        if($model->validate()){
            $model->save();
            
            $message = $model->alottedTo->emp_fullname." has $status Work Entry: ".$model->w_description." Project: ".$model->project->proj_title;
            \app\models\PNotiEntry::add($message, "/manager/work-entry", $model->w_alotted_by);
            
            AjaxResponse::i()->setStatus(true);
        } else {
            AjaxResponse::i()->setValidationStatus(false)
                    ->setValidationErrors($model->getErrors());
        }
        return AjaxResponse::i()->send();
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
        $alotted_by = TempData::i()->get("loggedInUser")->emp_id;
        $w_id = \Yii::$app->request->post("w_id");
        $emp_id = \Yii::$app->request->post("emp_id");
        
        $model = WorkEntry::find()->where(["w_id"=>$w_id,"w_alotted_by"=>$alotted_by])->one();
        if(is_null($model)){
            return AjaxResponse::i()->setError("Work Entry Not Found")->send();
        }
        $model->w_alotted_to = $emp_id;
        $model->w_status = WorkEntry::STATUS_TRANFERRED;
        if($model->validate()){

            $model->save();
            return AjaxResponse::i()->setStatus(true)->send();
        }
        return AjaxResponse::i()
                ->setValidationStatus(false)
                ->setValidationError($model->getErrors())
                ->setStatus(true)->send();
        
        
    }
}