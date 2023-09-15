<?php

/*
  Application Developed By Abhinav Software
  Website: http://abhinavsoftware.com
  Email: contact@abhinavsoftware.com
  Developer: Ankur Gupta (ankurgupta555@gmail.com)
  Copyright Ankur Gupta

  For licensing and terms of use please read license.txt file
 */

namespace app\components;

use app\components\AbstractSinglaton;
use app\nplugins\settings\Basic;
use Yii;

/**
 * Helper class provides various utility methods 
 */
class Helpers extends AbstractSinglaton {
public static function getCurrency($amount){
    
        return Basic::i()->currency_before." ".$amount." ".Basic::i()->currency_after;
    }
    public function getSecurityField(){
        return "<input type='hidden' value='".Yii::$app->request->csrfToken."' name='".Yii::$app->request->csrfParam."' />";
        
    }
    public function getClassShortName($object) {
        $ref = new \ReflectionClass($object);
        return $ref->getShortName();
    }

      function replaceImage($text){ 
            return str_replace(\yii\helpers\Url::base(true)."/uploads", Yii::getAlias("@webroot/uploads"), $text); 
        } 
        
    public function getParentClassShortName($object) {
        $ref = new \ReflectionClass($object);
        return $ref->getParentClass()->getShortName();
    }

    public function generateUID($format = false) {


        $str = strtoupper(md5(uniqid(rand(), true)));

        $length = strlen($str) - 16;
        $id = substr($str, $length);



        if ($format) {
            $this->formatUID($id);
        }
        return $id;
    }
     function autoSearch($loadList,$url, $selector = '.toolbar-form'){ ?>
            $("<?= $selector ?> input").on("input",function(){
                <?= $loadList ?>("<?= $url ?>");
            });
            $("<?= $selector ?> select").on("input",function(){
                <?= $loadList ?>("<?= $url ?>");
            });
            $("<?= $selector ?> input").on("change",function(){
                <?= $loadList ?>("<?= $url ?>");
            });
    <?php }  
    function strSplitUnicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function formatUID($id) {
        $f1 = substr($id, 0, 4);
        $f2 = substr($id, 4, 4);
        $f3 = substr($id, 8, 4);
        $f4 = substr($id, 12, 4);
        return $f1 . "-" . $f2 . "-" . $f3 . "-" . $f4;
    }

    /**
     * Generates the alias from 'title' and 'alias'.
     * @param string $title
     * @param string $alias
     * @return string
     */
    public function getAlias($title, $alias) {
        if (trim($alias) == "") {
            $words_to_filter = "~ ! @ # $ % ^ & * ( ) _ + ` = { } | [ ] \ : ; ' < > ? , . /";
            $words_to_filter = explode(" ", $words_to_filter);
            $alias_tags = strtolower(trim($title));
            $alias_tags = str_replace($words_to_filter, "-", trim($alias_tags));
            $alias_tags = str_replace(" ", "-", trim($alias_tags));
            $alias = $alias_tags;
           // $alias = implode("-", $alias_tags);
        } else {
            $words_to_filter = "~ ! @ # $ % ^ & * ( ) _ + ` = { } | [ ] \ : ; ' < > ? , . /";
            $words_to_filter = explode(" ", $words_to_filter);
            $alias_tags = strtolower($alias);
            $alias_tags = str_replace($words_to_filter, "-", $alias_tags);
            $alias_tags = str_replace(" ", "-", $alias_tags);
            $alias = $alias_tags;
        }
        if(substr($alias,-1) == "-"){
            return substr($alias, 0, -1);
        }
        return $alias;
    }

