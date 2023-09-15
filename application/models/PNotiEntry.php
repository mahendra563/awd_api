<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pnoti_entries".
 *
 * @property int $ent_id
 * @property string $ent_title
 * @property string $ent_url
 * @property string $ent_date
 * @property int $ent_read
 * @property int $emp_id
 *
 * @property Employees $emp
 */
class PNotiEntry extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pnoti_entries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ent_title', 'ent_url', 'ent_date', 'ent_read', 'emp_id'], 'required'],
            [['ent_date'], 'safe'],
            [['ent_read', 'emp_id'], 'integer'],
            [['ent_title', 'ent_url'], 'string', 'max' => 255],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ent_id' => 'Ent ID',
            'ent_title' => 'Ent Title',
            'ent_url' => 'Ent Url',
            'ent_date' => 'Ent Date',
            'ent_read' => 'Ent Read',
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
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if($insert){
            \app\components\PushNotification::i()->sendFor($this->emp_id);
        }
        return true;
    }
    
    public static function add($title,$url,$emp_id){
        $model = new self();
        $model->ent_title = $title;
        $model->ent_url = \Yii::$app->params["baseUrl"].$url;
        $model->ent_date = date("Y-m-d H:i:s");
        $model->ent_read = 0;
        $model->ent_sent = 0;
        $model->emp_id = $emp_id;
        if($model->validate()){
            $model->save();
        }
    }
}
