<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project_history".
 *
 * @property int $his_id
 * @property string $his_date
 * @property string $his_title
 * @property string $his_description
 * @property int $user_id
 *
 * @property Users $user
 */
class ProjectHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['his_date', 'his_title', 'his_description', 'user_id','proj_id'], 'required'],
            [['his_date'], 'safe'],
            [['his_description'], 'string'],
            [['user_id'], 'integer'],
            [['his_title'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['proj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['proj_id' => 'proj_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'his_id' => 'His ID',
            'his_date' => 'His Date',
            'his_title' => 'His Title',
            'his_description' => 'His Description',
            'user_id' => 'User ID',
            'proj_id' => 'Project',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
    
    public static function addRecord($proj_id,$title,$description,$user_id=null){
        $model = new self();
        $model->his_date = date("Y-m-d H:i:s");
        $model->his_title =  $title;
        $model->his_description = $description;
      
            $model->user_id = $user_id;
      
        $model->proj_id = $proj_id;
        if($model->validate()){
            $model->save();
            return true;
        }
        return false;
    }
    
}