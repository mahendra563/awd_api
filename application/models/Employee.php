<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "employees".
 *
 * @property int $emp_id
 * @property string $emp_fullname
 * @property int $emp_status
 * @property int $emp_email
 * @property int $emp_password
 * 
 *
 * @property EmployeeRolesJoin[] $employeeRolesJoins
 * @property EmployeeTypeHistory[] $employeeTypeHistories
 * @property Payment[] $payments
 * @property Workdone[] $workdones
 */
class Employee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employees';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_fullname','emp_email'], 'required'],
            [['emp_password'], 'default','value'=>''],
            [['emp_uid'], 'string'],
            [['emp_uid','emp_email'], 'unique'],
            [['emp_status'],'default','value'=>self::STATUS_PRESENT],
            [['emp_fullname'], 'string', 'max' => 255],
            
         
        ];
    }
    
    const STATUS_PRESENT = "Present";
    const STATUS_DELETED = "Deleted";

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'emp_id' => 'ID',
            'emp_uid' => 'Employee ID',
            'emp_fullname' => 'Full Name',
            'emp_email' => 'Email',
            'emp_status' => 'Status',
            'emp_password' => 'Password',
          
        ];
    }
    
    public static function validateEmployee($username, $password) {
        $model = self::find()
                ->where(["emp_email"=>$username, "emp_password"=>md5($password),'emp_status'=>self::STATUS_PRESENT])
                ->one();
        if(is_null($model)){
            return false;
        }
        return true;
    }
    
    public function getTargetOnDate($date){
        $model = EmployeeTargetHistory::find()
                ->where("tg_startdate <= :date AND emp_id = :emp_id",[
                    ":date"=>$date,":emp_id"=>$this->emp_id
                ])
                ->orderBy("tg_startdate desc, tg_id desc")
                ->one(); 
        if(is_null($model)){
            return null;
        }
        return $model->tg_amount;
    }
    
    public function getTypeOnDate($date){
        $model = EmployeeTypeHistory::find()
                ->where("tp_startdate <= :date AND emp_id = :emp_id",[
                    ":date"=>$date,":emp_id"=>$this->emp_id
                ])
                ->orderBy("tp_startdate desc,  tp_id desc")
                ->one(); 
        if(is_null($model)){
            return null;
        }
        return $model->tp_type;
    }
    
    public function canGetIncentive(ProjectTask $projectTask, ProjectPrintOrder $printOrder ){
        
        if($this->emp_status == self::STATUS_DELETED){
            return false;
        }
        
        //$task_date,$print_date;
        $task_date_type = $this->getTypeOnDate($projectTask->ts_completion_date);        
        $print_date_type = $this->getTypeOnDate($printOrder->pr_date);        
        
        if($task_date_type == "Insider" && $print_date_type == "Insider"){
            //Check if type changed between dates
            $between = EmployeeTypeHistory::find()
                        ->where("emp_id = :emp_id  AND tp_startdate >= :task_date AND tp_startdate < :print_date AND tp_type <> 'Insider'",[
                            ":task_date"=>$projectTask->ts_completion_date, 
                            ":print_date"=>$printOrder->pr_date,                            
                            ":emp_id"=>$this->emp_id
                        ])->count();
                         
            if($between>0){
                //It means employee type changed between two dates 
                //No incentive can be given now
                return false;
            } else {
                //He is continuous Insider employee... so incentive can be given
                return true;
            }
        } else if($task_date_type == "Outsider" && $print_date_type == "Outsider"){
            //We can not give incentive to outsiders
            return false;
        } else if($task_date_type == "Outsider" && $print_date_type == "Insider"){
            //We can not give incentive to those who have completed their work as Outsiders
            return false;
        } else if($task_date_type == "Insider" && $print_date_type == "Outsider"){
             
            //Check out many print orders given on that particular project between date
            $printOrderCount = ProjectPrintOrder::find()->where("pr_date >= :task_date AND pr_date < :print_date AND proj_id = :proj_id",[
                ":task_date" => $projectTask->ts_completion_date,
                ":print_date" => $printOrder->pr_date,
                ":proj_id"=>$projectTask->cost->proj_id
            ])->count();
            if($printOrderCount > 0){
                //If some print order had been given then we can not give more incentives
                return false;
            }            
        }
        
        return true;
         
    }
     

    /**
     * Gets query for [[EmployeeRolesJoins]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeRoleJoins()
    {
        return $this->hasMany(EmployeeRoleJoin::className(), ['emp_id' => 'emp_id']);
    }
    
    /**
     * Gets query for [[EmployeeRoles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeRoles()
    {
        return $this->hasMany(EmployeeRole::className(), ['role_id' => 'role_id'])
                ->via("employeeRoleJoins");
    }


    /**
     * Gets query for [[EmployeeTypeHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeTypeHistories()
    {
        return $this->hasMany(EmployeeTypeHistory::className(), ['emp_id' => 'emp_id']);
    }
    /**
     * Gets query for [[EmployeeTypeHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeTargetHistories()
    {
        return $this->hasMany(EmployeeTargetHistory::className(), ['emp_id' => 'emp_id']);
    }

    /**
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::className(), ['emp_id' => 'emp_id']);
    }

    /**
     * Gets query for [[Workdones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkdones()
    {
        return $this->hasMany(Workdone::className(), ['emp_id' => 'emp_id']);
    }
    
    public function getCurrentEmployeeType(){
        return $this->hasOne(EmployeeTypeHistory::className(), ['emp_id' => 'emp_id'])->where("tp_startdate <= :today",[
            ":today"=>date("Y-m-d")
        ])->orderBy("tp_startdate DESC, tp_id DESC");
    }
    public function getCurrentEmployeeTarget(){
        return $this->hasOne(EmployeeTargetHistory::className(), ['emp_id' => 'emp_id'])->where("tg_startdate <= :today",[
            ":today"=>date("Y-m-d")
        ])->orderBy("tg_startdate DESC, tg_id DESC");
    }
    
    public function beforeSave($insert) {
        parent::beforeSave($insert);
        if(trim($this->emp_password)!==""){
            $this->emp_password = md5($this->emp_password);
        }
        return true;
    }
}
