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

class AuthareaController extends \app\components\AdminController
{
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
            $user = \app\models\Employee::findOne($model->person_id);
        }
       // $model->token_ip = \Yii::$app->request->userIP;
	$model->token_expiry = date("Y-m-d H:i:s", strtotime("+1Hour"));
	$model->save();
        //\Yii::$app->user->login($user);
        TempData::i()->save("loggedInUser", $user);
        TempData::i()->save("loggedInUserType", $model->token_type);
        return $user;
    }
    
     
}