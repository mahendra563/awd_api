<?php

namespace app\models;

use app\components\MediaManager;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "qb_files".
 *
 * @property int $file_id
 * @property string $file_title
 * @property string $file_path
 * @property string $file_uploaded_on
 * @property string $file_updated_on
 * @property int $emp_id
 *
 * @property Employees $emp
 * @property QbKeywordFileJoins[] $qbKeywordFileJoins
 */
class QbFile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qb_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_title', 'file_uploaded_on', 'file_updated_on', 'emp_id'], 'required'],
            [['file_path'], 'default', 'value'=>''],
            [['file_uploaded_on', 'file_updated_on'], 'safe'],
            [['emp_id'], 'integer'],
            [['file_title', 'file_path'], 'string', 'max' => 255],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
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
            'file_title' => 'File Title',
            'file_path' => 'File Path',
            'file_uploaded_on' => 'Uploaded On',
            'file_updated_on' => 'Updated On',
            'emp_id' => 'Uploaded By',
        ];
    }

    /**
     * Gets query for [[Emp]].
     *
     * @return ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'emp_id']);
    }

    /**
     * Gets query for [[QbKeywordFileJoins]].
     *
     * @return ActiveQuery
     */
    public function getQbKeywordFileJoins()
    {
        return $this->hasMany(QbKeywordFileJoin::className(), ['file_id' => 'file_id']);
    }
    
    public function getTags(){
        return $this->hasMany(QbKeyword::class, ["kw_id"=>"kw_id"])->via("qbKeywordFileJoins")->where(["kw_type"=>"Tag"]);
    }
    
    public function getState(){
        return $this->hasOne(QbKeyword::class, ["kw_id"=>"kw_id"])->via("qbKeywordFileJoins")->where(["kw_type"=>"State"]);
    }
    
    public function getType(){
        return $this->hasOne(QbKeyword::class, ["kw_id"=>"kw_id"])->via("qbKeywordFileJoins")->where(["kw_type"=>"Type"]);
    }
    
    private $_file;
    public function getFile(){
        return $this->_file;
    }
    public function setFile($value){
        $this->_file = $value;
    }
    public function beforeValidate() {
        parent::beforeValidate();
        $this->file = UploadedFile::getInstance($this, "file");
        if($this->isNewRecord){
            $this->file_uploaded_on = date("Y-m-d H:i:s");
        }
        $this->file_updated_on = date("Y-m-d H:i:s");
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
}
