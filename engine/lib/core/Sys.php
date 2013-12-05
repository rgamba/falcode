<?php
/**~engine/lib/Sys.php
* ---
* Sys (Estatica - No instanciable)
* ---
* Variables del sistema y mensajes
* 
* @package      PHPlus
* @version      3.0
* @author       PHPlus
*/
class Sys{
    private static $params;
    public static $CSS_Files=array();
    public static $JS_Files=array();
    public static $JS_Req_Files=array();
    public static $CSS_Req_Files=array();
    public static $req_use_on_blank=true;
    public static $Module_JS="";
    public static $PHP_Files=array();
    public static $Autoload=array(); // Archivos que se tienen que incluir automaticamente
    public static $Autoloaded_Classes=array();
    public static $Loaded_Packages=array();
    public static $Memcached_servers=array();
    public static $Memcached;
    public static $Acl;
    public static $Config;
    public static $Db;
    // TODO
    private static $registry;
	
    public function setError($err){
        self::setErrorMsg($err);
    }
    
    public function getError(){
        return self::getErrorMsg();
    }
    
    public function setInfo($msg){
        self::setInfoMsg($msg);
    }
    
    public function getInfo(){
        return self::getInfoMsg();
    }
    
    public function setFlash($name,$val=""){
        $_SESSION['__flash_new__'][$name]=$val;
    }

    public function setFlashNow($name,$val=""){
        $_SESSION['__flash__'][$name]=$val;
    }

    
    public function getFlash($name=""){
        return empty($name) ? $_SESSION['__flash__'] : $_SESSION['__flash__'][$name];
    }
    
    public function hasError(){
        return self::hasErrorMsg();
    }
    
    public function __get($key){
        return self::$params[$key];
    }
    
    public function __set($key,$val){
        self::$params[$key] = $val;
    }
    
    public function __isset($key){
        return isset(self::$params[$key]);
    }
    
    public function __unset($key){
        unset(self::$params[$key]);
    }
    
    /**
    * Establece una variable de sistema
    * 
    * @param mixed $key
    * @param mixed $val
    */
	public static function set($key='',$val=''){
		if(empty($key) || empty($val))
			return false;
		self::$params[$key]=$val;
		return;
	}
	
    /**
    * Obtiene el valor de una variable de sistema
    * 
    * @param mixed $key
    * @return Sys
    */
	public static function get($key=''){
		if(empty($key))
			return false;
		return empty(self::$params[$key]) ? null : self::$params[$key];
	}
	
    /**
    * Detiene la ejecuci?n de la aplicacion
    * mediante die()
    * 
    */
	public static function quit(){
		if(!empty(self::$params['CONTENT_TYPE']))
			header("Content-type: ".self::$params['CONTENT_TYPE']);
		die(self::$params['DIE_MSG']);
	}
    
    /**
    * Establece un mensaje de informacion
    * 
    * @param mixed $msg
    */
    public static function setInfoMsg($msg){
        $_SESSION['__msg__']=$msg;
    }
    
    /**
    * Establece un mensaje de error
    * 
    * @param mixed $msg
    */
    public static function setErrorMsg($msg){
        $_SESSION['__error__']=$msg;
    }
    
    /**
    * Devuelve el mensaje de informacion
    * 
    */
    public static function getInfoMsg(){
        return empty($_SESSION['__msg__']) ? '' : $_SESSION['__msg__'];
    }
    
    /**
    * Devuelve el mensaje de error
    * 
    */
    public static function getErrorMsg(){
        return empty($_SESSION['__error__']) ? '' : $_SESSION['__error__'];
    }
    
    /**
    * Borra el mensaje de informacion
    * 
    */
    public static function clearInfoMsg(){
        unset($_SESSION['__msg__']);
    }
    
    /**
    * Borra el mensaje de error
    * 
    */
    public static function clearErrorMsg(){
        unset($_SESSION['__error__']);
    }
    
    public static function clearFlash(){
        unset($_SESSION['__flash__']);
    }

    public static function updateFlash(){
        if(empty($_SESSION['__flash_new__']))
            $_SESSION['__flash_new__'] = '';
        $_SESSION['__flash__'] = $_SESSION['__flash_new__'];
        unset($_SESSION['__flash_new__']);
    }
    
    /**
    * Devuelve true en caso de tener un mensaje de informacion
    * 
    */
    public static function hasInfoMsg(){
        return !empty($_SESSION['__msg__']);
    }
    
    /**
    * Devuelve true en caso de tener un mensaje de error
    * 
    */
    public static function hasErrorMsg(){
        return !empty($_SESSION['__error__']);
    }
    
    /**
    * Crea un error de ejecuci?n de tipo
    * E_USER_WARNING
    * 
    * @param mixed $msg
    */
    public static function raiseWarning($msg=NULL){
        if(NULL==$msg)
            return false;
        trigger_error($msg,E_USER_WARNING);
    }
    
    /**
    * Crea un error de ejecuci?n de tipo
    * E_USER_ERROR
    * 
    * @param mixed $msg
    */
    public static function raiseError($msg=NULL){
        if(NULL==$msg)
            return false;
        trigger_error($msg,E_USER_ERROR);
    }
    
    public static function path($path=""){
        switch(strtolower($path)){
            case '':
            case 'img':
            case 'images':
                return HTTP.PATH_CONTENT_TEMPLATES.Tpl::get(ACTIVE)."/images/";
                break;
            case 'css':
                return HTTP.PATH_CONTENT_TEMPLATES.Tpl::get(ACTIVE)."/css/";
                break;
            case 'javascript':
            case 'js':
                return HTTP.PATH_CONTENT_TEMPLATES.Tpl::get(ACTIVE)."/js/";
                break;
        }
    }
}