<?php
class Request{
    private static $instance = NULL;
    private $request_vars = array();

    private function __construct(){
        
    }
    
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = new Request();
        return self::$instance;
    }
    
    /**
    * Retrieve the superglobal _GET
    * 
    * @param mixed $key
    * @param mixed $cast
    * @return string
    */
    public function get($key,$cast = ""){
        if(!isset($_GET[$key]))
            return NULL;
        $g = $_GET[$key];
        if(get_magic_quotes_gpc()){
            if(is_array($g))
                array_walk($g, 'my_stripslashes');  
            else
                $g = stripslashes($g);
        }
        $this->castVal(&$g,$cast);
        return $g;
    }
    
    /**
    * Retrieve the superglobal _POST
    * 
    * @param mixed $key
    * @param mixed $cast
    * @return string
    */
    public function post($key,$return_object = false,$cast = ""){
        if(strpos($key,'[') !== false){
            $_key = str_replace(']','',$key);
            $_key = explode("[",$_key);
            eval('$g = isset($_POST['.implode('][',$_key).']) ? $_POST['.implode('][',$_key).'] : null;');
        }else{
            $g = isset($_POST[$key]) ? $_POST[$key] : null;
        }
        if(is_null($g) && !$return_object)
            return NULL;

        if(get_magic_quotes_gpc()){
            if(is_array($g))
                array_walk($g, 'my_stripslashes');  
            else
                $g = stripslashes($g);
        }
        $this->castVal(&$g,$cast);
        if(!$return_object)
            return $g;
        else{
            if(!isset($this->request_vars[$key])){
                $this->request_vars[$key] = new RequestVar($key,$g);
            }
            return $this->request_vars[$key];
        }
    }
    
    /**
    * Retrieve superglobals _POST or _GET
    * 
    * @param mixed $key
    * @return string
    */
    public function __get($key){
        $r = $this->post($key);
        if(is_null($r)){
            $r = $this->get($key);
        }
        return $r;
    }
    
    /**
    * Determine if the request was made via AJAX
    * 
    */
    public function isAjax(){
        if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || Router::$Control['ajax']==true)
            return true;
        return false;
    }
    
    /**
    * Check if request var is set
    * 
    * @param mixed $key
    */
    public function __isset($key){
        return $this->__get($key) === NULL ? false : true;
    }
    
    public function path($index=NULL){
        if(is_null($index))
            return Router::$path;
        return Router::$path[$index];
    }
    
    /**
    * Alias para Router::$module
    * 
    */
    public function module(){
        return Router::$Control['module'];
    }
    
    /**
    * Alias para Router::$action
    * 
    */
    public function action(){
        return Router::$Control['control'];
    }
    
    private function castVal(&$g,$cast){
        switch($cast){
            case "int":
                $g = (int)$g;
                break;
            case "float":
                $g = (float)$g;
                break;
            case "string":
                $g = (string)$g;
                break;
            case "bool":
            case "boolean":
                $g = (bool)$g;
                break;
        }
    }

    public function validate($throw_exception = true){
        $errors = array();
        if(!empty($this->request_vars)){
            foreach($this->request_vars as $rv){
                if(!$rv->validate())
                    $errors[] = array('field' => $rv->index, 'error' => $rv->error);
            }
        }
        if($throw_exception && !empty($errors)){
            Sys::setErrorMsg(Lang::get('validation_error'));
            Sys::get('module_controller')->throwValidationError($errors);
        }else{
            return empty($errors);
        }
    }
}