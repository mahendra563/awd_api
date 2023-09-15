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

 

class AjaxResponse extends AbstractSinglaton{
    private $_authenticaton = true;
    private $_authorization = true;
    private $_status = false;
    private $_validation_status = true;
    private $_validation_errors = false; 
    private $_error = false;
    
    function __toString() {
        $data = array(
            "authentication" => $this->_authenticaton,
            "authorization" => $this->_authorization,
            "status" => $this->_status,
            "data" => $this->_data,
            "html" => $this->_html,
    
            "msg"=>$this->_msg,
 
            "validation_status" => $this->_validation_status,
            "validation_errors" => $this->errorsToString($this->_validation_errors),
            "error" => $this->_error
        );
        return json_encode($data);
    }
     
    /**
     * Sets the authentication true or false
     * @param boolean $authentication
     * @return \AjaxOutput
     */
    public function setAuthentication($authentication) {
        $this->_authenticaton = $authentication;
        return $this;
    }
    /**
     * Sets the authorization true or false
     * @param type $authorization
     * @return \AjaxOutput
     */
    public function setAuthorization($authorization) {
        $this->_authorization = $authorization;
        return $this;
    }

    /**
     * Sets the status true or false
     * @param type $status
     * @return \AjaxOutput
     */
    public function setStatus($status) {
        $this->_status = $status;
        return $this;
    }

    private $_html = "";
    public function setHtml($html){
        $this->_html = $html;
        return $this;
    }
    
    private $_data = [];
    public function setData($data){
        $this->_data = $data;
        return $this;
    }
    
     
     
    /**
     * Sets the validation status true or false (Should be used in form validation)
     * @param type $validation_status
     * @return \AjaxOutput
     */
    public function setValidationStatus($validation_status) {
        $this->_validation_status = $validation_status;
        return $this;
    }

    /**
     * Sets the validation errors in array (Should be used in form validation)
     * @param array $validation_errors
     * @return \AjaxOutput
     */
    public function setValidationErrors($validation_errors) {
        $this->_validation_errors = $validation_errors;
        return $this;
    }
 
    /**
     * Sets the error
     * @param string $error
     * @return \AjaxOutput
     */
    public function setError($error) {
        $this->_error = $error;
        return $this;
    } 
    
    /**
     * Sets the error
     * @param string $msg
     * @return \AjaxOutput
     */
    private $_msg="";
    public function setMsg($msg) {
        $this->_msg = $msg;
        return $this;
    }
    
    /**
     * Outputs the result in json format
     */
    public function send() {
        $data = array(
            "authentication" => $this->_authenticaton,
            "authorization" => $this->_authorization,
            "status" => $this->_status,
            "data" => $this->_data,
            "html" => $this->_html,
 
            "msg"=>$this->_msg,
            
            "validation_status" => $this->_validation_status,
            "validation_errors" => $this->_validation_errors, //$this->errorsToString($this->_validation_errors),
            "error" => $this->_error
        );
        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = $data;
        
    }
    
     /**
     * Outputs the result in json format
     */
    public function display() {
        $data = array(
            "authentication" => $this->_authenticaton,
            "authorization" => $this->_authorization,
            "status" => $this->_status,
            "data" => $this->_data,
            "html" => $this->_html,
  
            "msg"=>$this->_msg,
 
            "validation_status" => $this->_validation_status,
            "validation_errors" => $this->errorsToString($this->_validation_errors),
            "error" => $this->_error
        );
        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = $data;
        echo \yii\helpers\Json::encode($data);
        exit();
    }
    
    
    /**
     * Converts validation errors array into string
     * @param array $errors
     * @return string
     */
    public function errorsToString($errors) {
        $str = "";
        if (is_array($errors)) {
            foreach ($errors as $element => $e) {
                $str .= implode("\n", $e) . "\n";
            }
        }
        return $str;
    }
}