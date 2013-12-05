<?php
/**~engine/lib/Controller.php
* 
* Controller
* ---
* Controlador de modulos y acciones
* 
* @package      FALCODE
* @version      3.0
* @author       FALCODE
*/
abstract class Controller{
    /**
    * Constantes
    */
    const ACT_LIST="list";
    const ACT_VIEW="view";
    const ACT_EDIT="edit";
    const ACT_ADD="add";
    const ACT_ADD_EDIT="ae";
    const ACT_SAVE="save";
    const ACT_DELETE="delete";
    const ACT_DEFAULT="main";
    const DISPLAY_PREFIX='';
    const ACTION_PREFIX='';
    const AUTO_ADD_HTML=true;
    
    /**
    * Non static variables
    */
    public $loaded=array();
    public $content=NULL;
    public $breadcrumb=array();
    public $params=array();
    //protected $db=NULL;
    public $defaultAction='main';
    public $redered=false;
    private $_load=null;
    private $_config=null;
    private $_lang=null;
    private $_session=null;
    private $_sys=null;
    private $_user=null;
    private $_db=null;
    private $_request=null;
    private $_response=null;
    public static $default_action;
    public static $include=NULL;
    public static $blank=false;
    public static $ajax=false;
    
    /**
    * Establece el metodo por defecto
    * 
    * @param mixed $action
    */
    final protected function setDefaultAction($action){
        if(empty($action))
            return false;
        $this->defaultAction=$action;
        self::$default_action=$action;
    }
    
    /**
    * Constructora
    * Solo se podrá instanciar para ser la clase padre de un 
    * controlador de aplicacion
    * 
    */
    protected function __construct(){

    }
    
    /**
    * Funcion para establecer el titulo del template actual
    * 
    * @param mixed $title
    * @return mixed
    */
    final protected function title($title=NULL){
        Tpl::set('PAGE_TITLE',$title);
    }
    
    /**
    * Funcion para establecer el layout actual como _blank.html
    * 
    * @param mixed $arg
    * @return mixed
    */
    final public function blank(){
        Tpl::set('MAIN_TEMPLATE',"_blank.html");
    }
    
    /**
    * Imprime en pantalla el output del template que se esta procesando
    * 
    */
    final public function render($replace_cache=false){
        if($this->__get('load')->getDefaultView())
            echo $this->__get('load')->getDefaultView()->render();
        $this->rendered=true;
        
    }
    
    /**
    * Incluye un archivo externo de control
    * 
    * @param mixed $file
    */
    final public function includeController($file=NULL){
        if(!empty($file)){
            $file=PATH_CONTROLLER_MODULES.DSP_MODULE.'/'.$file.(strpos($file,'.')===false ? '.php' : '');
            if(!file_exists($file)){
                Sys::raiseWarning("Controller::includeController() - El archivo de control <b>$file</b> no existe");
            }
            include_once($file);
        }
        return;
    }
    
    /**
    * Alias para Router::$module
    * @deprecated Use Request::module()
    */
    final protected function getModule(){
        return Router::$Control['module'];
    }
    
    /**
    * Alias para Router::$action
    * @deprecated Use Request::action()
    */
    final protected function getAction(){
        return Router::$Control['control'];
    }
    
    /**
    * Funcion para conseguir el _GET
    * @deprecated Use Request::get
    * @param mixed $key
    * @return mixed
    */
    final protected function GET($key=NULL,$searchRequest=true){
        if($key==NULL)
            return $_GET;
        if(!empty($_GET[$key]))
            return $_GET[$key];
        if(!empty($_REQUEST[$key]) && $searchRequest)
            return $_REQUEST[$key];
        return NULL;
    }
    
    /**
    * Funcion para conseguir el _POST
    * @deprecated Use Request::post
    * @param mixed $key
    * @return mixed
    */
    final protected function POST($key=NULL,$searchRequest=true){
        if($key==NULL)
            return $_POST;
        if(!empty($_POST[$key]))
            return $_POST[$key];
        if(!empty($_REQUEST[$key]) && $searchRequest)
            return $_REQUEST[$key];
        return NULL;
    }
    
    /**
    * Establece el valor del breadcrumb y lo devuelve
    * 
    * @param mixed $arr
    */
    final public function breadcrumb($arr=NULL){
        global $BREADCRUMB;
        if(!empty($arr)){
            $this->breadcrumb=$arr;
            Tpl::set('BREADCRUMB',$arr);
            $BREADCRUMB=$arr;
        }
        return $this->breadcrumb;
    }
    
