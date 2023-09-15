<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\models\OldWork;
use Yii;
 

class OldWorkController extends AdminController
{
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  OldWork::className(),     
                'pk'=>'work_id'
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  OldWork::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  OldWork::className(),                     
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->loadlist(\Yii::$app->request->get("emp_id"))
            ],
            
        ];
    }
    
    public function loadlist($emp_id=0){
        $q = OldWork::find();
        if($emp_id > 0){
            $q = $q->where(["emp_id"=>$emp_id]);
        }
        return $q->with(["employee"])->asArray()->orderBy("work_id desc");
    }
    
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);        
         
         
        \yii\base\Event::on(OldWork::class,OldWork::EVENT_BEFORE_VALIDATE, function(\yii\base\Event $e){
            $e->sender->work_date = \app\components\Helpers::i()->formatDate($e->sender->work_date,"Y-m-d");             
        });
   
    }
    
    
    
}
