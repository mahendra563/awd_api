<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "leave_requests".
 *
 * @property int $rq_id
 * @property string $rq_dates
 
 * @property string $rq_subject
 * @property string $rq_message
 * @property string $rq_status
 * @property string $rq_created_on
 * @property string $rq_updated_on
 * @property int $emp_id
 *
 * @property Employees $emp
 */
class LeaveRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_requests';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rq_dates', 'rq_subject', 'rq_message', 'rq_status', 'emp_id'], 'required'],
            [['rq_dates', 'rq_message'], 'string'],
            [['rq_created_on', 'rq_updated_on'], 'safe'],
            [['emp_id'], 'integer'],
            [[  'rq_status'], 'string', 'max' => 45],
            [['rq_subject'], 'string', 'max' => 255],
            [['rq_created_on', 'rq_updated_on'],'default','value'=>date("Y-m-d H:i:s")],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'rq_id' => 'Request ID',
            'rq_dates' => 'Dates',
 
            'rq_subject' => 'Subject',
            'rq_message' => 'Message',
            'rq_status' => 'Status',
            'rq_created_on' => 'Created On',
            'rq_updated_on' => 'Updated On',
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
    
    
    public function beforeValidate() {
        parent::beforeValidate();
        if(!$this->isNewRecord){
            $model = self::findOne($this->rq_id);
            \app\components\TempData::i()->save("modals.LeaveRequest", $model);
        }
        
        
        return true;
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if($insert){
            
        } else {
            $oldModel = \app\components\TempData::i()->get("modals.LeaveRequest");
            if($oldModel->rq_status !== $this->rq_status){
                $message = " Your leave request #$this->rq_id has been ".$this->rq_status;
                \app\models\PNotiEntry::add($message, "#", $this->emp_id);
            }
            //Check if status has changed
        }
        return true;
    }
}
