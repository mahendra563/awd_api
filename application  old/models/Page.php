<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pages".
 *
 * @property int $page_id
 * @property string $page_title
 * @property string $page_content
 * @property string $page_status
 * @property string $page_created_on
 * @property string $page_updated_on
 */
class Page extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_title', 'page_content', 'page_status', 'page_created_on', 'page_updated_on'], 'required'],
            [['page_content'], 'string'],
            [['page_created_on', 'page_updated_on'], 'safe'],
            [['page_title'], 'string', 'max' => 255],
            [['page_status'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'page_id' => 'Page ID',
            'page_title' => 'Page Title',
            'page_content' => 'Page Content',
            'page_status' => 'Page Status',
            'page_created_on' => 'Page Created On',
            'page_updated_on' => 'Page Updated On',
        ];
    }
    
    public function beforeValidate() {
        parent::beforeValidate();
        if($this->isNewRecord){
            $this->page_created_on = date("Y-m-d H:i:s");
        }
        $this->page_updated_on = date("Y-m-d H:i:s");
        return true;
    }
}
