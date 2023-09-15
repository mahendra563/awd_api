<?php

namespace app\controllers\manager;

use app\components\actions\Loadlist;
use app\components\ManagerController;
use app\models\Employee;
use Yii;
use yii\db\Expression;


class EmployeeController extends ManagerController
{

    

    public function actions()
    {
        return [
             
            'listall' => [
                'class' => Loadlist::className(),
                'pageSize'=>1000000000,
                'query'=> $this->loadlist("",Yii::$app->request->get("role",""),Yii::$app->request->get("include",0))
                ->orderBy("alotted_work_count ASC")
            ],
             
            
        ];
    } 
    
     public function loadlist($search,$role="",$include=0){ 
    
        $q = Employee::find();
        
        $w[] = "emp_fullname LIKE :search AND emp_status = :status";
        $p[":search"] ="%".$search."%";
        $p[":status"] = Employee::STATUS_PRESENT;
        
        $q = $q->leftJoin("work_entries","work_entries.w_alotted_to = employees.emp_id AND (work_entries.w_status = 'Transferred' OR work_entries.w_status = 'Pending' OR work_entries.w_status = 'Approved')");
        $q = $q->select([new Expression('COUNT(work_entries.w_alotted_to) as alotted_work_count'), 'employees.*']);
               
        if(trim($role)!==""){
            $q = $q->innerJoinWith("employeeRoleJoins");
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
                ->asArray();
        
        return $q;
    }
}
