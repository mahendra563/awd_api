<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "employee_target_history".
 *
 * @property int $tg_id
 * @property int $tg_amount
 * @property string $tg_startdate
 * @property int $emp_id
 *
 * @property Employees $emp
 */
class EmployeeTargetHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_target_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tg_amount', 'tg_startdate', 'emp_id'], 'required'],
            [['tg_amount', 'emp_id'], 'integer'],
            [['tg_startdate'], 'safe'],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tg_id' => 'ID',
            'tg_amount' => 'Target Amount',
            'tg_startdate' => 'Start Date',
            'emp_id' => 'Employee',
        ];
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
