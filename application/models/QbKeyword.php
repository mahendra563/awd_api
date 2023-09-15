<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "qb_keywords".
 *
 * @property int $kw_id
 * @property string $kw_title
 * @property string $kw_type
 */
class QbKeyword extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qb_keywords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kw_title', 'kw_type'], 'required'],
            [['kw_title'], 'string', 'max' => 255],
            [['kw_type'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'kw_id' => 'Kw ID',
            'kw_title' => 'Title',
            'kw_type' => 'Type',
        ];
    }
    
    /**
     * Gets query for [[QbKeywordFileJoins]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQbKeywordFileJoins()
    {
        return $this->hasMany(QbKeywordFileJoin::className(), ['kw_id' => 'kw_id']);
    }
    
    /**
     * Gets query for [[QbKeywordFileJoins]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQbFiles()
    {
        return $this->hasMany(QbFile::className(), ['file_id' => 'file_id'])->via("qbKeywordFileJoins");
    }
    
}
