<?php

namespace app\models;

use app\components\MediaManager;
use yii\web\UploadedFile;

/**
 * This is the model class for table "projects".
 *
 * @property int $proj_id
 * @property string $proj_title
 * @property string $proj_creation_date
 * @property string $proj_completion_date
 * @property string $proj_lasttaskdate
 *
 * @property ProjectCost[] $projectCosts
 * @property ProjectPrintorder[] $projectPrintorders
 */
class Project extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['proj_title', 'proj_type','proj_type2', 'proj_creation_date', 'proj_completion_date', 'proj_lasttaskdate','proj_cost'], 'required'],
            [['proj_creation_date', 'proj_completion_date', 'proj_lasttaskdate'], 'safe'],
            [['proj_title'], 'string', 'max' => 255],
            [['proj_file'], 'default', 'value' => ''],
            [['proj_cost'], 'number'],            
            [['file'],'file','extensions'=>['jpg','jpeg','png','gif','zip','txt','pdf','doc','docx',"xls","xlsx","ppt","pptx","csv"]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'proj_id' => 'Proj ID',
            'proj_title' => 'Project Title',
            'proj_creation_date' => 'Creation Date',
            'proj_completion_date' => 'Completion Date',
            'proj_lasttaskdate' => 'Last Task Date',
            'proj_cost' => 'Project Cost',
            'proj_type2' => 'Type 2',
        ];
    }

    /**
     * Gets query for [[ProjectCosts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectCosts()
    {
        return $this->hasMany(ProjectCost::className(), ['proj_id' => 'proj_id']);
    }

    /**
     * Gets query for [[ProjectPrintorders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectPrintorders()
    {
        return $this->hasMany(ProjectPrintorder::className(), ['proj_id' => 'proj_id']);
    }
    
    public function getManagerJoins()
    {
        return $this->hasMany(ProjectManager::className(), ['proj_id' => 'proj_id']);
    }
    
    public function getWorkEntries()
    {
        return $this->hasMany(WorkEntry::className(), ['proj_id' => 'proj_id']);
    }
    public function beforeValidate() {
        parent::beforeValidate();
        $this->file = UploadedFile::getInstance($this, "file"); 
        /*\app\components\TempData::i()->save("modals.Project.proj_creation_date", $this->proj_creation_date);
        \app\components\TempData::i()->save("modals.Project.proj_completion_date", $this->proj_completion_date);
        \app\components\TempData::i()->save("modals.Project.proj_lasttaskdate", $this->proj_lasttaskdate);
        \app\components\TempData::i()->save("modals.Project.proj_lasttaskdate", $this->proj_cost);*/
        $oldModel = null;
        if(!$this->isNewRecord){
            $oldModel = self::findOne($this->proj_id);        
        }
        \app\components\TempData::i()->save("modals.Project", $oldModel);
        
        return true;
    }
    public function beforeSave($insert) {
        parent::beforeSave($insert);              
        $this->proj_file = MediaManager::i()->upload($this->file, $this->proj_file);        
        
        return true;
    }
    public function beforeDelete() {
        parent::beforeDelete();        
        MediaManager::i()->delete($this->proj_file);
        return true;
    }
    
    const TYPE_DEFAULT = "Default";
    const TYPE_QBANK = "Question Bank";
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        $oldModel = \app\components\TempData::i()->get("modals.Project");
        if(!is_null($oldModel) && $oldModel !== false){
            $user = \app\components\TempData::i()->get("loggedInUser");
            $msg = [];
            
            foreach($this->attributes as $name=>$value){                 
                if($oldModel->$name!=$value){
                    $msg[] = $this->getAttributeLabel($name)." changed from \"".$oldModel->$name."\" to \"".$value."\"";
                }                
            }
            ProjectHistory::addRecord($this->proj_id,"Project Updated", implode("\n", $msg),$user->user_id);
        }
        elseif($oldModel === false && !is_null($oldModel)){
            $oldModel = null;
            if(!$this->isNewRecord){
                $oldModel = self::findOne($this->proj_id);  
            }
            $user = \app\components\TempData::i()->get("loggedInUser");
            
            $msg = [];
            $msg[] = "Project Created Successfully";
            
            
            ProjectHistory::addRecord($this->proj_id,"Project Created", implode("\n", $msg),$user->user_id);

        }
        
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
