<?php

namespace app\models;
use Yii;
/**
 * This is the model class for table "work_entries".
 *
 * @property int $w_id
 * @property string $w_description
 * @property float $w_rate
 * @property float $w_qty
 * @property float $w_amount
 * @property string $w_estimated_date
 * @property string $w_completion_date
 * @property int $w_alotted_by
 * @property int $w_user_type
 * @property int $w_alotted_to
 * @property string $w_lastupdated_on
 * @property string $w_status
 * @property int $task_id
 * @property int $cost_id
 * @property int $proj_id
 *
 * @property Authentic $auth
 * @property Tasks $task
 * @property Employees $wAlottedBy
 * @property Employees $wAlottedTo
 * @property ProjectCosts $cost
 * @property WorkFileWorkJoins[] $workFileWorkJoins
 * @property WorkHistory[] $workHistories
 * @property ProjectManagers $wUserType
 */
class WorkEntry extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work_entries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['w_description', 'w_rate', 'w_qty', 'w_amount', 'w_estimated_date', 'w_completion_date', 'w_alotted_by', 'w_alotted_to', 'w_lastupdated_on', 'task_id', 'cost_id'], 'required'],
            [['w_status'],'default','value'=>''],
            [['w_qty'],'enforceTaskNatureLimits'],
            [['w_description'], 'string'],
            [['w_rate', 'w_qty', 'w_amount'], 'number'],
            [['w_estimated_date', 'w_completion_date', 'w_lastupdated_on'], 'safe'],
            [['w_alotted_by', 'w_alotted_to', 'task_id', 'cost_id', 'proj_id' ,'w_user_type'], 'integer'],
            [['w_status'], 'string', 'max' => 45],   
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::className(), 'targetAttribute' => ['task_id' => 'task_id']],
            [['w_alotted_by'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['w_alotted_by' => 'emp_id']],
            [['w_user_type'],'exist', 'skipOnError' => true, 'targetClass' => ProjectManager::className(), 'targetAttribute' => ['w_user_type' => 'join_id']],
            [['w_alotted_to'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['w_alotted_to' => 'emp_id']],
            [['cost_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectCost::className(), 'targetAttribute' => ['cost_id' => 'cost_id']],
            [['proj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['proj_id' => 'proj_id']],

        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'w_id' => 'W ID',
            'w_description' => 'Description',
            'w_rate' => 'Rate',
            'w_qty' => 'Qty',
            'w_amount' => 'Amount',
            'w_estimated_date' => 'Estimated Date',
            'w_completion_date' => 'Completion Date',
            'w_alotted_by' => 'Alotted By',
            'w_user_type' => 'User Type',
            'w_alotted_to' => 'Alotted To',
            'w_lastupdated_on' => 'Last Updated On',
            'w_status' => 'Status',
            'task_id' => 'Task Nature',
            'cost_id' => 'Cost',
            'proj_id' => 'Project',
        ];
    }
    
    public function enforceTaskNatureLimits(){
       
        // $auth_user = Authentic::find("auth");
        // $auth_user = $data->auth;
        $type = \app\components\TempData::i()->get("loggedInUserType");
        if($this->w_status == "Approved")
        {
            $model = Task::findOne(["task_id"=>$this->task_id]);
        if(is_null($model)){
            return $this->addError("task_id","Invalid Task Nature");
        }
        if($model->task_limit > 0){
            if($this->w_qty > $model->task_limit){
                return $this->addError("w_qty","Beyond limit range for this type of task.");
            }
        }     

        }
       else if($type == "User" )
        {
            $model = Task::findOne(["task_id"=>$this->task_id]);
        if(is_null($model)){
            return $this->addError("task_id","Invalid Task Nature");
        }
        if($model->task_limit > 0){
            if($this->w_qty > $model->task_limit){
                return $this->addError("w_qty","Beyond limit range for this type of task.");
            }
        }        
        }
      else{
        $auth_user = 0;
    //    WorkHistory::addRecord($this->w_id,"Work Entry Updated", implode("\n", $msg),$type, $type == "User" ? $user->user_id : $user->emp_id);
        if( $auth_user == 1 ){
            return $this->addError("no_save"," AWD पर कार्य चढाने की  इस माह की तिथि समाप्त हो गयी है। अतः अब कार्य अगले माह में ही चढ़ा पाएंगे .......");
        }
     else{
       
        $model = Task::findOne(["task_id"=>$this->task_id]);
        if(is_null($model)){
            return $this->addError("task_id","Invalid Task Nature");
        }
        if($model->task_limit > 0){
            if($this->w_qty > $model->task_limit){
                return $this->addError("w_qty","Beyond limit range for this type of task.");
            }
        }        
    }
}
    }
    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskNature()
    {
        return $this->hasOne(Task::className(), ['task_id' => 'task_id']);
    }

    /**
     * Gets query for [[WAlottedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlottedBy()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'w_alotted_by']);
    }
    /** 
     * Gets query for [[WUserType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserType()
    {
        return $this->hasOne(ProjectManager::className(), ['join_id' => 'w_user_type']);
    }

    /** 
     * Gets query for [[WAlottedTo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlottedTo()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'w_alotted_to']);
    }

    /**
     * Gets query for [[Cost]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCost()
    {
        return $this->hasOne(ProjectCost::className(), ['cost_id' => 'cost_id']);
    }
    
    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['proj_id' => 'proj_id']);
    }

    /**
     * Gets query for [[WorkFileWorkJoins]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkFileWorkJoins()
    {
        return $this->hasMany(WorkFileWorkJoin::className(), ['w_id' => 'w_id']);
    }

    /**
     * Gets query for [[WorkHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkHistory()
    {
        return $this->hasMany(WorkHistory::className(), ['w_id' => 'w_id']);
    }
    
    public function beforeValidate() {
      
        parent::beforeValidate();
        $this->w_lastupdated_on = date("Y-m-d H:i:s");
        $this->w_estimated_date = \app\components\Helpers::i()->formatDate($this->w_estimated_date,"Y-m-d");
        $this->w_completion_date = \app\components\Helpers::i()->formatDate($this->w_completion_date,"Y-m-d");
        $this->w_amount  = $this->w_rate * $this->w_qty;
        
        $oldModel = null;
        if(!$this->isNewRecord){
            $oldModel = self::findOne($this->w_id);        
        }
        
        \app\components\TempData::i()->save("modals.WorkEntry", $oldModel);
        
        return true;
    }
    
    const STATUS_PENDING = "Pending";
    const STATUS_TRANFERRED = "Transferred";
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
        $oldModel = \app\components\TempData::i()->get("modals.WorkEntry");
        
        if($oldModel !== false && !is_null($oldModel)){
            $user = \app\components\TempData::i()->get("loggedInUser");
            $type = \app\components\TempData::i()->get("loggedInUserType");
            
            $msg = [];
            
            foreach($this->attributes as $name=>$value){
                if($name!=="w_lastupdated_on"){
                    if($oldModel->$name!=$value){
                        $msg[] = $this->getAttributeLabel($name)."Changed from \"".$oldModel->$name."\" to \"".$value."\"";
                    }
                }
            }

            WorkHistory::addRecord($this->w_id,"Work Entry Updated", implode("\n", $msg),$type, $type == "User" ? $user->user_id : $user->emp_id);
        }
        elseif($oldModel === false && !is_null($oldModel)){
            $oldModel = null;
            if(!$this->isNewRecord){
                $oldModel = self::findOne($this->w_id);  
            }
            $user = \app\components\TempData::i()->get("loggedInUser");
            $type = \app\components\TempData::i()->get("loggedInUserType");
            
            $msg = [];
            $msg[] = "Work Entry Created Successfully";

            WorkHistory::addRecord($this->w_id,"Work Entry Created", implode("\n", $msg),$type, $type == "User" ? $user->user_id : $user->emp_id);
        }
        return true;
        if($insert){
            $message = $this->alottedBy->emp_fullname." has alotted a Work: ".$this->w_description." Project: ".$this->project->proj_title;
            \app\models\PNotiEntry::add($message, "#", $this->w_alotted_to);
        }
        

    }
}
    