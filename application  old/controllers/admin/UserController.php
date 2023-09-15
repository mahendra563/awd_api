<?php

namespace app\controllers\admin;
use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist; 
use app\components\AdminController;
use app\models\User;
use Yii;
use yii\base\Event;
 
class UserController extends AdminController
{
    public function actions()
    {
        return [
            
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  User::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  User::className(),                  
                'beforeServing'=>[$this,"beforeServing"]
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> User::find()->where("user_name LIKE :s",[":s"=>"%".Yii::$app->request->get("search")."%"])->orderBy("user_id DESC"),
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> User::find()->orderBy("user_name ASC"),
            ],
            
        ];
     
    }
    
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);        
        \yii\base\Event::on(User::class,User::EVENT_BEFORE_DELETE, function(Event $e){
            if($e->sender->user_id == 1){
                return \app\components\AjaxResponse::i()->setError("Super Admin can not be deleted")->display();
            }
            //Associate all project tasks to super admin
            \app\models\ProjectTask::updateAll(["user_id"=>1], ["user_id"=>$e->sender->user_id]);
        });
        
    }
    
    public function actionSave(){
        $model = new User();
   
        $params = Yii::$app->request->post("User");


        if($params["user_id"] > 0){
            $model = User::findOne($params["user_id"]);
        }
        if($params["user_password"]!==""){            
            $params["user_password"] = md5($params["user_password"]);             
        } else {          
            if(!$model->isNewRecord){                
                $params["user_password"] = $model->user_password;
               
            }
        }

        if($model->load(["User"=>$params]) && $model->validate()){  
           
            $model->save();
            return \app\components\AjaxResponse::i()->setStatus(true)->send();
        }
        return \app\components\AjaxResponse::i()->setValidationStatus(false)
                ->setValidationErrors($model->getErrors())
                ->send();
    }
 
    public function beforeServing($r){
        $r["user_password"] = "";
        return $r;
    }
}
