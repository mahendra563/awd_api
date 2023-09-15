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
class ManagerController extends \yii\web\Controller{
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
	header("Access-Control-Allow-Origin: *");
	$this->enableCsrfValidation = false;   
	//header("Access-Control-Allow-Methods: OPTIONS");
        $user = $this->login();
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
            
             AjaxResponse::i()->setAuthentication(false)->setError("Only employees can access this area")->setAuthorization(false)->display();            
        } else {
            $user = \app\models\Employee::findOne($model->person_id);
            
            $role_ids = \yii\helpers\ArrayHelper::map($user->employeeRoles,"role_id","role_id");
            if(!isset($role_ids["2"])){
                AjaxResponse::i()->setAuthentication(true)->setAuthorization(false)->display();
            }
            
        }
        
        
	$model->token_expiry = date("Y-m-d H:i:s", strtotime("+1Hour"));
	$model->save();
        //\Yii::$app->user->login($user);
        TempData::i()->save("loggedInUser", $user);
        TempData::i()->save("loggedInUserType", $model->token_type);
        return $user;
    }
    
    
}

