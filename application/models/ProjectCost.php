<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project_costs".
 *
 * @property int $cost_id
 * @property string $cost_title
 * @property float $cost_amount
 * @property float $cost_incentive_rate
 * @property int $proj_id
 *
 * @property Project $proj
 * @property ProjectTask[] $projectTasks
 */
class ProjectCost extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_costs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cost_title', 'cost_amount', 'cost_incentive_rate', 'proj_id'], 'required'],
            [['cost_amount', 'cost_incentive_rate'], 'number'],
            [['proj_id'], 'integer'],
            [['cost_amount'], 'validateAmount'],
            [['cost_title'], 'string', 'max' => 255],
            [['proj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['proj_id' => 'proj_id']],
        ];
    }

    public function validateAmount($attr){
        
        if($this->isNewRecord){
            $filled = self::find()->where(["proj_id"=>$this->proj_id])->sum("cost_amount");            
        } else {
            $filled = self::find()->where("proj_id = :proj_id AND cost_id <> :cost_id",[":cost_id"=>$this->cost_id,":proj_id"=>$this->proj_id])->sum("cost_amount");
        }
        if( ($filled+$this->cost_amount) > 100 ){
            $this->addError("cost_amount","Exceeded 100%");
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cost_id' => 'Cost ID',
            'cost_title' => 'Title',
            'cost_amount' => 'Amount',
            'cost_incentive_rate' => 'Incentive Rate',
            'proj_id' => 'Proj ID',
        ];
    }

    /**
     * Gets query for [[Proj]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['proj_id' => 'proj_id']);
    }

    /**
     * Gets query for [[ProjectTasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectTasks()
    {
        return $this->hasMany(ProjectTask::className(), ['cost_id' => 'cost_id']);
    }
}
