<?php

namespace app\models;

use app\components\Helpers;
use app\components\MediaManager;
use yii\web\UploadedFile;

/**
 * This is the model class for table "project_files".
 *
 * @property int $file_id
 * @property string $file_title
 * @property string $file_path
 * @property string $file_date
 * @property int $proj_id
 *
 * @property Projects $proj
 */
class ProjectFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'file_date', 'proj_id'], 'required'],
            [['file_date'], 'safe'],
            [['proj_id'], 'integer'],
            [['file_path','file_title'],'default','value'=>''],
            [['file_title', 'file_path'], 'string', 'max' => 255],
            [['proj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['proj_id' => 'proj_id']],
            
            [['file'],'file','extensions'=>['jpg','jpeg','png','gif','zip','txt','pdf','doc','docx',"xls","xlsx","ppt","pptx","csv"]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'File ID',
            'file_title' => 'Title',
            'file_path' => 'File Path',
            'file_date' => 'Date',
            'proj_id' => 'Project',
        ];
    }

    /**
     * Gets query for [[Proj]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['proj_id' => 'proj_id']);
    }
    
    
    public function beforeValidate() {
        parent::beforeValidate();
        $this->file = UploadedFile::getInstance($this, "file"); 
        if(!is_null($this->file) && trim($this->file_title) == ""){            
            $this->file_title = $this->file->baseName;
        }
        if(!is_null($this->file)){
            $this->file_date = date("Y-m-d H:i:s");
        }
        return true;
    }
    public function beforeSave($insert) {
        parent::beforeSave($insert);              
        $this->file_path = MediaManager::i()->upload($this->file, $this->file_path);        
        return true;
    }
    public function beforeDelete() {
        parent::beforeDelete();        
        MediaManager::i()->delete($this->file_path);
        return true;
    }
    private $_file;
    public function getFile(){
        return $this->_file;
    }
    public function setFile($value){
        $this->_file = $value;
    }
}
