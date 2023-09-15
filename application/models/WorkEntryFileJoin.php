<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "work_entry_file_joins".
 *
 * @property int $join_id
 * @property int $w_id
 * @property int $f_id
 *
 * @property WorkFiles $f
 * @property WorkEntries $w
 */
class WorkEntryFileJoin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work_entry_file_joins';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['w_id', 'f_id'], 'required'],
            [['w_id', 'f_id'], 'integer'],
            [['f_id'], 'exist', 'skipOnError' => true, 'targetClass' => WorkFile::className(), 'targetAttribute' => ['f_id' => 'f_id']],
            [['w_id'], 'exist', 'skipOnError' => true, 'targetClass' => WorkEntry::className(), 'targetAttribute' => ['w_id' => 'w_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'join_id' => 'Join ID',
            'w_id' => 'W ID',
            'f_id' => 'F ID',
        ];
    }

    /**
     * Gets query for [[F]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkFile()
    {
        return $this->hasOne(WorkFile::className(), ['f_id' => 'f_id']);
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
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
        $user = \app\components\TempData::i()->get("loggedInUser");
        $type = \app\components\TempData::i()->get("loggedInUserType");
        if($user===false){
            return true;
        }
        $msg = $this->workFile->f_title."(".$this->workFile->f_path.") Uploaded By ".$this->workFile->employee->emp_fullname." Linked";

        \app\models\WorkHistory::addRecord($this->w_id,"File linked ".$this->workFile->f_title."(".$this->workFile->f_path.")", 
                $msg, 
                $type, $type == "User" ? $user->user_id : $user->emp_id);
        
        return true;        
    }
    
    public function beforeDelete() {
        parent::beforeDelete();
        
        $user = \app\components\TempData::i()->get("loggedInUser");
        $type = \app\components\TempData::i()->get("loggedInUserType");
        if($user===false){
            return true;
        }
        $msg = $this->workFile->f_title."(".$this->workFile->f_path.") Uploaded By ".$this->workFile->employee->emp_fullname." Unlinked";

        \app\models\WorkHistory::addRecord($this->w_id,"File Unlinked ".$this->workFile->f_title."(".$this->workFile->f_path.")", 
                $msg, 
                $type, $type == "User" ? $user->user_id : $user->emp_id);
        
        return true;
    }
}
