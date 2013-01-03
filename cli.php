<?php
/**
 * Entry point for CLI ONLY
 * NOTE: Controllers for CLI must be located under controller/cli
 * and they can't use any HTML views
 */
if(php_sapi_name() != "cli")
    die("Access denied");
error_reporting(0);
define('SYS_ROOT',dirname(__FILE__)."/");
require_once('engine/lib/core/Core.php');
require_once("engine/conf/definition.php");
Core::$autoload[]=PATH_CORE;
Core::$autoload[]=PATH_ENGINE_MODEL;
Core::$autoload[]=PATH_EXTENSIONS;
Sys::set('config',Config::getInstance());
Sys::get('config')->load("config.php");
Sys::set('page_load_init',microtime(true));
Sys::set('response',Response::getInstance());
Sys::set('layout',Layout::create());
Sys::set('loader',Loader::getInstance());
// Database setup
Db::$host=Sys::get('config')->db_host;
Db::$user=Sys::get('config')->db_user;
Db::$pass=Sys::get('config')->db_pass;
Db::$name=Sys::get('config')->db_name;
Db::$engine=Sys::get('config')->db_engine;
/**
 * Database unique handle connection
 */
if(Sys::get('config')->db_auto_connect){
    Db::connect();
    Sys::$Db = Db::getInstance();
}
Router::parseCliControl();
define('DSP_MODULE',Router::$Control['module']);
define('DSP_CONTROL',Router::$Control['control']);
define('DSP_DIR',Router::$Control['dir']);
define('DSP_FILE',Router::$Control['file']);

require_once(DSP_FILE);
if(!class_exists('ModuleController'))
    die("Error: ModuleController class required");
$methods=get_class_methods('ModuleController');
$run = "";
if(DSP_CONTROL == ""){
    if(in_array("main",$methods))
        $run = "main";
    else
        die("Error: There is no method to run");
}else{
    if(!in_array(DSP_CONTROL,$methods))
        die("Error: '".DSP_CONTROL."' method not found on the controller");
    else
        $run = DSP_CONTROL;
}
$Controller = new ModuleController();

try{
    $Controller->{$run}();
}catch(Exception $e){
    die($e->getMessage());
}