<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\Employee;
use Faker;
use app\models\EmployeeTypeHistory;
use app\models\Project;


 
class NotificationController extends Controller
{
    public function __construct($id, $module, $config = []) {
        parent::__construct($id, $module, $config);
        $this->enableCsrfValidation = false;
        header("Access-Control-Allow-Origin: *");
    }
    public function actionIndex(){
   //     \app\components\PushNotification::i()->sendFor(243);
    }
    
    
    public function actionSubscribe(){
        \app\components\PushNotification::i()->subscribe(\Yii::$app->request->post("emp_id"), \Yii::$app->request->post("newSubscription"));
        return \app\components\AjaxResponse::i()->setStatus(true)->send();
    }
    
    public function actionTest(){
        $publicKey = 'BFZSrzmIgLznfySh9srRdVMyFv9Q8kGopM9wlBD9WV4dFJfKvserBbQNYBAooqjpK8yygrEo6HDJdu9M_YpfW8Q';
        $privateKey = 'FUU1KRxFfomrRF5Zhzt4GzI0qFPnn6z8zw9xZMBA94w';
        
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:contact@vedantasoftware.com', // can be a mailto: or your website address
                'publicKey' => $publicKey, // (recommended) uncompressed public key P-256 encoded in Base64-URL
                'privateKey' => $privateKey, // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL               
            ],
        ];
        $webPush = new \Minishlink\WebPush\WebPush($auth);
        $notifications = [];
        $models = \app\models\PNotiSubscriptions::find()->all();
        foreach($models as $model){
            $notifications[] = [
                "subscription"=> \Minishlink\WebPush\Subscription::create(\yii\helpers\Json::decode(
                           $model->sub_data
                       )),
                "payload"=> \yii\helpers\Json::encode([
                    "title"=>"Text",                        
                    "data"=> ["url"=>"https://google.com"],
                ]),
            ];
        }
        
        
        foreach($notifications as $notification){
        
            $webPush->queueNotification(
                $notification['subscription'],
                $notification['payload'] // optional (defaults null)
            );
        }
        
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                echo "SUCCESS: ";      
            } else {
               // if($report->isSubscriptionExpired()){
                    echo "EXPIRED: ";
                //}
            }
            echo $endpoint."<br />";
        }
    }
}