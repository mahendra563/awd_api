<?php
/*
Application Developed By Abhinav Software
Website: http://abhinavsoftware.com
Email: contact@abhinavsoftware.com
Developer: Ankur Gupta (ankurgupta555@gmail.com)
Copyright Ankur Gupta

For licensing and terms of use please read license.txt file
*/

namespace app\components;
class AdminController extends \yii\web\Controller{
    public function __construct($id, $module, $config = array()) {
        
        parent::__construct($id, $module, $config);
	header("Access-Control-Allow-Origin: *");
	$this->enableCsrfValidation = false;   
	//header("Access-Control-Allow-Methods: OPTIONS");
        

       
    }
    
    
    public function login(){
        
        $token = null;
        
        if(isset($_GET["token"])){
            $token = \Yii::$app->request->get("token",null);
        }
        
        if(isset($_POST["token"])){
            $token = \Yii::$app->request->post("token",null);
        }
         
        
        \app\models\AccessToken::deleteAll("token_expiry < :now",[":now"=>date("Y-m-d H:i:s")]);
        $model = \app\models\AccessToken::find()
        ->where("token_uid = :token AND token_expiry > :now AND token_ip = :ip",[
            ":token"=>$token,
            ":ip"=>\Yii::$app->request->userIP,
            ":now"=>date("Y-m-d H:i:s")
        ])->one();
        if(is_null($model)){
            AjaxResponse::i()->setAuthentication(false)
            ->setAuthorization(false)->display();
        }
        
        if($model->token_type == "User"){
            $user = \app\models\User::findOne($model->person_id);
        } else if($model->token_type == "Employee"){
            AjaxResponse::i()->setAuthentication(false)->setError("Only operators and super admins can access this area")->setAuthorization(false)->display();  
            //$user = \app\models\Employee::findOne($model->person_id);
        }
        
	$model->token_expiry = date("Y-m-d H:i:s", strtotime("+1Hour"));
	$model->save();
        //\Yii::$app->user->login($user);
        TempData::i()->save("loggedInUser", $user);
        TempData::i()->save("loggedInUserType", $model->token_type);
        return $user;
    }
    
    public function beforeAction($action){
        $user = $this->login();
        if($user->user_id > 1){
            $super_admin_actions = ["delete","type-delete",
                "history-delete","file-delete","target-delete",
                "account-delete","request-delete","holiday-delete"
                ];            
            if(in_array($action->id, $super_admin_actions)){
                AjaxResponse::i()->setAuthorization(false)->display();
            }
        } 
        return parent::beforeAction($action); 
    }
}

