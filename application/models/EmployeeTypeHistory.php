<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "employee_type_history".
 *
 * @property int $tp_id
 * @property string $tp_type
 * @property string $tp_startdate
 * @property string $tp_enddate
 * @property int $emp_id
 *
 * @property Employee $emp
 */
class EmployeeTypeHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_type_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tp_type', 'tp_startdate', 'emp_id'], 'required'],
            [['tp_startdate'], 'safe'],
            [['emp_id'], 'integer'],
            [['tp_type'], 'string', 'max' => 45],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tp_id' => 'Type ID',
            'tp_type' => 'Employee Type',
            'tp_startdate' => 'Start Date',            
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
