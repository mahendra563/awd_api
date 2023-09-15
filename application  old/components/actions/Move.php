<?php
/*
Application Developed By Abhinav Software
Developer: Ankur Gupta
Email: contact@abhinavsoftware.com
Mobile: 09977139265
*/

namespace app\components\actions;

use app\components\AjaxResponse;
use app\components\Helpers;
use Yii;
use yii\base\Action;

class Move extends Action{
    public $modelClass; 
    public $direction;
    public function run(){        
        
        $modelClass = $this->modelClass;
        $model = new $modelClass;
        
        $id = Yii::$app->request->post("id");
        
        if($this->direction == "up"){
            $model->moveUp($id);
        } else if($this->direction == "down"){
            $model->moveDown($id);
        }
        
        return AjaxResponse::i()->setStatus(true)->send();
         
    }
    
    public static function jsCode($selector,$url,$afterSuccess){
        ?>

$(document).on("click", "<?= $selector ?>", function (e) {
    e.preventDefault();           
    var id = $(this).attr("data-id"); 
    $.ajax({
        type: "post",
        data: {"id": id},
        url: "<?= $url; ?>",
        success: function (response, textStatus, XMLHttpRequest) {
            <?= $afterSuccess ?>
        }
    });
    return false;
});

<?php
    }
}