<?php

namespace app\controllers;

use app\components\AjaxResponse;
use app\models\AccessToken;
use app\models\Category;
use app\models\Content;
use app\models\Entry;
use app\models\User;
use Dompdf\Dompdf;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
   
      public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
	header("Access-Control-Allow-Origin: *");
	$this->enableCsrfValidation = false; 
         
	//header("Access-Control-Allow-Methods: OPTIONS");

        /*$token = \Yii::$app->request->get("token",null);
        if(is_null($token)){
            $token = \Yii::$app->request->post("token",null);
        }
        $model = \app\models\AccessToken::find()
                ->where("token_uid = :token AND token_expiry > :now",[
                    ":token"=>$token,
                    ":now"=>date("Y-m-d H:i:s")
                ])->one();
        if(is_null($model)){
            AjaxResponse::i()->setAuthentication(false)
                    ->setAuthorization(false)->display();  
        }*/
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        echo \Yii::$app->request->userIP;
        
        //return $this->render('index');
    }

    
    public function actionFaker(){
        
    }
    
    public function actionCheckLogin(){
        $token = Yii::$app->request->get("token");
        $model = AccessToken::find()->where("token_uid = :token AND token_expiry >= :now AND token_ip = :ip",[
            ":token"=>$token, ":now"=>date("Y-m-d H:i:s"),
            ":ip"=>\Yii::$app->request->userIP
        ])->one();        
        if(is_null($model)){
            return AjaxResponse::i()->setAuthentication(false)
                    ->send();
        }
        return AjaxResponse::i()->send();
    }
    
    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $login_auth = 1;

        $username = Yii::$app->request->post("username");
        $password = Yii::$app->request->post("password");
        
        $type = Yii::$app->request->post("type","User");
        
       // lock full Panel associate and maneger
        if($type == "Employee"){
            if(  $login_auth == 1){
            $userModel = \app\models\Employee::find()->where(["emp_email"=>$username,"emp_password"=>md5($password)])
                ->one();
            }
            else{
               return AjaxResponse::i()
                    ->setError("AWD पर कार्य चढाने की  इस माह की तिथि समाप्त हो गयी है। अतः अब कार्य अगले माह में ही चढ़ा पाएंगे .......")
                     ->send();
            }
                    } else {
            /*$userModel = User::find()->where(["user_name"=>$username,"user_password"=>md5($password)])
                ->one();*/
             $userModel = User::find()->where(["user_name"=>$username,"user_password"=>md5($password)])
                ->one();   
        }
        if(is_null($userModel)){
            return AjaxResponse::i()
                    ->setAuthentication(true)
                    ->setAuthorization(true)
                    ->setError("Invalid Username or Password")
                    ->send();
        }
        
        $tokenModel = AccessToken::find()
                ->where("person_id = :person_id AND token_expiry > :now AND token_type = :type AND token_ip = :ip",[
                    ":person_id"=>$type == "Employee" ? $userModel->emp_id : $userModel->user_id,
                    ":ip" => \Yii::$app->request->userIP,
                    ":type"=>$type,
                    ":now"=>date("Y-m-d H:i:s")
                ])->one();
        
        if(is_null($tokenModel)){
            $token = md5(uniqid());
            $tokenModel = new AccessToken();
            $tokenModel->person_id = $type == "Employee" ? $userModel->emp_id : $userModel->user_id;
            $tokenModel->token_uid = $token;
            $tokenModel->token_type = $type;
            $tokenModel->token_ip = \Yii::$app->request->userIP;
            $tokenModel->token_expiry = date("Y-m-d H:i:s", strtotime("+1Hour"));
            if($tokenModel->validate()){
                $tokenModel->save();
                
                if($type =="User"){
                    $person = ["user_name"=>$userModel->user_name,"user_id"=>$userModel->user_id];
                } else {
                    $roles = array_values(\yii\helpers\ArrayHelper::map($userModel->employeeRoles, "role_id", "role_title"));
                    $person = ["user_name"=>$userModel->emp_fullname,"user_id"=>$userModel->emp_id, "roles"=>$roles];
                }
                
                return AjaxResponse::i() 
                        ->setData(["token"=>$token,"user"=>$person,"type"=>$type])
                        ->setStatus(true)->send();
            } else {
                return AjaxResponse::i() 
                        ->setAuthentication(false)
                        ->setAuthorization(false)
                        ->setError("Can not create token")
                        ->setStatus(false)->send();
            }
        } else {
            if($type =="User"){
                $person = ["user_name"=>$userModel->user_name,"user_id"=>$userModel->user_id];
            } else {
                $roles = array_values(\yii\helpers\ArrayHelper::map($userModel->employeeRoles, "role_id", "role_title"));
                $person = ["user_name"=>$userModel->emp_fullname,"user_id"=>$userModel->emp_id, "roles"=>$roles];
            }
		return AjaxResponse::i() 
                        ->setData([ "token"=>$tokenModel->token_uid,
                            "user"=>$person,
                            "type"=>$type ])
                        ->setStatus(true)->send();
	}   
    }
     /*
    public function actionUpgrade(){
        $projectModels = \app\models\Project::find()->all();
        foreach($projectModels as $projectModel){
            if($projectModel->proj_cost == 0){
                $totalCost = 0;
                foreach($projectModel->projectCosts as $costModel){
                    //Calculate Total Project Cost;
                    $totalCost += $costModel->cost_amount;
                }
                $projectModel->proj_cost = $totalCost;
                $projectModel->save();
                foreach($projectModel->projectCosts as $costModel){
                    //Calculate Percentage of Each Cost Object  
                    
                    $cost_percentage = (100 * $costModel->cost_amount) / $totalCost;
                    $cost_percentage = number_format($cost_percentage, 2, ".", "");
                    echo $projectModel->proj_id." - ".$totalCost." - ".$costModel->cost_amount." (".$cost_percentage."%)"."<br />";
                            
                    \app\models\ProjectCost::updateAll(["cost_amount"=>$cost_percentage], [
                        "cost_id"=>$costModel->cost_id
                    ]);
                    
                }
            }            
        }
        echo "Done!";
    }*/
}
