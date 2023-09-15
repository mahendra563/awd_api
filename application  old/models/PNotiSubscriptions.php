<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pnoti_subscriptions".
 *
 * @property int $sub_id
 * @property string $sub_data
 * @property int $emp_id
 *
 * @property Employees $emp
 */
class PNotiSubscriptions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pnoti_subscriptions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sub_data', 'emp_id', 'sub_endpoint'], 'required'],
            [['sub_data','sub_endpoint'], 'string'],
            [['emp_id'], 'integer'],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sub_id' => 'Sub ID',
            'sub_data' => 'Sub Data',
            'emp_id' => 'Emp ID',
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
