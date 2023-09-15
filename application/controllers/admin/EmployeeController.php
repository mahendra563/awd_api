<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\components\AjaxResponse;
use app\components\Helpers;
use app\models\Employee;
use app\models\EmployeeRole;
use app\models\EmployeeRoleJoin;
use app\models\EmployeeTargetHistory;
use app\models\EmployeeTypeHistory;
use Yii;
use yii\base\Event;
use yii\db\Expression;


class EmployeeController extends AdminController
{

    

    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  Employee::className(),     
                'pk'=>'emp_id'
            ],
           
            'get' => [
                'class' => Get::className(),
                'with' => ["employeeRoles","currentEmployeeType","currentEmployeeTarget"],
                'beforeServing'=>[$this,'beforeServing'],
                'modelClass'=>  Employee::className(),                     
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->loadlist(
                    Yii::$app->request->get("search",""), 
                    Yii::$app->request->get("role_id",""),
                    Yii::$app->request->get("include",0)
                    )
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> $this->loadlistWithWorkCount("",Yii::$app->request->get("role",""),Yii::$app->request->get("include",0))
            ],
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> $this->loadlistWithWorkCount("",Yii::$app->request->get("role",""),Yii::$app->request->get("include",0))
            ],
            'roles-listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> EmployeeRole::find()->orderBy("role_id ASC"),
            ], 
            
            
            'type-save' => [
                'class' => Save::className(),
                'modelClass'=> EmployeeTypeHistory::className(),     
                'pk'=>'tp_id'
            ],
            'type-delete' => [
                'class' => Delete::className(),
                'modelClass'=> EmployeeTypeHistory::className(),     
            ],
            'type-get' => [
                'class' => Get::className(),                
                'beforeServing'=>[$this,'beforeServing_type'],
                'modelClass'=> EmployeeTypeHistory::className(),     
            ],  
            'type-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> EmployeeTypeHistory::find()->where(["emp_id"=>\Yii::$app->request->get("emp_id")])->orderBy("tp_startdate desc, tp_id desc")
            ],
            
            'target-save' => [
                'class' => Save::className(),
                'modelClass'=> EmployeeTargetHistory::className(),     
                'pk'=>'tg_id'
            ],
            'target-delete' => [
                'class' => Delete::className(),
                'modelClass'=> EmployeeTargetHistory::className(),     
            ],
            'target-get' => [
                'class' => Get::className(),                
                'beforeServing'=>[$this,'beforeServing_target'],
                'modelClass'=> EmployeeTargetHistory::className(),     
            ],  
            'target-loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> EmployeeTargetHistory::find()->where(["emp_id"=>\Yii::$app->request->get("emp_id")])->orderBy("tg_startdate desc, tg_id desc")
            ],
            
        ];
    }
    public function actionDelete(){
        $id = \Yii::$app->request->post("id");
        $model = Employee::findOne($id);
        if(!is_null($model)){
            $model->emp_status = Employee::STATUS_DELETED;
            $model->save();
        }
        return AjaxResponse::i()->setStatus(true)->send();
    }
    public function beforeServing_target($r){ 
        $r["tg_startdate"] = Helpers::i()->formatDate($r["tg_startdate"],"d M Y");
        return $r;
    }
    
    public function beforeServing_type($r){ 
        $r["tp_startdate"] = Helpers::i()->formatDate($r["tp_startdate"],"d M Y");
        return $r;
    }

    public function beforeServing($r){
        $r["role_ids"] = $r["employeeRoles"];
        $r["tp_type"] = $r["currentEmployeeType"]["tp_type"];
      
       
        $r["tg_amount"] = $r["currentEmployeeTarget"]["tg_amount"];
        return $r;
    }
     
    public function loadlist($search,$role="",$include=0){
        $q = Employee::find();
        
        $w[] = "emp_fullname LIKE :search AND emp_status = :status";
        $p[":search"] ="%".$search."%";
        $p[":status"] = Employee::STATUS_PRESENT;
        
         
        if(trim($role)!==""){
            $q->innerJoinWith("employeeRoleJoins");
            $w[] = "role_id = :role_id";
            $p[":role_id"] = $role;
        }
        
        if($include > 0){
            $where = "(".implode(" AND ",$w).") OR employees.emp_id = :include";
            $p[":include"] = $include;
        } else {
            $where = implode(" AND ",$w);
        }
        
        $q = $q->with(["employeeRoles","currentEmployeeType","currentEmployeeTarget"])
                ->where($where,$p)
                ->orderBy("emp_id desc")->asArray();
        
        return $q;
    }
    
    public function loadlistWithWorkCount($search,$role="",$include=0){
        $q = Employee::find();
        
        $w[] = "emp_fullname LIKE :search AND emp_status = :status";
        $p[":search"] ="%".$search."%";
        $p[":status"] = Employee::STATUS_PRESENT;
        
        $q = $q->leftJoin("work_entries","work_entries.w_alotted_to = employees.emp_id AND (work_entries.w_status = 'Transferred' OR work_entries.w_status = 'Pending' OR work_entries.w_status = 'Approved')");
        $q = $q->select([new Expression('COUNT(work_entries.w_alotted_to) as alotted_work_count'), 'employees.*']);
         
        
        if(trim($role)!==""){
            $q->innerJoinWith("employeeRoleJoins");
            $w[] = "role_id = :role_id";
            $p[":role_id"] = $role;
        }
        
        if($include > 0){
            $where = "(".implode(" AND ",$w).") OR employees.emp_id = :include";
            $p[":include"] = $include;
        } else {
            $where = implode(" AND ",$w);
        }
        
        $q = $q->with(["employeeRoles","currentEmployeeType","currentEmployeeTarget"])
                ->where($where,$p)
                ->groupBy("employees.emp_id")
                ->orderBy("alotted_work_count asc")->asArray();
        
        return $q;
    }
    
    public function __construct($id, $module) {
        parent::__construct($id, $module);
         
        Event::on(Employee::class, Employee::EVENT_AFTER_INSERT, [$this,"saveEmployee"]);
        Event::on(Employee::class, Employee::EVENT_AFTER_UPDATE, [$this,"saveEmployee"]);
        
        

        Event::on(EmployeeTypeHistory::class, EmployeeTypeHistory::EVENT_BEFORE_INSERT, [$this,"beforeSaveEmployeeTypeHistory"]);
        Event::on(EmployeeTypeHistory::class, EmployeeTypeHistory::EVENT_BEFORE_UPDATE, [$this,"beforeSaveEmployeeTypeHistory"]);
        
        Event::on(EmployeeTargetHistory::class, EmployeeTargetHistory::EVENT_BEFORE_INSERT, [$this,"beforeSaveEmployeeTargetHistory"]);
        Event::on(EmployeeTargetHistory::class, EmployeeTargetHistory::EVENT_BEFORE_UPDATE, [$this,"beforeSaveEmployeeTargetHistory"]);
    }
    
    public function beforeSaveEmployeeTypeHistory(Event $e){
        $e->sender->tp_startdate = Helpers::i()->formatDate($e->sender->tp_startdate,"Y-m-d");
    }
    
    public function beforeSaveEmployeeTargetHistory(Event $e){
        $e->sender->tg_startdate = Helpers::i()->formatDate($e->sender->tg_startdate,"Y-m-d");
    }

    public function saveEmployee(Event $e){
        
        if(\Yii::$app->controller->action->id == "delete"){
            return;
        }
        
        EmployeeRoleJoin::deleteAll(["emp_id"=>$e->sender->emp_id]);
        foreach(\Yii::$app->request->post("role_ids",[]) as $role_id){
            $model = new EmployeeRoleJoin();
            $model->emp_id = $e->sender->emp_id;
            $model->role_id = $role_id;
            if($model->validate()){
                $model->save();
            } else {
                \Yii::debug($model->getErrors());
            }
        }
        
        if(is_null($e->sender->currentEmployeeType) || 
                
                ( !is_null($e->sender->currentEmployeeType) &&
                $e->sender->currentEmployeeType->tp_type !== Yii::$app->request->post("tp_type") )
                
                ){
            if(trim(Yii::$app->request->post("tp_type")) == ""){
                echo AjaxResponse::i()->setError("Employee Type is Required")->display();
            }
            $model = new EmployeeTypeHistory();
            $model->tp_startdate = date("Y-m-d");            
            $model->emp_id = $e->sender->emp_id;
            $model->tp_type = Yii::$app->request->post("tp_type");
            
            if($model->validate()){
                $model->save();
            } else {
                \Yii::debug($model->getErrors());
            }

            
        }
        
        if(is_null($e->sender->currentEmployeeTarget) || 
                
                ( !is_null($e->sender->currentEmployeeTarget) &&
                $e->sender->currentEmployeeTarget->tg_amount != Yii::$app->request->post("tg_amount") )
                
                ){
            if(trim(Yii::$app->request->post("tg_amount")) == ""){
                echo AjaxResponse::i()->setError("Target Amount is Required")->display();
            }
            $model = new EmployeeTargetHistory();
            $model->tg_startdate = date("Y-m-d");            
            $model->emp_id = $e->sender->emp_id;
            $model->tg_amount = Yii::$app->request->post("tg_amount");
            
            if($model->validate()){
                $model->save();
            } else {
                \Yii::debug($model->getErrors());
            }

            
        }
    }
    
     
}
