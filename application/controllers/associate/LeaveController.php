<?php

namespace app\controllers\associate;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
 
use app\components\Helpers;
use app\models\Employee;
use app\models\LeaveAccount;
use app\models\LeaveHolidayList;
use app\models\LeaveRequest;
use Yii;
use yii\base\Event;
 

class LeaveController extends \app\components\EmployeeController
{
    public function actions()
    {
        return [ 
            'holiday-listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>2000,
                'query'=> LeaveHolidayList::find()->orderBy("dt_date DESC"),
            ],
            'request-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->_loadlist_LeaveRequests(
                        Yii::$app->request->get("search",""),
                             
                        Yii::$app->request->get("status","")
                )
            ], 
            'account-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=>$this->_loadlist_AccountTxns(
                        Yii::$app->request->get("from"),
                        Yii::$app->request->get("to"),
                        Yii::$app->request->get("leave_type")            
                ),
            ], 
        ];
    }
    
    public function actionPostRequest(){
        $user = \app\components\TempData::i()->get("loggedInUser");
        $model = new LeaveRequest();
        if($model->load(\Yii::$app->request->post())){ 
            
            //Check if he has enough balance for particular type.
             
            $model->emp_id = $user->emp_id;
            $model->rq_created_on = date("Y-m-d H:i:s");
            $model->rq_updated_on = date("Y-m-d H:i:s");
            $model->rq_status = "Pending";
            $model->rq_dates = \yii\helpers\Json::encode($_POST["LeaveRequest"]["rq_dates"]);
            if($model->validate()){
                $model->save();
                return \app\components\AjaxResponse::i()->setStatus(true)->send();
            }
        }
        return \app\components\AjaxResponse::i()->setValidationErrors($model->getErrors())
                ->setValidationStatus(false)->send();
    }
    
    public function actionViewRequest($id){
        $user = \app\components\TempData::i()->get("loggedInUser");
        $model = LeaveRequest::findOne($id);
        if(is_null($model)){
            return \app\components\AjaxResponse::i()->setStatus(false)->setError("Record Not Found")->send();
        }
        if($model->emp_id !== $user->emp_id){
            return \app\components\AjaxResponse::i()->setAuthorization(false)->send();
        }
        
        return \app\components\AjaxResponse::i()
                ->setStatus(true)
                ->setData($model)->send();
    }
    
     
     
    public function actionAccountGetBalance(){
        $user = \app\components\TempData::i()->get("loggedInUser");
        $emp_id = $user->emp_id;
        
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
    
    
    
    public function __construct($id, $module, $config = []) {
        parent::__construct($id, $module, $config);
    }
     
     
    
    public function _loadlist_LeaveRequests($search, $status){
        $user = \app\components\TempData::i()->get("loggedInUser");
        $emp_id = $user->emp_id;
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
    
     
    
    public function _loadlist_AccountTxns($from, $to, $leave_type=""){
        $user = \app\components\TempData::i()->get("loggedInUser");
        $emp_id = $user->emp_id;
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
                ->where(implode(" AND ", $w),$p)->orderBy("txn_date desc");
    }
     
}
