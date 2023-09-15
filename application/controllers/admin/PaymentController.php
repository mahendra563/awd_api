<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\AjaxResponse;
use app\components\Helpers;
use app\models\Payment;
use app\models\ProjectIncentive;
use app\models\ProjectTask;
use app\models\WorkDone;
use yii\base\Event;

class PaymentController extends AdminController{
    public function actionReport(){
        
        $print = \Yii::$app->request->get("print","false");
        
        $from = \Yii::$app->request->get("month")."-01";
 
        $date = Helpers::i()->formatDate($from,"Y-m-t");
        $emp_id = \Yii::$app->request->get("emp_id");
        
        WorkDone::updateForDate($emp_id, $from);
        
        
        $empModel = \app\models\Employee::findOne($emp_id);
        
        $params = [":emp_id"=>$emp_id,":from"=>$from,":to"=>$date];
        
        foreach(["Hard","Soft"] as $type2){
            $total_amount[$type2] = ProjectTask::find()
                    ->innerJoinWith("cost.project")
                    ->where("proj_type2 = '$type2' AND ts_alotted_to = :emp_id AND ts_completion_date <= :to AND ts_completion_date >= :from AND ts_status = :status",
                            [":emp_id"=>$emp_id,":from"=>$from,":to"=>$date,":status"=>"Completed"])                    
                    ->sum("ts_amount");
            
            
            $data["month_records"]["incentive"][$type2] = ProjectIncentive::find()
            ->with(["projectTask.cost.project","projectTask.task","projectPrintOrder"])->asArray()
            ->where("proj_type2 = '$type2' AND ts_alotted_to = :emp_id AND inc_date >= :from AND inc_date <= :to",$params)->innerJoinWith("projectTask.cost.project")->all(); 
            
            $data["month_records"]["projecttasks"][$type2] = \app\models\ProjectTask::find()
            ->innerJoinWith("cost.project")
            ->with(["task","cost.project"])->asArray()
            ->where("proj_type2 = '$type2' AND ts_alotted_to = :emp_id AND ts_completion_date >= :from AND ts_completion_date <= :to AND ts_status = 'Completed'",$params)->all();    

        }
          
        $data["month_records"]["workdone"] = WorkDone::find()->where("emp_id = :emp_id AND wd_date >= :from AND wd_date <= :to",$params)->all();    
 
        //$data["month_records"]["payment"] = Payment::find()->where("emp_id = :emp_id AND pmt_date >= :from AND pmt_date <= :to",$params)->all();
        $data["month_records"]["payment"] = Payment::find()->where("emp_id = :emp_id AND pmt_month = :month",[
            ":emp_id"=>$emp_id, ":month"=>$date, 
        ])->all();
        
        $data["month_summery"]["workdone"] = (float)WorkDone::getForMonth($emp_id, $date);
        $data["month_summery"]["incentive"] = (float)ProjectIncentive::getForMonth($emp_id, $date);
        $data["month_summery"]["payment"] = (float)Payment::getForMonth($emp_id, $date);        
        $data["month_summery"]["oldwork"] = \app\models\OldWork::getForMonth($emp_id, $date);
        
        $data["month_summery"]["target"] = (float)$empModel->getTargetOnDate($date);
        $data["month_summery"]["total_amount"] = (float)$total_amount["Hard"] + (float)$total_amount["Soft"];
        
        $data["summery"]["oldwork"] = \app\models\OldWork::getTillDate($emp_id, $date);
        $data["summery"]["workdone"] = (float)WorkDone::getTillDate($emp_id, $date);
        $data["summery"]["incentive"] = (float)ProjectIncentive::getTillDate($emp_id, $date);
        $data["summery"]["payment"] = (float)Payment::getTillDate($emp_id, $date);
        
        //$data["summery"]["payment_month"] = Payment::getForMonth($emp_id, $date);
        
        $data["summery"]["balance"] = round($data["summery"]["workdone"] +  $data["summery"]["incentive"] -  $data["summery"]["payment"],2);
        
         
        if($print == "true"){
            return $this->render("report",$data);
        } else {
            return AjaxResponse::i()->setStatus(true)->setData($data)->send();
        }
    }
    /*
    public function actionReport(){
        
        $print = \Yii::$app->request->get("print","false");
        
        $from = \Yii::$app->request->get("month")."-01";
 
        $date = Helpers::i()->formatDate($from,"Y-m-t");
        $emp_id = \Yii::$app->request->get("emp_id");
        
        WorkDone::updateForDate($emp_id, $from);
        
        
        $empModel = \app\models\Employee::findOne($emp_id);
        
        
        
        $total_amount = ProjectTask::find()->where("ts_alotted_to = :emp_id AND ts_completion_date <= :to AND ts_completion_date >= :from AND ts_status = :status",
            [":emp_id"=>$emp_id,":from"=>$from,":to"=>$date,":status"=>"Completed"])->sum("ts_amount");
         
        $params = [":emp_id"=>$emp_id,":from"=>$from,":to"=>$date];
          
        $data["month_records"]["workdone"] = WorkDone::find()->where("emp_id = :emp_id AND wd_date >= :from AND wd_date <= :to",$params)->all();    

        $data["month_records"]["projecttasks"] = \app\models\ProjectTask::find()
        ->with(["task","cost.project"])->asArray()
        ->where("ts_alotted_to = :emp_id AND ts_completion_date >= :from AND ts_completion_date <= :to AND ts_status = 'Completed'",$params)->all();    
    
        $data["month_records"]["incentive"] = ProjectIncentive::find()
        ->with(["projectTask.cost.project","projectTask.task","projectPrintOrder"])->asArray()
        ->where("ts_alotted_to = :emp_id AND inc_date >= :from AND inc_date <= :to",$params)->innerJoinWith("projectTask")->all();        
        
        //$data["month_records"]["payment"] = Payment::find()->where("emp_id = :emp_id AND pmt_date >= :from AND pmt_date <= :to",$params)->all();
        $data["month_records"]["payment"] = Payment::find()->where("emp_id = :emp_id AND pmt_month = :month",[
            ":emp_id"=>$emp_id, ":month"=>$date, 
        ])->all();
        
        $data["month_summery"]["workdone"] = (float)WorkDone::getForMonth($emp_id, $date);
        $data["month_summery"]["incentive"] = (float)ProjectIncentive::getForMonth($emp_id, $date);
        $data["month_summery"]["payment"] = (float)Payment::getForMonth($emp_id, $date);        
        $data["month_summery"]["oldwork"] = \app\models\OldWork::getForMonth($emp_id, $date);
        
        $data["month_summery"]["target"] = (float)$empModel->getTargetOnDate($date);
        $data["month_summery"]["total_amount"] = (float)$total_amount;
        
        $data["summery"]["oldwork"] = \app\models\OldWork::getTillDate($emp_id, $date);
        $data["summery"]["workdone"] = (float)WorkDone::getTillDate($emp_id, $date);
        $data["summery"]["incentive"] = (float)ProjectIncentive::getTillDate($emp_id, $date);
        $data["summery"]["payment"] = (float)Payment::getTillDate($emp_id, $date);
        
        //$data["summery"]["payment_month"] = Payment::getForMonth($emp_id, $date);
        
        $data["summery"]["balance"] = $data["summery"]["workdone"] + $data["summery"]["incentive"] - $data["summery"]["payment"];
        
        if($print == "true"){
            return $this->render("report",$data);
        } else {
            return AjaxResponse::i()->setStatus(true)->setData($data)->send();
        }
    }*/
    
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  Payment::className(),
                'pk'=>'pmt_id'
            ],
            'get' => [
                'class' => Get::className(),
                'beforeServing'=>[$this,'beforeServing'],
                'modelClass'=>  Payment::className(),
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  Payment::className(),
            ],
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>\Yii::$app->request->get("perpage",10),
                'query'=> Payment::find() 
                ->asArray()
                ->where("emp_id = :emp_id",[":emp_id"=>\Yii::$app->request->get("emp_id")])->orderBy("pmt_id DESC"),
            ],
        ];
    }
    
    public function beforeServing($r){
        $r["pmt_date"] = Helpers::i()->formatDate($r["pmt_date"],"d M Y");
        $month = Helpers::i()->formatDate($r["pmt_month"],"m");
        $year = Helpers::i()->formatDate($r["pmt_month"],"Y");
        $r["pmt_month"] = $month;
        $r["pmt_year"] = $year;
        return $r;
    }
    
    public function beforeSaving(Event $e){
        if(isset($_POST["Payment"]["pmt_year"])){
            $payment_month = Helpers::i()->formatDate($_POST["Payment"]["pmt_year"]."-".$_POST["Payment"]["pmt_month"]."-01","Y-m-t");
        } else {
            $payment_month = Helpers::i()->formatDate($_POST["Payment"]["pmt_month"], "Y-m-t");
        }
        
        $e->sender->pmt_date = Helpers::i()->formatDate($e->sender->pmt_date,"Y-m-d");
        $e->sender->pmt_month = $payment_month;
       
    }
    
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
        Event::on(Payment::class, Payment::EVENT_BEFORE_VALIDATE, [$this,"beforeSaving"]);
        Event::on(Payment::class, Payment::EVENT_BEFORE_VALIDATE, [$this,"beforeSaving"]);
    }

}
