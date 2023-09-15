<?php

namespace app\models;

use app\components\Helpers;

/**
 * This is the model class for table "old_work".
 *
 * @property int $work_id
 * @property string $work_date
 * @property string $work_type
 * @property string $work_amount
 * @property int|null $emp_id
 *
 * @property Employees $emp
 */
class OldWork extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'old_work';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['work_date', 'work_type', 'work_amount','emp_id'], 'required'],
            [['emp_id'], 'integer'],
            [['work_date', 'work_type', 'work_amount'], 'string', 'max' => 45],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'work_id' => 'Work ID',
            'work_date' => 'Work Date',
            'work_type' => 'Work Type',
            'work_amount' => 'Work Amount',
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
    
    public static function getTillDate($emp_id,$date){
        $date = Helpers::i()->formatDate($date,"Y-m-t");
        $sql = "select sum(work_amount) from ".self::tableName()." where emp_id =:emp_id AND work_date <= :date AND work_type='Incentive' ORDER BY work_date desc";
        $incentive = \Yii::$app->db->createCommand($sql,[":emp_id"=>$emp_id,":date"=>$date])->queryScalar();
        $sql = "select sum(work_amount) from ".self::tableName()." where emp_id =:emp_id AND work_date <= :date AND work_type='Project Work' ORDER BY work_date desc";
        $project_work = \Yii::$app->db->createCommand($sql,[":emp_id"=>$emp_id,":date"=>$date])->queryScalar();
        return ["incentive"=>(float)$incentive,"project_work"=>(float)$project_work];
    }
    
    public static function getForMonth($emp_id,$date){
        $from = Helpers::i()->formatDate($date,"Y-m-01");
        $to = Helpers::i()->formatDate($date,"Y-m-t");        
        
        $sql = "select SUM(work_amount) from ".self::tableName()."
where emp_id =:emp_id AND work_date <= :to AND work_date >= :from AND work_type = 'Incentive' ORDER BY work_date desc";        
        $incentive = \Yii::$app->db->createCommand($sql,[":emp_id"=>$emp_id,
            ":from"=>$from,":to"=>$to])->queryScalar();
        
        $sql = "select SUM(work_amount) from ".self::tableName()."
where emp_id =:emp_id AND work_date <= :to AND work_date >= :from AND work_type = 'Project Work' ORDER BY work_date desc";        
        $project_work = \Yii::$app->db->createCommand($sql,[":emp_id"=>$emp_id,
            ":from"=>$from,":to"=>$to])->queryScalar();
         
        return ["incentive"=>(float)$incentive,"project_work"=>(float)$project_work];
    }
}
