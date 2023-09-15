<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "qb_keyword_file_joins".
 *
 * @property int $join_id
 * @property int $file_id
 * @property int $kw_id
 *
 * @property QbFiles $file
 * @property QbKeywords $kw
 */
class QbKeywordFileJoin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qb_keyword_file_joins';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_id', 'kw_id'], 'required'],
            [['file_id', 'kw_id'], 'integer'],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => QbFile::className(), 'targetAttribute' => ['file_id' => 'file_id']],
            [['kw_id'], 'exist', 'skipOnError' => true, 'targetClass' => QbKeyword::className(), 'targetAttribute' => ['kw_id' => 'kw_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'join_id' => 'Join ID',
            'file_id' => 'File ID',
            'kw_id' => 'Kw ID',
        ];
    }

    /**
     * Gets query for [[File]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(QbFile::className(), ['file_id' => 'file_id']);
    }

    /**
     * Gets query for [[Kw]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getKeyword()
    {
        return $this->hasOne(QbKeyword::className(), ['kw_id' => 'kw_id']);
    }
}
