<?php

namespace app\controllers\manager;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;

use app\models\Task;
use Yii;
 

class TaskController extends \app\components\ManagerController
{
    public function actions()
    {
        return [ 
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> Task::find()->orderBy("task_title ASC"),
            ],
        ];
    }
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);        
        
         
    }
    
}
