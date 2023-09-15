<?php

namespace app\controllers\associate;

use app\components\AjaxResponse;
use app\components\EmployeeController;
use app\components\TempData;
 

class MeController extends EmployeeController
{
    public function actionChangePassword(){
        $pass1 = \Yii::$app->request->post("new_password");
        
        $pass2 = \Yii::$app->request->post("new_password2");
        $user = TempData::i()->get("loggedInUser");
        
        if(trim($pass1) == ""){
            return AjaxResponse::i()->setError("Please enter a password")->send();
        }
        
        if($pass1 === $pass2){
            $user->emp_password = $pass1;
            if($user->validate()){
                $user->save();
                return AjaxResponse::i()->setStatus(true)->send();
            } else {
                return AjaxResponse::i()->setValidationStatus(false)
                        ->setValidationErrors($user->getErrors())->send();
            }
        } else {
            return AjaxResponse::i()->setError("Both passwords must be same")->send();
        }
    }
}