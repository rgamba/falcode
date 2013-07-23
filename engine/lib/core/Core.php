<?php
/**~engine/core/Core.php
* 
* Core (Singleton - No instanciable)
* ---
* System basic functions
* 
* @package      FALCODE
* @version      2.0
* @author       FALCODE
*/
class Core{
    const ERR_HEADERS_SENT="Error: Headers already sent.";
    
    public static $slaveOutput=""; // Output retenido por ob_get_clean();
    public static $autoload=array();
    
    private function __construct(){
        return false;
    }
    
    /**
    * Funcion de load automatico para clases que esten dentro de 
    * engine>bin o dentro de engine>lib
    * 
    * @param mixed $class
    */
    public static function autoload($class){
        $found=false;
        if(!empty(self::$autoload)){
            foreach(self::$autoload as $dir){
                if(@file_exists($dir.$class.'.php')){
                    require_once($dir.$class.'.php');
                    $found=true;
                    break;
                }
            }
        }
        if(!$found){
            // Verificamos las funciones de los paquetes
            if(!empty(Sys::$Loaded_Packages)){
                foreach(Sys::$Loaded_Packages as $name => $al){
                    if(!empty($al)){
                        call_user_func($al,$class);       
                    }
                }
            }
        } 
    }
    
    /**
    * Genera un log de evento al sistema
    * Utiliza la clase Log
    * 
    * @param mixed $params
    * @return mixed
    */
    public static function log($params=array()){
        if(empty($params))
            return false;
        return Log::create($params);
    }
    
    /**
    * Envia los headers de CONTENT TYPE y charset
    * definidos previamente por el sistema
    * 
    */
    public static function contentType(){
        if(defined('SYS_CONTENT_TYPE') && !is_empty(SYS_CONTENT_TYPE))
            if(headers_sent())
                throw new Exception(self::ERR_HEADERS_SENT);
            header('Content-type: '.SYS_CONTENT_TYPE.((defined('SYS_CHARSET') && !is_empty(SYS_CHARSET)) ? '; charset='.SYS_CHARSET : ''));
    }
    
