<?php

namespace app\controllers\admin;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
use app\components\AdminController;
use app\models\Employee;
use app\models\EmployeeRole;
use app\models\EmployeeRoleJoin;
use app\models\EmployeeTypeHistory;
use Yii;
use yii\base\Event;
use app\components\Helpers;
use app\models\ProjectTask;


class DefaultController extends AdminController
{
    public function actionDashboard(){
        $data["count"]["projects"] = \app\models\Project::find()->count();
        $data["count"]["employees"] = \app\models\Employee::find()->where("emp_status = 'Present'")->count();
        $data["count"]["project_tasks"] = \app\models\ProjectTask::find()->count();
        
        $data["count"]["work_entries"] = \app\models\WorkEntry::find()->where("w_status <> 'Sent to AWD'")->count();
        $data["count"]["leave_requests"] = \app\models\LeaveRequest::find()->where(["rq_status"=>'Pending'])->count();

        //$data["users"] = \app\models\User::find()->count();
        
        //Latest Work Entries
        $data["list"]["work_entries"] = \app\models\WorkEntry::find()
                ->with(["project","alottedBy","alottedTo"])
                ->where("w_status <> 'Sent to AWD'")
                ->asArray()
                ->orderBy("w_lastupdated_on desc")->limit(10)->all();
        //Latest Leave Request
        $data["list"]["leave_requests"] = \app\models\LeaveRequest::find()->where("rq_status = 'Pending'")
                ->asArray()->with("employee")
                ->orderBy("rq_updated_on desc")->limit(10)->all();
        //Latest Uploads
        $data["list"]["qbank_files"] = \app\models\QbFile::find()
                ->asArray()
                ->with("employee")
                ->orderBy("file_updated_on desc")->limit(10)->all();
        
        return \app\components\AjaxResponse::i()->setData($data)->send();
    }
}
