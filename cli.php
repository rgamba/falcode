#!/usr/bin/php -q
<?php
/**
 * Entry point for CLI ONLY
 * NOTE: Controllers for CLI must be located under controller/cli
 * and they can't use any HTML views
 */
if(php_sapi_name() != "cli")
    die("Access denied");
//error_reporting(0);
error_reporting(E_CORE_ERROR | E_ERROR | E_PARSE | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
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
        die("Error: There is no method to run\n");
}else{
    if(!in_array(DSP_CONTROL,$methods))
        die("Error: '".DSP_CONTROL."' method not found on the controller\n");
    else
        $run = DSP_CONTROL;
}
$Controller = new ModuleController();

try{
    $Controller->{$run}();
}catch(Exception $e){
    die($e->getMessage());
}