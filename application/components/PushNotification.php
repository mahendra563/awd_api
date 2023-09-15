<?php

namespace app\components;

class PushNotification extends \app\components\AbstractSinglaton{
    
    public function send(){
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
        $webPush->queueNotification(
            $notification['subscription'],
            $notification['payload'] // optional (defaults null)
        );
        $notification = [
            "subscription"=> \Minishlink\WebPush\Subscription::create(\yii\helpers\Json::decode(
                    '{"endpoint":"https://fcm.googleapis.com/fcm/send/evMdXbdHBE0:APA91bGAI0pZiBBMRyptx0DhmDxxl58y91Y4S0hLOhJYFLs2IwWrvVEnLD_YqZK67yfZ5f8vw5ZqbZimddI9mMPXZGwIu1VGssLY7JCCXQdHXWO81puwjyDSvgMzU_bmQ7s_POG7ZB2K","expirationTime":null,"keys":{"p256dh":"BAxOa88fRBFl91fEHxO4QZtNl-F0gCe4QIGGNCm3Yyo2tkF4PyRpQivFxZ9gqGy3ypdfrhZx_aM7Sxh66rlfyDQ","auth":"x4pG_YbBAUjABt7t2EN76g"}}'
                   )),
            "payload"=> \yii\helpers\Json::encode([
                "title"=>"Text",                        
                "data"=> ["url"=>"https://google.com"],
            ]),
        ];
        
        $webPush->queueNotification(
            $notification['subscription'],
            $notification['payload'] // optional (defaults null)
        );
        
    }
    
    public function sendFor($emp_id) {
        $subModels = \app\models\PNotiSubscriptions::findAll(["emp_id"=>$emp_id]);
        $notifications = [];
        foreach($subModels as $subModel){
            //Create another loop for fetching notification list and send them one by one
            $entModels = \app\models\PNotiEntry::findAll(["emp_id"=>$emp_id,"ent_sent"=>0]);
            foreach($entModels as $entModel){
                $notifications[] = [
                    "entModel"=>$entModel,
                    "subscription"=> \Minishlink\WebPush\Subscription::create(\yii\helpers\Json::decode($subModel->sub_data)),
                    "payload"=> \yii\helpers\Json::encode([
                        "title"=>$entModel->ent_title,
                        //"message"=>"Message Description",
                        "data"=> ["url"=>$entModel->ent_url],
                        //"actions"=>[
                        //    ["action"=>"open","title"=>"Open App"],
//                            ["action"=>"cancel","title"=>"Cancel"],
  //                      ]
                    ]),                    
                ];
            }
        }
        
        if(count($notifications) == 0){
            return false;
        }
        
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

        foreach ($notifications as $notification) {            
            $webPush->queueNotification(
                $notification['subscription'],
                $notification['payload'] // optional (defaults null)
            );
            $notification['entModel']->ent_sent = 1;
            $notification['entModel']->save();
        }
         
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                 
            } else {
                if($report->isSubscriptionExpired()){
                    \app\models\PNotiSubscriptions::deleteAll(["sub_endpoint"=>$endpoint]);
                }
            }
        }
        
    }
     
    public function subscribe($emp_id,$notidata){
        $endpoint = \yii\helpers\Json::decode($notidata)["endpoint"];
        $model = \app\models\PNotiSubscriptions::findOne([
            "sub_endpoint"=>$endpoint,            
            "emp_id"=>$emp_id
        ]);
        if(is_null($model)){
            $model = new \app\models\PNotiSubscriptions();
            $model->emp_id = $emp_id;
            $model->sub_data = $notidata;
            $model->sub_endpoint = $endpoint;
            if($model->validate()){
                $model->save();
            }
        }
        //\app\models\PNotiSubscriptions::deleteAll(["emp_id"=>$emp_id]);
        
    }
}