    /**
     * Converts siz in array into integer
     * @param type $val
     * @return int
     */
    public function returnBytes($val) {
            
        
        $last = strtolower($val[strlen($val) - 1]);
            $val =  (int)trim($val);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024*1024*1024;
            case 'm':
                $val *= 1024*1024;
            case 'k':
                $val *= 1024;
        } 
        return $val;
    }

    /**
     * Returns extension of any file
     * @param string $filename
     * @return string
     */
    public function getExtension($filename) {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Checks if file is an image
     * @param string $filename
     * @return boolean
     */
    public function isImageFilename($filename) {
        $ext = strtolower($this->getExtension($filename));
        if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif") {
            return true;
        }
        return false;
    }

    /**
     * Checks if user is in default action off default controller
     * @return boolean
     */
    public function isHomePage() {

        $controller = Yii::app()->getController();
        $default_controller = Yii::app()->defaultController;
        $defaultAction = $controller->defaultAction;

        if (($controller->id === $default_controller) && ($controller->action->id === $defaultAction)) {
            return true;
        }
        return false;
    }

    /**
     * Filter outs the unneccessary words and characters and extracts the words which can be used as tags
     * @param string $string
     * @return array
     */
    public function extractTags($string) {
        $words_to_filter1 = "1 2 3 4 5 6 7 8 9 0 ~ ! @ # $ % ^ & * ( ) _ + ` - = { } | [ ] \ : ; ' < > ? , . /";
        $words_to_filter2 = "a b c d e f g h i j k l m n o p q r s t u v w x y z";

        $string = " " . strtolower($string) . " ";

        //Clean Up [Word Level] 
        $words_to_filter3 = array_unique(explode("\n", file_get_contents(Yii::getAlias("@app/data") . "/filterwords_list.txt")));

        foreach ($words_to_filter3 as $wtf) {
            $string = str_ireplace(" " . trim($wtf) . " ", " ", $string);
        }
 
        //Clean Up
        str_ireplace("  ", " ", $string);

        //Clean Up  [Numbers and Special Chars]
        $words_to_filter1 = explode(" ", $words_to_filter1);
        $string = str_ireplace($words_to_filter1, " ", $string);

        //Clean Up [Single Chars]
        $words_to_filter2 = explode(" ", $words_to_filter2);
        foreach ($words_to_filter2 as $wtf) {
            $string = str_ireplace(" " . $wtf . " ", " ", $string);
        }

        $tags = explode(" ", strtolower(trim($string)));
        $newtags = array();
        foreach ($tags as $t) {
            if (trim($t) != "") {
                $newtags[] = strtolower(trim($t));
            }
        };
        $newtags = array_unique($newtags);
        return $newtags;
    }

    /**
     * Returns maximum upload file size.
     * @param boolean $showbytes
     * @return mixed
     */
    public function getMaxUploadFileSize($showbytes = true) {
        $max_upload = (int) (ini_get('upload_max_filesize'));
        $max_post = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));
        $size = min($max_upload, $max_post, $memory_limit);
        if ($showbytes) {
            return $size * 1024 * 1024 * 1024;
        } else {
            return $size;
        }
    }

    /**
     * Formats the date(string)
     * @param string $dateString
     * @param string $format
     * @return string
     */
    public function formatDate($dateString, $format = "F j, Y, g:i a") {
        if (is_array($dateString)) {
            $newdates = [];
            foreach ($dateString as $dt) {
                $timestamp = strtotime($dt);
                $newdates[] = date($format, $timestamp);
            }
            return $newdates;
        } else {
            $timestamp = strtotime($dateString);
            return date($format, $timestamp);
        }
    }

    /**
     * Returns the credit
     * @param boolean $link
     * @param string $productname
     * @return string
     */
    public function getCredit($link = false, $productname = "") {
        if ($link) {
            return $productname . ' Developed By <a href="http://abhinavsoftware.com" target="_blank">Abhinav Software</a>';
        }
        return "
        <!--     $productname
                 -------------------------------------------------------------
                 Abhinav CMS Framework (Built on the top of Yii-Framework)
                 Developed By Ankur Gupta
                 Email: ankurgupta555@gmail.com | contact@abhinavsoftware.com
                 Website: www.abhinavsoftware.com                 
        -->
        ";
    }

    /**
     * Word limiter
     * @param string $str
     * @param integer $limit
     * @param string $end_char
     * @return string
     */
    public function wordLimiter($str, $limit = 100, $end_char = '...') {
        if (trim($str) == '') {
            return $str;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,' . (int) $limit . '}/', $str, $matches);

        if (strlen($str) == strlen($matches[0])) {
            $end_char = '';
        }

        return rtrim($matches[0]) . $end_char;
    }

    /**
     * Character Limiter
     * @param string $str
     * @param integer $n
     * @param string $end_char
     * @return string
     */
    public function characterLimiter($str, $n = 500, $end_char = '&#8230;') {

        if (strlen($str) < $n) {
            return $str;
        }
        return substr($str, 0, $n) . $end_char;
    }

    public function arrayRandomAssoc($arr, $num = 1) {
        $keys = array_keys($arr);
        shuffle($keys);

        $r = array();
        for ($i = 0; $i < $num; $i++) {
            $r[$keys[$i]] = $arr[$keys[$i]];
        }
        return $r;
    }

    /**
     * Returns ratio between two numbers
     * @param int $a
     * @param int $b
     * @return array
     */
    public function getRatio($a, $b) {
        $var = $this->_gcd($a, $b);
        return array($a / $var, $b / $var);
    }

    //CREDITS
    //============================================================
    //Author: J. A. Greant ( zak@nucleus.com )
    //Version 1: June 9, 2000
    //http://www.weberdev.com/get_example-1808.html
    //============================================================
    private function _gcd($a, $b) {
        while ($b != 0) {
            $remainder = $a % $b;
            $a = $b;
            $b = $remainder;
        }
        return abs($a);
    }

    /**
     * Checks if visior is a bot or not
     * @return boolean
     */
    public function isBot() {
        $bots = array(
            'Googlebot', 'Baiduspider', 'ia_archiver',
            'R6_FeedFetcher', 'NetcraftSurveyAgent', 'Sogou web spider',
            'bingbot', 'Yahoo! Slurp', 'facebookexternalhit', 'PrintfulBot',
            'msnbot', 'Twitterbot', 'UnwindFetchor',
            'urlresolver', 'Butterfly', 'TweetmemeBot');

        foreach ($bots as $b) {
            if (stripos($_SERVER['HTTP_USER_AGENT'], $b) !== false)
                return true;
        }

        return false;
    }

    /**
     * Automatically adds <br /> tag in string for every new line.
     * @param string $text
     * @return string
     */
    public function autoTypography($text) {
        return str_replace("\n", "<br />", $text);
    }

    public function makeLinksActive($text) {
        // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        // Check if there is a url in the text
        if (preg_match($reg_exUrl, $text, $url)) {
            // make the urls hyper links
            return preg_replace($reg_exUrl, "<a href='" . $url[0] . "'>" . $url[0] . "</a> ", $text);
        } else {
            // if no urls in the text just return the text
            return $text;
        }
    }

    function dirToArray($dir) {

        $result = array();

        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    function arrayToCsv(array $fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false) {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $outputString = "";
        foreach ($fields as $tempFields) {
            $output = array();
            foreach ($tempFields as $field) {
                if ($field === null && $nullToMysqlNull) {
                    $output[] = 'NULL';
                    continue;
                }

                // Enclose fields containing $delimiter, $enclosure or whitespace
                if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
                    $field = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
                }
                $output[] = $field . " ";
            }
            $outputString .= implode($delimiter, $output) . "\r\n";
        }
        return $outputString;
    }

    
    public function showAlert(){
        $success = Yii::$app->session->getFlash("success");
                $error = Yii::$app->session->getFlash("error");
                $warning = Yii::$app->session->getFlash("warning");
                $info = Yii::$app->session->getFlash("info");


                if (is_array($error)) {
                    $error_array = "<ul>";
                    foreach ($error as $k => $v) {

                        $error_array .= "<li>". implode(" ", $v) . "</li>";
                    }
                    $error_array .= "</ul>";
                    $error = $error_array;
                }

                if (!is_null($success)) {
                    echo "<div class='main-alert alert alert-success mt-2'>$success</div>";
                }
                if (!is_null($error)) {
                    $str = "";
                    if(is_array($error)){
                        foreach($error as $k=>$v){
                            $str .= $k." => ".$v."<br />";
                        }
                    } else {
                        $str = $error;
                    }
                    echo "<div class='main-alert alert alert-danger mt-2'>$str</div>";
                }
                if (!is_null($warning)) {
                    echo "<div class='main-alert alert alert-warning mt-2'>$warning</div>";
                }
                if (!is_null($info)) {
                    echo "<div class='main-alert alert alert-info mt-2'>$info</div>";
                }
    }
}
