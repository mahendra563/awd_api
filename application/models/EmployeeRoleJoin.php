<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "employee_roles_joins".
 *
 * @property int $join_id
 * @property int $emp_id
 * @property int $role_id
 *
 * @property Employee $emp
 * @property EmployeeRole $role
 */
class EmployeeRoleJoin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_roles_joins';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id', 'role_id'], 'required'],
            [['emp_id', 'role_id'], 'integer'],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => EmployeeRole::className(), 'targetAttribute' => ['role_id' => 'role_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'join_id' => 'Join ID',
            'emp_id' => 'Emp ID',
            'role_id' => 'Role ID',
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

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeRole()
    {
        return $this->hasOne(EmployeeRole::className(), ['role_id' => 'role_id']);
    }
}
