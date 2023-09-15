<?php


?>
<style>
 
/* devanagari */
@font-face {
   font-family: 'Noto Sans';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  
   src: url(<?= Yii::getAlias("@webroot/fonts/NotoSans-Regular.ttf") ?>) format('truetype');
  /*
 
  src: url(<?= Yii::getAlias("@web/fonts/o-0IIpQlx3QUlC5A4PNr5DRAW_0.woff2") ?>) format('woff2');
  
  src: url(<?= Yii::getAlias("@app/data/o-0IIpQlx3QUlC5A4PNr5DRAW_0.woff2") ?>) format('woff2');
  src: local('Noto Sans'), local('NotoSans'), url(https://fonts.gstatic.com/s/notosans/v9/o-0IIpQlx3QUlC5A4PNr5DRAW_0.woff2) format('woff2');
  unicode-range: U+0900-097F, U+1CD0-1CF6, U+1CF8-1CF9, U+200C-200D, U+20A8, U+20B9, U+25CC, U+A830-A839, U+A8E0-A8FB;*/
}
 
   .content {
    font-family: 'Noto Sans';
  }
    
<?php if($model->cnt_language == "Hindi" && $model->cnt_keyboard == "Remington"){ ?>
    
<?php } else if ($model->cnt_language == "Hindi" && $model->cnt_keyboard == "Inscript"){ ?>
     
<?php } ?>
</style>


<div class="content">
    <h1><?= $model->cnt_title ?></h1>
        <?= app\components\Helpers::i()->autoTypography($model->cnt_text) ?>
    </div>


<p>
    Typing Test Software developed by <a href="https://abhinavsoftware.com" target="_blank">www.abhinavsoftware.com</a>
</p>