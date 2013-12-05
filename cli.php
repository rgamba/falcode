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
error_reporting(E_ALL);
chdir(dirname(__FILE__));
define('SYS_ROOT',dirname(__FILE__)."/");
require_once('engine/lib/core/_misc.php');
require_once('engine/lib/core/Core.php');
require_once("engine/conf/definition.php");
ini_set('date.timezone',DEF_TIMEZONE);
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
define('PATH_CONTROLLER_MODULES', PATH_CONTROLLER.'cli/');

// Email server setup
Mail::$host=Sys::get('config')->mail_host;
Mail::$port=Sys::get('config')->mail_port;
Mail::$user=Sys::get('config')->mail_user;
Mail::$pass=Sys::get('config')->mail_pass;
Mail::$from=Sys::get('config')->mail_from;

// Tpl Config
Tpl::set('ACTIVE',Sys::get('config')->tpl_default);
Tpl::set('DEF',Sys::get('config')->tpl_default);
Tpl::set('BLANK',Sys::get('config')->tpl_blank);
Tpl::set('DEF_MAIN_TEMPLATE',Sys::get('config')->tpl_default_layout);
Tpl::set('MAIN_TEMPLATE',Tpl::get('DEF_MAIN_TEMPLATE'));
Tpl::set('PATH',__DIR__.'/'.PATH_CONTENT_TEMPLATES.Tpl::get('ACTIVE')."/"); // Relative path
Tpl::set('PATH_COMMON',__DIR__.'/'.PATH_CONTENT_TEMPLATES.'common/');
Tpl::set('PATH_EMAILS',__DIR__.'/common/email/');

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