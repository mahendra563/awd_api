<?php

namespace app\controllers\manager;

use app\components\actions\Delete;
use app\components\actions\Get;
use app\components\actions\Loadlist;
use app\components\actions\Save;
 
use app\components\AjaxResponse;
use app\components\TempData;
use app\models\QbFile;
use app\models\QbKeyword;
use app\models\QbKeywordFileJoin;
use Yii;
use yii\base\Event;
 

class QbBankController extends \app\components\ManagerController
{
    public function actions()
    {
        return [
            'save' => [
                'class' => Save::className(),
                'modelClass'=>  QbFile::className(),     
                'pk'=>'file_id'
            ],
            'delete' => [
                'class' => Delete::className(),
                'modelClass'=>  QbFile::className(),                     
            ],
            'get' => [
                'class' => Get::className(),
                'modelClass'=>  QbFile::className(),         
                'beforeServing'=>[$this,"_beforeServing"]
            ],  
            'loadlist' => [
                'class' => Loadlist::className(),
                'pageSize'=>Yii::$app->request->get("perpage",10),
                'query'=> $this->_loadList(
                        \Yii::$app->request->get("search"),
                        \Yii::$app->request->get("type"),
                        \Yii::$app->request->get("state")
                        ),
                'beforeServingEachRecord'=>[$this,'_beforeServingEachRecord']
            ], 
        ];
    }
    
    public function _beforeServingEachRecord($r){
        $emp  = TempData::i()->get("loggedInUser");          
        $r["readonly"] = false; 
        if(strtotime($r["file_uploaded_on"]) < strtotime(date("Y-m-d H:i:s"))-3600-3600){           
            $r["readonly"] = true;
        }        
        return $r;
    }
    
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);         
        Event::on(QbFile::class, QbFile::EVENT_AFTER_INSERT, [$this,"_afterSave"]);
        Event::on(QbFile::class, QbFile::EVENT_AFTER_UPDATE, [$this,"_afterSave"]);
        Event::on(QbFile::class, QbFile::EVENT_BEFORE_DELETE, [$this,"_beforeDelete"]);        
        Event::on(QbFile::class, QbFile::EVENT_BEFORE_VALIDATE, [$this,"_beforeValidate"]);
        
        Event::on(QbFile::class, QbFile::EVENT_BEFORE_UPDATE, [$this,"_beforeUpdate"]);
    }
    
     
    
    public function _beforeValidate(Event $e){
        $user = TempData::i()->get("loggedInUser");
        $e->sender->emp_id = $user->emp_id;
    }


    public function getAccessType(){
        $user = TempData::i()->get("loggedInUser");
        $model = \app\models\WorkEntry::find()
                ->innerJoinWith("project.managerJoins")
                ->where("proj_type = :type AND w_status = 'Sent to AWD' AND project_managers.emp_id = :emp_id",
                        [":type"=> \app\models\Project::TYPE_QBANK,":emp_id"=>$user->emp_id])
                ->one();
        if(is_null($model)){
            $access = "Read";
        } else {
            $access = "Read Write";
        }
        return $access;
    }
    
    public function actionGetAccess(){
        return AjaxResponse::i()->setData($this->getAccessType())->send();
    }
    
    public function _loadList($search, $type, $state){
        $q = QbFile::find();
        $w = $p = [];
        if($state!==""){
            $q = $q->innerJoin("qb_keyword_file_joins AS state_join","state_join.file_id = qb_files.file_id");
            $q = $q->innerJoin("qb_keywords as state","state.kw_id = state_join.kw_id AND state.kw_type = 'State'");
            $w[] = "state.kw_id = :state";
            $p[":state"] = $state;
        }
        
        if($type!==""){
            $q = $q->innerJoin("qb_keyword_file_joins AS type_join","type_join.file_id = qb_files.file_id");
            $q = $q->innerJoin("qb_keywords as type","type.kw_id = type_join.kw_id AND type.kw_type = 'Type'");
            $w[] = "type.kw_id = :type";
            $p[":type"] = $type;
        }
        
        if($search!==""){
            $q = $q->innerJoin("qb_keyword_file_joins AS tag_join","tag_join.file_id = qb_files.file_id");
            $q = $q->innerJoin("qb_keywords as tag","tag.kw_id = tag_join.kw_id AND tag.kw_type = 'Tag'");
            $w[] = "(tag.kw_title LIKE :search OR file_title LIKE :search)";
            $p[":search"] = "%$search%";            
        }
        
        $q = $q->where(implode(" AND ",$w),$p)->with(["employee","tags","state","type"])->asArray()->orderBy("file_id desc");
        
        return $q;        
    }


    public function _afterSave(Event $e){
        $file_id = $e->sender->file_id;
        $state_ids = [isset($_POST["QbFile"]["states"]) ? $_POST["QbFile"]["states"] : ""];
        $type_ids = [isset($_POST["QbFile"]["types"]) ? $_POST["QbFile"]["types"] : ""];
        $keywords = isset($_POST["QbFile"]["keywords"]) ? $_POST["QbFile"]["keywords"] : "";
        
        QbKeywordFileJoin::deleteAll(["file_id"=>$file_id]);        
        foreach($state_ids as $kw_id){
            if($kw_id!==""){
                $model = new QbKeywordFileJoin();
                $model->file_id = $file_id;
                $model->kw_id = $kw_id;
                if($model->validate()){
                    $model->save();
                }
            }
        }
        
        foreach($type_ids as $kw_id){
            if($kw_id!==""){
                $model = new QbKeywordFileJoin();
                $model->file_id = $file_id;
                $model->kw_id = $kw_id;
                if($model->validate()){
                    $model->save();
                }
            }
        }
        
        $keywords = explode(",", strtolower($keywords));
        foreach($keywords as $keyword){
            $keyword = trim($keyword);
            if($keyword!==""){
               
                $model1 = QbKeyword::find()->where(["kw_title"=>$keyword])->one();
                if(is_null($model1)){
                    $model1 = new QbKeyword();
                    $model1->kw_title = $keyword;
                    $model1->kw_type = "Tag";
                    if($model1->validate()){
                        $model1->save();
                    } else {
                        $model1->getErrors();
                    }  
                    
                }

                $model2 = new QbKeywordFileJoin();
                $model2->file_id = $file_id;
                $model2->kw_id = $model1->kw_id;
                if($model2->validate()){
                    $model2->save();
                }
            }
        }
        
    }
    
    public function _beforeDelete(Event $e){
        $userType = TempData::i()->get("loggedInUserType");
        $user = TempData::i()->get("loggedInUser");
        if($userType == "User" && $user->user_id > 1){
            if (time() > strtotime($e->sender->file_uploaded_on) + (3600*2)){
                AjaxResponse::i()->setError("You can not delete this file anymore")->display();
            }            
        }
    }
    
    public function _beforeServing($r){
        $r["keywords"] = [];
        $r["states"] = "";
        $r["types"] = "";
        $models = QbKeywordFileJoin::find()->where(["file_id"=>$r["file_id"]])->with(["keyword"])->all();
        foreach($models as $model){
            if($model->keyword->kw_type == "State"){
                $r["states"] = $model->keyword->kw_id;
            } else if($model->keyword->kw_type == "Type"){
                $r["types"] = $model->keyword->kw_id;
            } else {
                $r["keywords"][] = $model->keyword->kw_title;
            }
        }
        $r["keywords"] = implode(",", $r["keywords"]);
        return $r;
    }
    
}
