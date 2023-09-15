<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\Helpers;
use app\models\Employee;
use app\models\LeaveAccount;
use app\models\LeaveHolidayList;
use app\models\LeaveRequest;
use Yii;
use yii\base\Event;
 

class LeaveController extends AdminController
{
    public function actions()
    {
        return [
            'holiday-save' => [
                'class' => Save::className(),
                'modelClass'=> LeaveHolidayList::className(),     
                'pk'=>'dt_id'
            ],
            'holiday-delete' => [
                'class' => Delete::className(),
                'modelClass'=>  LeaveHolidayList::className(),                     
            ],
            'holiday-get' => [
                'class' => Get::className(),
                'modelClass'=>  LeaveHolidayList::className(),     
                'beforeServing'=>[$this,'holiday_beforeServing']
            ],  
            'holiday-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> LeaveHolidayList::find()->where("dt_title LIKE :s AND dt_date LIKE :year",[
                    ":s"=>"%".Yii::$app->request->get("search")."%",
                    ":year"=>Yii::$app->request->get("year")."%"
                    ])->orderBy("dt_date DESC"),
            ],
            'holiday-listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>2000,
                'query'=> LeaveHolidayList::find()->orderBy("dt_date DESC"),
            ],
            
            'request-save' => [
                'class' => Save::className(),
                'modelClass'=> LeaveRequest::className(),     
                'pk'=>'rq_id'
            ],
            'request-delete' => [
                'class' => Delete::className(),
                'modelClass'=>  LeaveRequest::className(),                     
            ],
            'request-get' => [
                'class' => Get::className(),
                'modelClass'=>  LeaveRequest::className(),        
                 
            ],
            'request-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->_loadlist_LeaveRequests(
                        Yii::$app->request->get("search",""),
                     
                        Yii::$app->request->get("emp_id",""),
                        Yii::$app->request->get("status","")
                )
            ],
             
            'account-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=>$this->_loadlist_AccountTxns(
                        Yii::$app->request->get("from"),
                        Yii::$app->request->get("to"),
                        Yii::$app->request->get("leave_type"),
                        Yii::$app->request->get("emp_id","")
                ),
            ],
            
            'account-get' => [
                'class' => Get::className(),
                'modelClass'=>  LeaveAccount::className(),        
                 
            ],
            
            'account-save' => [
                'class' => Save::className(),
                'modelClass'=> LeaveAccount::className(),     
                'pk'=>'txn_id'
            ],
            
            'account-delete' => [
                'class' => Delete::className(),
                'modelClass'=>  LeaveAccount::className(),                     
            ],
            
            
        ];
    }
    
    public function actionRequestAction(){
        $data = Yii::$app->request->post("modal");
        $action = Yii::$app->request->post("action");
        
        $model = LeaveRequest::findOne($data["rq_id"]);
        if($model->rq_status !== "Approved" && $action == "Approve"){
            //Perform Approval
            if($model->load(["LeaveRequest"=>$data])){
                $model->rq_status = "Approved";
                $model->rq_dates = \yii\helpers\Json::encode($model->rq_dates); //too much encoded
                
                if($model->validate()){
                    $model->save();  
                    $model->refresh();
                    $dates = \yii\helpers\Json::decode($model->rq_dates);
                    
                    $return = LeaveAccount::assignLeaves($model->emp_id, $dates);
                    
                    if($return){
                        return \app\components\AjaxResponse::i()->setStatus(true)->send();
                    } else {
                        return \app\components\AjaxResponse::i()->setError("Not Enough Balance")->send();
                    }
                    
                } else {
                     
                    return \app\components\AjaxResponse::i()
                                ->setValidationErrors($modal->getErrors())
                                ->setValidationStatus(false)->send();
                }
            }            
        } else {
            if($model->load(["LeaveRequest"=>$model])){
                $model->rq_status = $action == "Reject" ? "Rejected" : "Pending";
                if($model->validate()){
                    $model->save();                                         
                    return \app\components\AjaxResponse::i()->setStatus(true)->send();                                     
                } else {
                    return \app\components\AjaxResponse::i()
                                ->setValidationErrors($modal->getErrors())
                                ->setValidationStatus(false)->send();
                }
            }
        }
        
    }
    
    public function actionAccountGetBalance($emp_id){
        $credit_casual = LeaveAccount::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Credit","txn_leave_type"=>"Casual"])->sum("txn_amount");
        $credit_special = LeaveAccount::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Credit","txn_leave_type"=>"Special"])->sum("txn_amount");
        $credit_paid = LeaveAccount::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Credit","txn_leave_type"=>"Paid"])->sum("txn_amount");
        
        $debit_casual = LeaveAccount::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Debit","txn_leave_type"=>"Casual"])->sum("txn_amount");
        $debit_special = LeaveAccount::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Debit","txn_leave_type"=>"Special"])->sum("txn_amount");
        $debit_paid = LeaveAccount::find()
                ->where(["emp_id"=>$emp_id,"txn_type"=>"Debit","txn_leave_type"=>"Paid"])->sum("txn_amount");
        
        if(isset($_GET["from"]) && isset($_GET["to"])){
            $from = Helpers::i()->formatDate($_GET["from"],"Y-m-d 00:00:00");
            $to = Helpers::i()->formatDate($_GET["to"],"Y-m-d 23:59:59");
            
            
            
            $credit_casual2 = LeaveAccount::find()
                ->where("emp_id = :emp_id AND txn_type = :txn_type AND txn_leave_type = :txn_leave_type AND txn_date >= :from AND txn_date <= :to",
                        [":emp_id"=>$emp_id,":txn_type"=>"Credit",":txn_leave_type"=>"Casual",":from"=>$from,":to"=>$to])->sum("txn_amount");
            $credit_special2 = LeaveAccount::find()
                    ->where("emp_id = :emp_id AND txn_type = :txn_type AND txn_leave_type = :txn_leave_type AND txn_date >= :from AND txn_date <= :to",
                            [":emp_id"=>$emp_id,":txn_type"=>"Credit",":txn_leave_type"=>"Special",":from"=>$from,":to"=>$to])->sum("txn_amount");
            $credit_paid2 = LeaveAccount::find()
                    ->where("emp_id = :emp_id AND txn_type = :txn_type AND txn_leave_type = :txn_leave_type AND txn_date >= :from AND txn_date <= :to",
                            [":emp_id"=>$emp_id,":txn_type"=>"Credit",":txn_leave_type"=>"Paid",":from"=>$from,":to"=>$to])->sum("txn_amount");

            $debit_casual2 = LeaveAccount::find()
                    ->where("emp_id = :emp_id AND txn_type = :txn_type AND txn_leave_type = :txn_leave_type AND txn_date >= :from AND txn_date <= :to",
                            [":emp_id"=>$emp_id,":txn_type"=>"Debit",":txn_leave_type"=>"Casual",":from"=>$from,":to"=>$to])->sum("txn_amount");
            $debit_special2 = LeaveAccount::find()
                    ->where("emp_id = :emp_id AND txn_type = :txn_type AND txn_leave_type = :txn_leave_type AND txn_date >= :from AND txn_date <= :to",
                            [":emp_id"=>$emp_id,":txn_type"=>"Debit",":txn_leave_type"=>"Special",":from"=>$from,":to"=>$to])->sum("txn_amount");
            $debit_paid2 = LeaveAccount::find()
                    ->where("emp_id = :emp_id AND txn_type = :txn_type AND txn_leave_type = :txn_leave_type AND txn_date >= :from AND txn_date <= :to",
                            [":emp_id"=>$emp_id,":txn_type"=>"Debit",":txn_leave_type"=>"Paid",":from"=>$from,":to"=>$to])->sum("txn_amount");            
            $data = [
                "Casual"=>$credit_casual - $debit_casual,
                "Special"=>$credit_special - $debit_special,
                "Paid"=>$credit_paid - $debit_paid,
                "Casual_Duration"=>$credit_casual2 - $debit_casual2,
                "Special_Duration"=>$credit_special2 - $debit_special2,
                "Paid_Duration"=>$credit_paid2 - $debit_paid2,
            ];
            
        } else {
            $data = [
                "Casual"=>$credit_casual - $debit_casual,
                "Special"=>$credit_special - $debit_special,
                "Paid"=>$credit_paid - $debit_paid,
            ];
        }
        
        
        
        
        return \app\components\AjaxResponse::i()->setData($data)
        ->send();
        
    }
    
    public function holiday_beforeServing($r){
        $r["dt_date"] = Helpers::i()->formatDate($r["dt_date"], "d M Y");
        return $r;
    }
    
    
    public function __construct($id, $module, $config = []) {
        parent::__construct($id, $module, $config);
        Event::on(LeaveAccount::class, LeaveAccount::EVENT_BEFORE_VALIDATE, function($e){
            $e->sender->txn_date = date("Y-m-d H:i:s");
        });
        Event::on(LeaveHolidayList::class, LeaveHolidayList::EVENT_BEFORE_VALIDATE, function($e){
            $e->sender->dt_date = Helpers::i()->formatDate($e->sender->dt_date,"Y-m-d");
        });
        
        Event::on(LeaveRequest::class, LeaveRequest::EVENT_BEFORE_VALIDATE, function($e){
            // $e->sender->txn_date = date("Y-m-d H:i:s");
            if(\Yii::$app->controller->action->id == "request-save"){                
             $e->sender->rq_dates = \yii\helpers\Json::encode($_POST["LeaveRequest"]["rq_dates"]);
            }
        });
        
    }
    
    public function actionBulkCredit(){
        $days = \Yii::$app->request->post("days");
        $type = \Yii::$app->request->post("type");
        $newmodels = [];
        $models = Employee::find()->where("emp_status = 'Present' ")->all();
        foreach($models as $model){             
            $etype = $model->getTypeOnDate(date("Y-m-d"));
            if($etype == "Insider"){
                $newmodels[] = $model;
            }
        }
        
        foreach($newmodels as $m){
           
            $lv = new LeaveAccount();
            $lv->txn_amount = $days;
            $lv->txn_comment = "Bulk Credit";
            $lv->txn_date = date("Y-m-d H:i:s");
            $lv->txn_leave_type = $type;
            $lv->txn_type = "Credit";
            $lv->emp_id = $m->emp_id;
            if($lv->validate()){
                $lv->save();
            }
        }
        
        return \app\components\AjaxResponse::i()->setStatus(true)->send();
    }
    
    public function actionBulkCloseLeaves(){ 
        $type = \Yii::$app->request->post("type");
        $newmodels = [];
        $models = Employee::find()->where("emp_status = 'Present' ")->all();
        foreach($models as $model){
            $type = $model->getTypeOnDate(date("Y-m-d"));
            if($type == "Insider"){
                $newmodels[] = $model;
            }
        }
        
        foreach($newmodels as $m){            
            $balance = LeaveAccount::getAccountBalance($m->emp_id, $type);
            if($balance > 0){
                $lv = new LeaveAccount();
                $lv->txn_amount = $balance;
                $lv->txn_comment = "Bulk Closing";
                $lv->txn_date = date("Y-m-d H:i:s");
                $lv->txn_leave_type = $type;
                $lv->txn_type = "Debit";
                $lv->emp_id = $m->emp_id;
                if($lv->validate()){
                    $lv->save();
                }
            }
        }
        
        return \app\components\AjaxResponse::i()->setStatus(true)->send();
    }
   
   
    
    public function _loadlist_LeaveRequests($search,$emp_id,$status){
        $w = $p = [];
        if(trim($emp_id) !== ""){
            $w[] = "emp_id = :emp_id";
            $p[":emp_id"] = $emp_id;
        }
         
        if(trim($status) !== ""){
            $w[] = "rq_status = :status";
            $p[":status"] = $status;                    
        }
        
        if(trim($search) !== ""){
            $w[] = "rq_subject LIKE :search";
            $p[":search"] = "%$search%";
        }
        return LeaveRequest::find()->with(["employee"])
                ->where(implode(" AND ", $w),$p)->orderBy("rq_id desc");
    }
    
     
    
    public function _loadlist_AccountTxns($from, $to, $leave_type="", $emp_id){
        $from = Helpers::i()->formatDate($from, "Y-m-d 00:00:00");
        $to = Helpers::i()->formatDate($to, "Y-m-d 23:59:59");
        $w = $p = [];
        
        $w[] = "txn_date >= :from AND txn_date <= :to";
        $p[":from"] = $from; 
        $p[":to"] = $to;
                
        if(trim($emp_id) !== ""){
            $w[] = "emp_id = :emp_id";
            $p[":emp_id"] = $emp_id;
        }
        
        if($leave_type!==""){
            $w[] = "txn_leave_type = :type";
            $p[":type"] = $leave_type;
        }
        
        return LeaveAccount::find()
                ->where(implode(" AND ", $w),$p)->orderBy("txn_id desc");
    }
     
}
