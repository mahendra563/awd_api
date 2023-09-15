<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "employee_roles".
 *
 * @property int $role_id
 * @property string $role_title
 *
 * @property EmployeeRolesJoin[] $employeeRolesJoins
 */
class EmployeeRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_roles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_title'], 'required'],
            [['role_title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'role_id' => 'Role ID',
            'role_title' => 'Role Title',
        ];
    }

    /**
     * Gets query for [[EmployeeRolesJoins]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeRoleJoins()
    {
        return $this->hasMany(EmployeeRoleJoin::className(), ['role_id' => 'role_id']);
    }
}
