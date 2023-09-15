<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project_printorders".
 *
 * @property int $pr_id
 * @property string $pr_description
 * @property string $pr_date
 * @property int $proj_id
 *
 * @property ProjectIncentive[] $projectIncentives
 * @property Project $proj
 */
class ProjectPrintOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_printorders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pr_description', 'pr_date', 'proj_id'], 'required'],
            [['pr_date'], 'safe'],
            [['proj_id'], 'integer'],
            [['pr_description'],'checkCompletionOfTasks'],
            [['pr_description'], 'string', 'max' => 255],
            [['proj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['proj_id' => 'proj_id']],
        ];
    }

    public function checkCompletionOfTasks(){
        $models = ProjectTask::find()
                ->innerJoinWith(["cost"])
                ->where(["proj_id"=>$this->proj_id])
                ->all();
        foreach($models as $model){
            if($model->ts_status == "Pending"){
                $this->addError("pr_description","Some tasks are Pending on this project");
            }
            return false;
        }
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'pr_id' => 'ID',
            'pr_description' => 'Description',
            'pr_date' => 'Date',
            'proj_id' => 'Project ID',
        ];
    }

    /**
     * Gets query for [[ProjectIncentives]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIncentives()
    {
        return $this->hasMany(ProjectIncentive::className(), ['pr_id' => 'pr_id']);
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
}
