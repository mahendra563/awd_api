<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $user_id
 * @property string $user_name
 * @property string $user_password
 * @property string $auth_workentry  
 * @property int $auth_balance
 * @property ProjectTask[] $projectTasks
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        
            return [
                [['user_name', 'user_password'], 'required'], 
                [['user_name', 'user_password','auth_workentry'], 'string', 'max' => 255],
            ];
         
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'user_password' => 'Password',
            'auth_project' => 'Auth Project',
            'auth_balance' => 'Auth Balance',
            'auth_subadmin' => 'Auth Subadmin',
            'auth_workentry' => 'Auth Workentry',
           

        ];
    }

    /**
     * Gets query for [[ProjectTasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectTasks()
    {
        return $this->hasMany(ProjectTask::className(), ['user_id' => 'user_id']);
    }

    public function getAuthKey(): string {
        
    }

    public function getId() {
        
    }

    public function validateAuthKey($authKey): bool {
        
    }

    public static function findIdentity($id) {
       
    }

    public static function findIdentityByAccessToken($token, $type = null) {
        
    }

}