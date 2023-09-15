<?php

namespace app\models;

use app\components\Helpers;
use Yii;

/**
 * This is the model class for table "payments".
 *
 * @property int $pmt_id
 * @property string $pmt_date
 * @property float $pmt_amount
 * @property int $emp_id
 *
 * @property Employee $emp
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pmt_date', 'pmt_month',   'pmt_amount', 'emp_id', 'pmt_description','pmt_mode'], 'required'],
            [['pmt_date'], 'safe'],
            [['pmt_amount'], 'number'],
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
            'pmt_id' => 'Pmt ID',
            'pmt_date' => 'Date',
            'pmt_amount' => 'Amount',
            'pmt_month' => 'Month',
      
            'pmt_mode'=>"Mode",
            "pmt_description"=>"Description",
            'emp_id' => 'Emp ID',
        ];
    }

    /**
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmp()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'emp_id']);
    }
    
    public static function getTillDate($emp_id,$date){
        $date = Helpers::i()->formatDate($date,"Y-m-t");
        return self::find()->where("emp_id = :emp_id AND pmt_month <= :date",[":emp_id"=>$emp_id,":date"=>$date])
        ->sum("pmt_amount");
    }
    
    public static function getForMonth($emp_id,$date){
        $date = Helpers::i()->formatDate($date,"Y-m-t"); 
        return self::find()->where("emp_id = :emp_id AND pmt_month = :date",
            [":emp_id"=>$emp_id,":date"=>$date])->sum("pmt_amount");
    }
}
