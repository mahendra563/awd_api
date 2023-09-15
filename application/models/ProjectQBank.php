<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project_qbank".
 *
 * @property int $q_id
 * @property string $q_agency_name
 * @property string $q_exam_name
 * @property string $q_admit_card_date
 * @property string $q_exam_date
 * @property string $q_exam_mode
 * @property int $q_paper_available
 * @property int $q_exam_shifts
 * @property string $q_anskey_start_date
 * @property string $q_anskey_end_date
 * @property string $q_anskey_type
 * @property int $q_anskey_pre_paper
 * @property int $q_anskey_final_paper
 * @property string $q_anskey_last_date
 * @property int $q_paper_downloaded_pre
 * @property int $q_paper_received_final
 * @property int $proj_id
 *
 * @property Projects $proj
 */
class ProjectQBank extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_qbank';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ///[['q_id', 'q_agency_name', 'q_exam_name', 'q_admit_card_date', 'q_exam_date', 'q_exam_mode', 'q_paper_available', 'q_exam_shifts', 'q_anskey_start_date', 'q_anskey_end_date', 'q_anskey_type', 'q_anskey_pre_paper', 'q_anskey_final_paper', 'q_anskey_last_date', 'q_paper_downloaded_pre', 'q_paper_received_final', 'proj_id'], 'required'],
            
            [['proj_id'], 'required'],
            [['q_id', 'q_agency_name', 'q_exam_name', 'q_admit_card_date', 'q_exam_date', 'q_exam_mode', 'q_paper_available', 'q_exam_shifts', 'q_anskey_start_date', 'q_anskey_end_date', 'q_anskey_type', 'q_anskey_pre_paper', 'q_anskey_final_paper', 'q_anskey_last_date', 'q_paper_downloaded_pre', 'q_paper_received_final'], 'default','value'=>''],
            
            [['q_id',   'q_exam_shifts', 'q_anskey_pre_paper', 'q_anskey_final_paper', 'q_paper_downloaded_pre', 'q_paper_received_final', 'proj_id'], 'integer'],
            [['q_admit_card_date', 'q_exam_date', 'q_anskey_start_date', 'q_anskey_end_date', 'q_anskey_last_date'], 'safe'],
           [['q_agency_name', 'q_exam_name', 'q_exam_mode', 'q_anskey_type'], 'string', 'max' => 255],
            [['q_id'], 'unique'],
          //  [['proj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['proj_id' => 'proj_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'q_id' => 'ID',
            'q_agency_name' => 'Agency Name',
            'q_exam_name' => 'Exam Name',
            'q_admit_card_date' => 'Admit Card Date',
            'q_exam_date' => 'Exam Date',
            'q_exam_mode' => 'Exam Mode',
            'q_paper_available' => 'Paper Available',
            'q_exam_shifts' => 'Exam Shifts',
            'q_anskey_start_date' => 'Ans Key Start Date',
            'q_anskey_end_date' => 'Ans Key End Date',
            'q_anskey_type' => 'Ans Key Type',
            'q_anskey_pre_paper' => 'Ans Key Preliminary',
            'q_anskey_final_paper' => 'Ans Key Final Paper',
            'q_anskey_last_date' => 'Ans Key Last Date',
            'q_paper_downloaded_pre' => 'Paper Downloaded Preliminary',
            'q_paper_received_final' => 'Paper Received Final',
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
        
        foreach([
            "q_admit_card_date","q_exam_date","q_anskey_start_date","q_anskey_end_date","q_anskey_last_date"            
        ] as $field){
            $this->$field = \app\components\Helpers::i()->formatDate($this->$field,"Y-m-d");
        }
        
        return true;
    }
}