    /**
    * Busca los archivos que deben ser incluidos automaticamente al sistema
    * Estos archivos pueden ser Javascript, CSS o PHP
    * Los archivos que se incluyen automaticamente deben tener '_' como prefijo
    * al nombre del archivo
    * 
    */
    public static function autoIncludeFiles(){
        $TPL_DEF_PATH=Tpl::get(PATH);
        $PATH_CSS=HTTP_CONTENT_TEMPLATES.Tpl::get(ACTIVE)."/css/";
        $PATH_IMG=HTTP_CONTENT_TEMPLATES.Tpl::get(ACTIVE)."/images/";
        $PATH_JS=HTTP_CONTENT_TEMPLATES.Tpl::get(ACTIVE)."/js/";

        /**
         * Handle engine includes
         */
        if(SYS_BIN_AUTO_INCLUDE){
            if(function_exists('scandir')){ // PHP 5 
                $inc=array_merge(
                    @scandir(PATH_ENGINE_BIN),
                    @scandir(PATH_CORE)
                );
            }else{ // PHP 4
                $dir=@opendir(PATH_ENGINE_BIN);
                while (false !== ($file = readdir($dir))) {
                    $inc[] = $file;
                }
            }
            if(!empty($inc)){
                foreach($inc as $i => $file){
                    $_file=explode(".",$file);
                    if(@file_exists(PATH_ENGINE_BIN.$file)){
                        $inc_file=PATH_ENGINE_BIN.$file;
                    }elseif(@file_exists(PATH_CORE.$file)){
                        $inc_file=PATH_CORE.$file;
                    }else{
                        $inc_file=false;
                    }
                    if($inc_file!=false){
                        if(substr($file,0,1)=="_" || $_file[1]=='req'){ // Required file
                            require_once($inc_file);
                            Sys::$PHP_Files[]=$inc_file;
                        }else{ // Normal include
                            if(!SYS_BIN_AUTO_ONLY_REQ){
                                include_once($inc_file);
                                Sys::$PHP_Files[]=$inc_file;
                            }
                        }    
                    }
                }
            }
        }else{
            // Files array to include
            // need to reside in PATH_ENGINE_BIN
            if(!empty(Sys::$Autoload)){
                foreach(Sys::$Autoload as $i => $file){
                    $_file=explode(".",$file);
                    if(substr($file,0,1)=="_" || $_file[1]=='req'){
                        require_once(PATH_ENGINE_BIN.$file)
                            or die("Unable to include ".$file);
                    }else{
                        if(SYS_BIN_AUTO_ONLY_REQ)
                            continue;
                        include_once(PATH_ENGINE_BIN.$file);
                    }
                    Sys::$PHP_Files[]=PATH_ENGINE_BIN.$file;
                }
            }
        }
        
        /**
         * Hande Javascript Files
         */
        if(function_exists('scandir')){ // PHP 5 
            $inc=@scandir($TPL_DEF_PATH."js");
        }else{ // PHP 4
            $dir=@opendir($TPL_DEF_PATH."js");
            while (false !== ($file = readdir($dir))) {
                $inc[] = $file;
            }
        }
        $GLOBALS['SYS_JS']=array(); // Reset
        Sys::$JS_Files=array();
        if(!empty($inc)){
            foreach($inc as $i => $file){
                if(substr($file,0,1)=='_')
                    Sys::$JS_Files[]=$PATH_JS.$file;
            }
        }
        
        /**
         * Hande CSS Files
         */
        if(function_exists('scandir')){ // PHP 5 
            $inc=scandir($TPL_DEF_PATH."css");
        }else{ // PHP 4
            $dir=@opendir($TPL_DEF_PATH."css");
            while (false !== ($file = readdir($dir))) {
                $inc[] = $file;
            }
        }
        $GLOBALS['SYS_CSS']=array(); // Reset
        Sys::$CSS_Files=array();
        if(!empty($inc)){
            foreach($inc as $i => $file){
                if(substr($file,0,1)=='_')
                    Sys::$CSS_Files[]=$PATH_CSS.$file;
            }
        }
        // Theme
        if(Tpl::get(THEME)!=""){
            Sys::$CSS_Files[]=HTTP_CONTENT_TEMPLATES."themes/".Tpl::get(THEME)."theme.css";
        }
        // Auto include the js file for the module
        if(file_exists($TPL_DEF_PATH."css/".DSP_MODULE.".css"))
            Sys::$CSS_Files[]=$PATH_CSS.DSP_MODULE.".css";
  
        // Verificamos si hay un archivo req.ini para el modulo
        if(file_exists(PATH_CONTROLLER_MODULES.DSP_MODULE."/req.ini")){
            $req=@parse_ini_file(PATH_CONTROLLER_MODULES.DSP_MODULE."/req.ini",true);
            $action=DSP_CONTROL;
            if(empty($action))
                $action="default";
            if($req!=false && !empty($req)){
                $rinc=array();
                $req_use_mod=false;
                // Verificamos para la accion actual
                if(!empty($req[$action])){
                    if($req[$action]['use_on_blank']===false || $req[$action]['use_on_blank']===""){
                        Sys::$req_use_on_blank=false;
                        $req_use_mod=true;
                    }
                    if(!empty($req[$action]['js']))
                        foreach($req[$action]['js'] as $i => $file)
                            if(!empty($file))
                                $rinc['js'][]=$PATH_JS.$file;
                    if(!empty($req[$action]['css']))
                        foreach($req[$action]['css'] as $i => $file)
                            if(!empty($file))
                                $rinc['css'][]=$PATH_CSS.$file;
                } 
                // Verificamos para "all"
                if(!empty($req['all'])){
                    if($req['all']['use_on_blank']===false && !$req_use_mod){
                        Sys::$req_use_on_blank=false;
                    }
                    if(!empty($req['all']['js']))
                        foreach($req['all']['js'] as $i => $file)
                            if(!empty($file))
                                $rinc['js'][]=$PATH_JS.$file;
                    if(!empty($req['all']['css']))    
                        foreach($req['all']['css'] as $i => $file)
                            if(!empty($file))
                                $rinc['css'][]=$PATH_CSS.$file;
                }  
                // Agregamos los archivos al registro del sistema
                $_SESSION['sys_loaded_files']=array();
                if(!empty($rinc['js'])){
                    foreach($rinc['js'] as $i => $file){
                        if(!in_array($file,Sys::$JS_Req_Files))
                            Sys::$JS_Req_Files[]=$file;
                        if(in_array($file,Sys::$JS_Files))
                            continue;
                        Sys::$JS_Files[]=$file;
                    }
                }
                $_SESSION['sys_loaded_files']['js']=Sys::$JS_Files;
                if(!empty($rinc['css'])){
                    foreach($rinc['css'] as $i => $file){
                        if(!in_array($file,Sys::$CSS_Req_Files))
                            Sys::$CSS_Req_Files[]=$file;
                        if(in_array($file,Sys::$CSS_Files))
                            continue;
                        Sys::$CSS_Files[]=$file;
                    }
                }
                $_SESSION['sys_loaded_files']['css']=Sys::$CSS_Files;
            }
        }
        // Auto include the js file for the module
        if(file_exists($TPL_DEF_PATH."js/".DSP_MODULE.".js")){   
            Sys::$JS_Files[]=$PATH_JS.DSP_MODULE.".js";
            //Sys::$Module_JS=PATH_JS.DSP_MODULE.".js";
        }
    }
    
    /**
    * Inicia el capturador de buffer de salida
    * 
    */
    public static function recordOutput(){
        ob_start();
    }
    
    /**
    * Resetea el capturador de buffer de salida y 
    * pone el buffer capturado en $slaveOuput
    * 
    */
    public static function resetOutput(){
        self::$slaveOutput=ob_get_clean();
    }
    
