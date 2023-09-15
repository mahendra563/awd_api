<?php

namespace app\controllers\associate;

use app\components\actions\Loadlist;
 
use app\models\Employee;
use Yii;
use yii\db\Expression;


class EmployeeController extends \app\components\EmployeeController
{

    

    public function actions()
    {
        return [
             
            'listall-managers' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> $this->loadlist(Yii::$app->request->get("proj_id"))
                ->orderBy("emp_fullname ASC")
            ],
             
            
        ];
    } 
    
     public function loadlist($proj_id){ 
    
        $q = Employee::find();
        
        $w[] = "emp_status = :status AND project_managers.proj_id = :proj_id"; 
        $p[":status"] = Employee::STATUS_PRESENT;
        $p[":proj_id"] = $proj_id;
        
        $q = $q->leftJoin("project_managers","project_managers.emp_id = employees.emp_id");
        
        //$q = $q->select([new Expression('COUNT(work_entries.w_alotted_to) as alotted_work_count'), 'employees.*']);
               
       
            $q = $q->innerJoinWith("employeeRoleJoins");
            $w[] = "role_id = :role_id";
            $p[":role_id"] = 2;
       
        
        $where = implode(" AND ",$w);
        
        $q = $q->where($where,$p)->asArray();
        
        return $q;
    }
}
