<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project_managers".
 *
 * @property int $join_id
 * @property int $proj_id
 * @property int $emp_id

 *
 * @property Projects $proj
 * @property Employees $emp
 */
class ProjectManager extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_managers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['proj_id', 'emp_id' ], 'required'],
            [['proj_id', 'emp_id'], 'integer'],
            [['proj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['proj_id' => 'proj_id']],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'join_id' => 'Join ID',
            'proj_id' => 'Proj ID',
            'emp_id' => 'Emp ID',
           
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
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'emp_id']);
    }
    
}