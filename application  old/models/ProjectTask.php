<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project_tasks".
 *
 * @property int $ts_id
 * @property string $ts_description
 * @property float $ts_rate
 * @property float $ts_qty
 * @property float $ts_amount
 * @property string $ts_estimated_date
 * @property string $ts_completion_date
 * @property int $ts_alotted_by
 * @property int $ts_alotted_to
 * @property string $ts_status
 * @property int $cost_id
 * @property int $task_id
 * @property int $user_id
 *
 * @property ProjectIncentive[] $projectIncentives
 * @property User $user
 * @property Task $task
 * @property ProjectCost $cost
 */
class ProjectTask extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ts_rate', 'ts_qty', 'ts_amount', 'ts_estimated_date', 'ts_completion_date', 'ts_alotted_by', 'ts_alotted_to', 'ts_status', 'cost_id', 'task_id', 'user_id'], 'required'],
            [['ts_description'], 'string'],
            [['ts_description'], 'validateTask'],
            [['ts_amount'], 'validateAmount'],
            [['ts_description'],'default','value'=>''],
            [['ts_rate', 'ts_qty', 'ts_amount'], 'number'],
            [['ts_estimated_date', 'ts_completion_date'], 'safe'],
            [['ts_alotted_by', 'ts_alotted_to', 'cost_id', 'task_id', 'user_id'], 'integer'],
            [['ts_status'], 'string', 'max' => 45],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::className(), 'targetAttribute' => ['task_id' => 'task_id']],
            [['cost_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectCost::className(), 'targetAttribute' => ['cost_id' => 'cost_id']],
        ];
    }

    public function validateTask(){
        //if today is more than the project proj_lasttaskdate
        if($this->isNewRecord){
        $today = strtotime(date("d M Y"));
         
        $lasttaskdate = strtotime($this->cost->project->proj_lasttaskdate);
        if($today > $lasttaskdate){
            $this->addError("ts_description","You can not add further tasks");
        }
        }
    }
    public function validateAmount(){        
        $total = $this->cost->project->proj_cost;
        $proj_id = $this->cost->proj_id;
	if($this->isNewRecord){
        	$used  = self::find()
                        ->innerJoinWith("cost")
                        ->where(["proj_id"=>$proj_id])->sum("ts_amount");
	} else {
		$used  = self::find()
                        ->innerJoinWith("cost")
                        ->where("proj_id = :proj_id AND ts_id <> :ts_id", 
[":proj_id"=>$proj_id,":ts_id"=>$this->ts_id])->sum("ts_amount");
	}

        $available = $total - $used;

        if($this->ts_amount > $available){
            $this->addError("ts_amount","Task amount is exceeding the budget of Project");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ts_id' => 'Ts ID',
            'ts_description' => 'Description',
            'ts_rate' => 'Rate',
            'ts_qty' => 'Qty',
            'ts_amount' => 'Amount',
            'ts_estimated_date' => 'Estimated Date',
            'ts_completion_date' => 'Completion Date',
            'ts_alotted_by' => 'Alotted By',
            'ts_alotted_to' => 'Alotted To',
            'ts_status' => 'Status',
            'cost_id' => 'Cost',
            'task_id' => 'Task',
            'user_id' => 'User',
        ];
    }

    /**
     * Gets query for [[ProjectIncentives]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIncentives()
    {
        return $this->hasMany(ProjectIncentive::className(), ['ts_id' => 'ts_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    
    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::className(), ['task_id' => 'task_id']);
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

    public function getAlottedBy()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'ts_alotted_by']);
    }

    public function getAlottedTo()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'ts_alotted_to']);
    }
}
