<?php

namespace app\forms;

use Yii;

class Project extends yii\base\Model{
    public $proj_id;
    public $proj_title;
    public $cost1 = 0;
    public $cost2 = 0;
    public function rules(){
        return [
            [["cost1","cost2"],"numeric"],
            [["proj_title","cost1","cost2"],"required"],
            [['proj_title'], 'string', 'max' => 255],
        ];
    }
    public function attributeLabels(): array {
        $r = parent::attributeLabels();
        $r["proj_id"] = "Project ID";
        $r["proj_title"] = "Project Title";
        $r["cost1"] = "Cost 1";
        $r["cost2"] = "Cost 2";
        return $r;
    }
    public static function get($id){        
        $model = \app\models\Project::findOne($id);
        if(is_null($model)){
            return null;
        }
        
        $form = new self();
        $form->proj_id = $model->prod_id;
        $form->proj_title = $model->proj_title;
        
        $costs = $model->projectCosts;        
        
        if(count($costs)==2){
            $costList = \yii\helpers\ArrayHelper::map($costs, "cost_title", "cost_amount");
            $form->cost1 = $costList["C1"];
            $form->cost2 = $costList["C2"];
        }
        
        return $form;
    }
    public function save(){
        $model = \app\models\Project::findOne($this->proj_id);
        if(is_null($model)){
            $model = new Project();
        }
        $model->proj_title = $this->proj_title;
        if($model->validate()){
            $model->save();
        }
        
        foreach (["C1"=>$this->cost1,"C2"=>$this->cost2] as $cost_title=>$cost){
            $cost = \app\models\ProjectCost::findOne(["cost_title"=>$cost_title]);
            if(is_null($cost)){
                $cost = new \app\models\ProjectCost();
            }
            $cost->cost_title = $cost_title;            
            $cost->cost_amount = $cost;
            $cost->proj_id = $model->proj_id;
            if($cost->validate()){
                $cost->save();
            }
        }
        
    }
}