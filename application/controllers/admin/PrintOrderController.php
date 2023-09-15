<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\Helpers;
use app\models\ProjectPrintOrder;
use app\models\ProjectCost;
use app\models\ProjectIncentive;
use app\models\ProjectTask;
use Yii;
use yii\base\Event;

class PrintOrderController extends AdminController
{
    public function actions()
    {
        return [            
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  ProjectPrintOrder::className(),     
                'pk'=>'pr_id'
            ],            
            'get' => [
                'class' => Get::className(),                
                'beforeServing'=>[$this,'beforeServing'],
                'modelClass'=>  ProjectPrintOrder::className(),                     
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  ProjectPrintOrder::className(),                     
            ],            
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> ProjectPrintOrder::find()         
                ->asArray()->with(["project"])
                ->where(["proj_id"=>Yii::$app->request->get("proj_id")])->orderBy("pr_date DESC, pr_id desc"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> ProjectPrintOrder::find()->orderBy("pr_date DESC"),
            ],
        ];
    }
    
    public function beforeServing($r){ 
        $r["pr_date"] = Helpers::i()->formatDate($r["pr_date"],"d M Y");        
        return $r;
    }
      
    public function beforeSave(Event $e){                
        $e->sender->pr_date = Helpers::i()->formatDate($e->sender->pr_date,"Y-m-d");        
    }
    
    public function afterSave(Event $e){   
        if(\Yii::$app->request->post("updateIncentives") == "true"){
            \Yii::debug("Fired!");
            //Delete incentive data
            ProjectIncentive::deleteAll(["pr_id"=>$e->sender->pr_id]);
            //Add Incentive data
            $projectCostModels = ProjectCost::find()->where(["proj_id"=>$e->sender->proj_id])->all();
            foreach($projectCostModels as $projectCost){
                $projectTaskModels = ProjectTask::find()->with(["alottedTo"])->where(["cost_id"=>$projectCost->cost_id,"ts_status"=>"Completed"])->all();
                foreach ($projectTaskModels as $projectTask){
			  
                    if($projectTask->alottedTo->canGetIncentive($projectTask,$e->sender)){
                        $incentiveModel = new ProjectIncentive();
                        $incentiveModel->inc_date = $e->sender->pr_date;
                        $incentiveModel->inc_amount = $projectCost->cost_incentive_rate * $projectTask->ts_amount / 100;
 
                        $incentiveModel->pr_id = $e->sender->pr_id;
                        $incentiveModel->ts_id = $projectTask->ts_id;
                        if($incentiveModel->validate()){
                            $incentiveModel->save();
                        }
                    }
                }
            }

        }   
    }   

    public function __construct($id, $module) {
        parent::__construct($id, $module);
         
     
        Event::on(ProjectPrintOrder::class, ProjectPrintOrder::EVENT_BEFORE_INSERT, [$this,"beforeSave"]);
        Event::on(ProjectPrintOrder::class, ProjectPrintOrder::EVENT_BEFORE_UPDATE, [$this,"beforeSave"]);
        
        Event::on(ProjectPrintOrder::class, ProjectPrintOrder::EVENT_AFTER_INSERT, [$this,"afterSave"]);
        Event::on(ProjectPrintOrder::class, ProjectPrintOrder::EVENT_AFTER_UPDATE, [$this,"afterSave"]);
         
    }
    
     
}
