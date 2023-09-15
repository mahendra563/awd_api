<?php

namespace app\models;
use Yii;
use app\components\Helpers;

/**
 * This is the model class for table "project_incentives".
 *
 * @property int $inc_id
 * @property string $inc_date
 * @property float $inc_amount
 * @property int $ts_id
 * @property int $pr_id
 *
 * @property ProjectPrintorder $pr
 * @property ProjectTask $ts
 */
class ProjectIncentive extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_incentives';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inc_date', 'inc_amount', 'ts_id', 'pr_id'], 'required'],
            [['inc_date'], 'safe'],
            [['inc_amount'], 'number'],
            [['ts_id', 'pr_id'], 'integer'],
            [['pr_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectPrintOrder::className(), 'targetAttribute' => ['pr_id' => 'pr_id']],
            [['ts_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectTask::className(), 'targetAttribute' => ['ts_id' => 'ts_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'inc_id' => 'Inc ID',
            'inc_date' => 'Inc Date',
            'inc_amount' => 'Inc Amount',
            'ts_id' => 'Ts ID',
            'pr_id' => 'Pr ID',
        ];
    }

    /**
     * Gets query for [[Pr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectPrintOrder()
    {
        return $this->hasOne(ProjectPrintOrder::className(), ['pr_id' => 'pr_id']);
    }

    /**
     * Gets query for [[Ts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectTask()
    {
        return $this->hasOne(ProjectTask::className(), ['ts_id' => 'ts_id']);
    }

public static function getTillDate($emp_id,$date){
        $date = Helpers::i()->formatDate($date,"Y-m-t");
        return self::find()->where("ts_alotted_to = :emp_id AND inc_date <= :date",[":emp_id"=>$emp_id,":date"=>$date])        
            ->innerJoinWith("projectTask")->sum("inc_amount");
    }
    
    public static function getForMonth($emp_id,$date){
        $from = Helpers::i()->formatDate($date,"Y-m-01");
        $to = Helpers::i()->formatDate($date,"Y-m-t");
        
        return self::find()->where("ts_alotted_to = :emp_id AND inc_date <= :to AND inc_date >= :from",
            [":emp_id"=>$emp_id,":to"=>$to,":from"=>$from])->innerJoinWith("projectTask")->sum("inc_amount");        
    }
}
