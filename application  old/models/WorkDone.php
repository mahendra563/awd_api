<?php

namespace app\models;

use app\components\Helpers;
use Yii;

/**
 * This is the model class for table "workdone".
 *
 * @property int $wd_id
 * @property int $wd_date
 * @property float $wd_amount
 * @property int $emp_id
 *
 * @property Employee $emp
 */
class WorkDone extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workdone';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['wd_date', 'wd_amount', 'emp_id'], 'required'],
            [[ 'emp_id'], 'integer'],
            [['wd_amount'], 'number'],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'wd_id' => 'ID',
            'wd_date' => 'Date',            
            'wd_amount' => 'Amount',
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
        $sql = "select sum(greatest(0,wd_amount)) as work_done from ".self::tableName()." 
where emp_id =:emp_id AND wd_date <= :date ORDER BY wd_date desc";
        return \Yii::$app->db->createCommand($sql,[":emp_id"=>$emp_id,":date"=>$date])->queryScalar();
    }
    
    public static function getForMonth($emp_id,$date){
        $from = Helpers::i()->formatDate($date,"Y-m-01");
        $to = Helpers::i()->formatDate($date,"Y-m-t");
        
        $sql = "select SUM(wd_amount) from ".self::tableName()."
where emp_id =:emp_id AND wd_date <= :to AND wd_date >= :from ORDER BY wd_date desc";
        $workdone = \Yii::$app->db->createCommand($sql,[":emp_id"=>$emp_id,
            ":from"=>$from,":to"=>$to])->queryScalar();
        
      /*  if($workdone <= 0){
            return 0;
        } */
        
        return $workdone;
    }
    
    public static function updateForDate($emp_id,$date){
        $empModel = Employee::findOne($emp_id);
        if(is_null($empModel)){
            return false;   
        }
        $from = $date;
        $to = date("Y-m-t",strtotime($date));
        
        $amount = ProjectTask::find()->where("ts_alotted_to = :emp_id AND ts_completion_date <= :to AND ts_completion_date >= :from AND ts_status = :status",
            [":emp_id"=>$emp_id,":from"=>$from,":to"=>$to,":status"=>"Completed"])->sum("ts_amount");
       
        $oldWork = \app\models\OldWork::getForMonth($emp_id, $date);
        
        $target = $empModel->getTargetOnDate($to);
        
        $workDone = $oldWork["project_work"] + $amount - $target;
       
        //$wdModel = self::findOne(["emp_id"=>$emp_id,"wd_date"=>$date]);
        self::deleteAll(["emp_id"=>$emp_id, "wd_date"=>$date]);
        //if(is_null($wdModel)){
            $wdModel = new WorkDone();
            $wdModel->wd_date = $date;
            $wdModel->emp_id = $emp_id;
        //}
        $wdModel->wd_amount = $workDone;
       
        if($wdModel->validate()){
            $wdModel->save();
            return $wdModel;
        }  
        
        return false;       
    }
}
