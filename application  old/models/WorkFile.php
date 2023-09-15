<?php

namespace app\models;

use app\components\MediaManager;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "work_files".
 *
 * @property int $f_id
 * @property string $f_title
 * @property string $f_path
 * @property string $f_uploaded_on
 * @property string $f_modified_on
 * @property int $emp_id
 *
 * @property WorkFileWorkJoins[] $workFileWorkJoins
 * @property Employees $emp
 */
class WorkFile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['f_title', 'f_path', 'emp_id'], 'required'],
            [['f_uploaded_on', 'f_modified_on'], 'safe'],
            [['f_uploaded_on', 'f_modified_on'],'default','value'=>date("Y-m-d H:i:s")],
            [['emp_id'], 'integer'],
            [['f_title', 'f_path'], 'string', 'max' => 255],
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
            'f_id' => 'ID',
            'f_title' => 'Title',
            'f_path' => 'Path',
            'f_uploaded_on' => 'Uploaded On',
            'f_modified_on' => 'Modified On',
            'emp_id' => 'Employee',
        ];
    }

    /**
     * Gets query for [[WorkFileWorkJoins]].
     *
     * @return ActiveQuery
     */
    public function getWorkEntryFileJoin()
    {
        return $this->hasMany(WorkEntryFileJoin::className(), ['f_id' => 'f_id']);
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
   
    
    public function beforeValidate() {
        parent::beforeValidate();
        $this->file = UploadedFile::getInstance($this, "file"); 
         
         
        
        return true;
    }
    public function beforeSave($insert) {
        parent::beforeSave($insert);  
        if($insert){
            $this->f_uploaded_on = date("Y-m-d H:i:s");            
        }
        $this->f_modified_on = date("Y-m-d H:i:s");
        $this->f_path = MediaManager::i()->upload($this->file, $this->f_path);        
        
        return true;
    }
    public function beforeDelete() {
        parent::beforeDelete();        
        MediaManager::i()->delete($this->f_path);
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