    /**
    * Redirige al usuario a la pagina especificada
    * 
    * @param mixed $url
    * @param mixed $fwdGet      En caso de true envia el GET actual
    * @param mixed $fwdPost     En caso de true, reenvia el POST actual
    */
    final protected function redirect($url=NULL,$fwdGet=NULL,$fwdPost=NULL){
        if($url==NULL)
            return false;
        return redirect(url($url),($fwdGet==NULL ? false : true),($fwdPost==NULL ? false : true));
    }
    
    /**
    * Establece los headers para json
    * depreciated
    * Use: Response::isJSON() instead
    */
    final protected function isJSON(){
        $this->blank(true);
        Sys::get('response')->setHeader("Content-Type: application/json");
    }
    
    /**
    * Determine wether the actual request was generated via AJAX or not
    * @return bool
    * depreciated: use Request::isAjax() instead
    */
    final public function isAjaxRequest(){
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
            $_SERVER['HTTP_X_REQUESTED_WITH'] = '';
        if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || @Router::$Control['ajax']==true)
            return true;
        return false;
    }
    
    /**
    * Get the memcached system object
    * 
    */
    final protected function &memcached(){
        return Sys::$Memcached;
    }
    
    /**
    * Magic get for loaded modules and models
    * 
    * @param mixed $method
    * @return mixed
    */
    final public function __get($method){
        switch($method){
            case "load":
                if(empty($this->_load)){
                    $this->_load =& Loader::getInstance();
                }
                return $this->_load;
                break;
            case "config":
                if(empty($this->_config)){
                    $this->_config =& Config::getInstance();
                }
                return $this->_config;
                break;
            case "lang":
                if(empty($this->_lang)){
                    $this->_lang = new Lang();
                }
                return $this->_lang;
                break;
            case "session":
            case "ses":
                if(empty($this->_session)){
                    $this->_session = new Session();
                }
                return $this->_session;
                break;
            case "sys":
            case "system":
                if(empty($this->_sys)){
                    $this->_sys=new Sys();
                }
                return $this->_sys;
                break;
            case "user":
                if(empty($this->_user)){
                    $this->_user = new ThisUser();
                }
                return $this->_user;
                break;
            case "db":
                if(empty($this->_db)){
                    $this->_db = Db::getInstance();
                }
                return $this->_db;
                break;
            case "request":
                if(empty($this->_request)){
                    $this->_request = Request::getInstance();
                }
                return $this->_request;
                break;
            case "response":
                if(empty($this->_response)){
                    $this->_response = Response::getInstance();
                }
                return $this->_response;
                break;
        }
        return $this->_load->{$method};
    }
    
    /**
    * Helper function to load views on this context instead of the Loader class context
    * 
    * @param mixed $file
    * @param mixed $vars
    */
    final public function includeView($file,$vars=array()){
        if(!empty($vars)){
            foreach($vars as $k => $v){
                ${$k} = $v;
            }
        }
        include($file);
    }

    final public function loadSnippet($file,$context=NULL){
        $this->includeView($this->loadView($file));
    }
    
    /**
    * Shortcut to throwing an access denied exception
    * 
    * @param mixed $msg
    */
    final public function throwAccessDenied($msg="Access denied"){
        throw new ControllerException($msg,ControllerException::ACCESS_DENIED);
    }
    
    /**
    * Shortcut to throwing an under maintenancer exception
    * 
    * @param mixed $msg
    */
    final public function throwUnderMaintenance($msg="Under maintenance"){
        throw new ControllerException($msg,ControllerException::UNDER_MAINTENANCE);
    }
    
    /**
    * Shortcut to throwing a custom error exception
    * 
    * @param mixed $msg
    */
    final public function throwCustomError($msg="Unknown error"){
        throw new ControllerException($msg,ControllerException::CUSTOM_ERROR);
    }

    /**
     * Shortcut to throwing a custom error exception
     *
     * @param mixed $msg
     */
    final public function throwValidationError($arr = array()){
        throw new ControllerException(serialize($arr),ControllerException::VALIDATION);
    }
    
    final protected function locateView($file){
        return Tpl::htmlPath().$file;
    }
    
}