    /**
    * Parsea las variables POST y GET para evitar sql injection
    * o html injection en consultas y escrituras a la base de datos
    * 
    */
    public static function secureHttpVars(){
        foreach($_POST as $k => $v){
            if(is_array($v)){
                self::secureArray($_POST[$k]);
            }else{
                $_POST[$k]=htmlspecialchars(Db::_realEscapeString($v),ENT_NOQUOTES);
            }
        }
        foreach($_GET as $k => $v){
            $_GET[$k]=htmlspecialchars(Db::_realEscapeString($v),ENT_NOQUOTES);
        }
        foreach($_REQUEST as $k => $v){
            $_REQUEST[$k]=htmlspecialchars(Db::_realEscapeString($v),ENT_NOQUOTES);
        }
    }
    
    /**
    * Recursiva para secureHttpVars()
    * 
    * @param string $arr
    */
    private static function secureArray(&$arr){
        if(is_array($arr)){
            foreach($arr as $k => $v)
                if(is_array($v))
                    self::secureArray($arr[$k]);
                else
                    $arr[$k]=htmlspecialchars(Db::_realEscapeString($v),ENT_NOQUOTES);
        }else{
            $arr=htmlspecialchars(Db::_realEscapeString($v),ENT_NOQUOTES);
        }
    }
    
    /**
    * Obtiene el subdominio usado
    * 
    */
    public static function getSubDomain(){
        $host=explode('.',$_SERVER['HTTP_HOST']);
        if(count($host)>2){ // NOTA: en caso de que el dominio sea ".com.mx" o se componga de 2 puntos, elevar el limite a 3
            unset($host[0]);
            $i=0;
            $_host=$host;
            $host=array();
            foreach($_host as $c => $val){
                $host[$i]=$val;
                $i++;   
            }
        }
        $server_name=$_SERVER['SERVER_NAME'];
        $server=explode('.',$server_name);
        $subdomain=$server[0];

        if($subdomain=="www" || $subdomain==$host[0] || $subdomain=="localhost" || $subdomain=="127.0.0.1")
            $subdomain="";
    
        return $subdomain;
    }
    
    /**
    * Devuelve la url actual
    * 
    */
    public static function URL() {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }
    
    /**
    * Set timezone in PHP and Database
    * 
    */
    public static function setTimezone(){
        $tz = Sys::get('config')->timezone;
        if(ThisUser::islogged()){
            $utz = ThisUser::get("timezone");
            if(!empty($utz)){
                $tz = $utz;
            }
        }
        if(!in_array($tz,timezone_identifiers_list()))
            $tz = "UTC";
        date_default_timezone_set($tz);
        // Adjust in DB
        //Sys::$Db->setTimezone();
    }
    
    /**
    * Establece el lenguaje del usuario con base en los headers
    * enviados desde su explorador
    * 
    */
    public static function setLang(){
        $langCode = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if(!empty($_REQUEST[LANG_CTRL_VAR]) && file_exists(PATH_ENGINE_LANG.$langCode.'.php')){
            $langCode = $_REQUEST[LANG_CTRL_VAR];
            if(ThisUser::islogged()){
                $User = Usuario::find(ThisUser::get("id_user"));
                $User->lang = $_REQUEST[LANG_CTRL_VAR];
                $User->save();
                ThisUser::set("lang",$_REQUEST[LANG_CTRL_VAR]);
            }
        }else{
            if(ThisUser::islogged() && ThisUser::get("lang") != ""){
                $langCode = ThisUser::get("lang");
            }
        }

        if(file_exists(PATH_ENGINE_LANG.$langCode.'.php')){
            Lang::load($langCode.'.php');
        }else{
            Lang::load(Sys::get('config')->lang_default.'.php');
        }
    }
    
    /**
    * Establece la moneda preferente del usuario. Por defecto intentara establecerla
    * con base en su localizacion geografica
    * 
    */
    public static function setCurrency(){
        if(!Sys::get('config')->db_auto_connect)
            return false;

        $db = Db::getInstance();

        if(!empty($_REQUEST['currency'])){
            $moneda = $db->fetch("SELECT * FROM currency WHERE currency_code = '".$db->escape($_REQUEST['currency'])."' AND active = 1");
            if($moneda->num_rows <= 0)
                return;
            if(ThisUser::isLogged()){
                $User = Usuario::find(ThisUser::get("id_user"));
                $User->currency = $_REQUEST['currency'];
                $User->save();
            }
            $_SESSION['currency'] = $_REQUEST['currency'];   
        }else{
            if(!empty($_SESSION['currency']))
                return;
            if(ThisUser::isLogged() && ThisUser::get("currency") != ""){
                if(ThisUser::get("currency") != "")
                    $_SESSION['currency'] = ThisUser::get("currency");
            }else{
                $geo = $db->fetch("SELECT m.* FROM geolocation INNER JOIN currency m USING(iso_3166_2) WHERE ('".$db->escape(ip2long($_SERVER['REMOTE_ADDR']))."' BETWEEN ip_start AND ip_end) AND m.active = 1");
                if($geo->num_rows > 0){
                    $_SESSION['currency'] = $geo->row['currency_code'];
                }
            }
        }
    }
}

// Register autoload method for class instances
spl_autoload_register(array('Core','autoload'));
