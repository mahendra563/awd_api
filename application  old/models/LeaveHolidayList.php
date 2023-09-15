<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "leave_holidaylist".
 *
 * @property int $dt_id
 * @property string $dt_date
 * @property string $dt_title
 */
class LeaveHolidayList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_holidaylist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dt_date', 'dt_title'], 'required'],
            [['dt_date'], 'safe'],
            [['dt_title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'dt_id' => 'Dt ID',
            'dt_date' => 'Dt Date',
            'dt_title' => 'Dt Title',
        ];
    }
}
