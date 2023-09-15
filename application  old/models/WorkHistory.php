<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "work_history".
 *
 * @property int $his_id
 * @property string $his_date
 * @property string $his_title
 * @property string $his_description
 * @property string $his_user_type
 * @property int $user_id
 * @property int $w_id
 *
 * @property Employee $employee
 * @property User $user
 * @property WorkEntries $w
 */
class WorkHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['his_date', 'his_title', 'his_description', 'his_user_type','user_id', 'w_id'], 'required'],
            [['his_date'], 'safe'],
            [['his_description'], 'string'],
            [['user_id', 'w_id'], 'integer'],
            [['his_title'], 'string', 'max' => 255],
            
            [['w_id'], 'exist', 'skipOnError' => true, 'targetClass' => WorkEntry::className(), 'targetAttribute' => ['w_id' => 'w_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'his_id' => 'ID',
            'his_date' => 'Date',
            'his_title' => 'Title',
            'his_description' => 'Description',
            'his_type' => 'Type',            
            'user_id' => 'User/Employee',
            'w_id' => 'W ID',
        ];
    }

    /**
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'user_id']);
    }
    
    /**
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * Gets query for [[W]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkEntry()
    {
        return $this->hasOne(WorkEntry::className(), ['w_id' => 'w_id']);
    }
    
    public static function addRecord($w_id,$title,$description,$user_type,$user_id=null){
        $model = new self();
        $model->his_date = date("Y-m-d H:i:s");
        $model->his_title =  $title;
        $model->his_description = $description;    
        $model->his_user_type = $user_type;
        $model->user_id = $user_id;        
        $model->w_id = $w_id;
        if($model->validate()){
            $model->save();
            return true;
        }
        return false;
    }
}
