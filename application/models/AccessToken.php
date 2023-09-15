<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "access_tokens".
 *
 * @property int $token_id
 * @property string $token_uid
 * @property string $token_expiry
 * @property int $user_id
 *
 * @property User $user
 */
class AccessToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token_uid', 'token_expiry', 'person_id','token_type','token_ip'], 'required'],
            [['token_expiry'], 'safe'],
            [['person_id'], 'integer'],
            [['token_uid'], 'string', 'max' => 255],            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'token_id' => 'Token ID',
            'token_uid' => 'Token Uid',
            'token_expiry' => 'Token Expiry',
            'person_id' => 'Person ID',
            'token_type' => 'Token Type',
            'token_ip' => 'Token IP'
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'person_id']);
    }
    
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'person_id']);
    }
}
