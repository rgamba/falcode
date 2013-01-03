<?php
/**~ index.php 
* ---
* index.php
* ---
* Index file.
* 
* No further modifications needed on this file except
* for error reporting and output prevent in case
* of debugging.
* 
* @package  FALCODE
*/
// Check for CLI access
if(php_sapi_name() == "cli"){
    include("cli.php");
    exit;
}
/**
* Set to false for production enviroment
*/
define('SYS_DEBBUGING',true);

/**
* Error reporting
*/
if(SYS_DEBBUGING==true){
    error_reporting(E_CORE_ERROR | E_ERROR | E_PARSE | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
    ini_set('display_errors', '1');
}else{
    error_reporting(0);
    ini_set('display_errors', '0');
}

/**
* Execution time limit
*/
set_time_limit(60);

/**
* Force database setup (uncomment if needed)
*/
//if(!file_exists('install/dbsetup.txt') && is_dir('install'))
//    header('Location: install/');

/**
* DO NOT modify anything below this line
*/
define('SYS_ROOT',dirname(__FILE__)."/");
require_once('engine/lib/core/Core.php'); 
require_once("engine/conf/definition.php");
Core::$autoload[]=PATH_CORE;
Core::$autoload[]=PATH_ENGINE_MODEL;
Core::$autoload[]=PATH_EXTENSIONS;
Sys::set('config',Config::getInstance());
Sys::get('config')->load("config.php");
Sys::set('page_load_init',microtime(true));
/**
 * Database unique handle connection
 */
if(Sys::get('config')->db_auto_connect){
    die();
    Db::connect();
    Sys::$Db = Db::getInstance();
    Sys::set('db',Sys::$Db);
}
/**
 * Session
 */
Session::start();

Sys::set('response',Response::getInstance());
Sys::set('layout',Layout::create());
Sys::set('loader',Loader::getInstance());
Sys::updateFlash();
require_once(PATH_CORE.'init.php');
// Prevent output
if(!SYS_DEBBUGING)
    Core::recordOutput();
// Render view
Sys::get('layout')->output();
// Clear ilegal output
if(!SYS_DEBBUGING)
    Core::resetOutput();
// Send response
Sys::get('response')->setOutput(Sys::get('layout')->output);
Sys::get('response')->sendOutput();

// Clear system messages
if(!Sys::get('module_controller')->isAjaxRequest()){
    Sys::clearErrorMsg();
    Sys::clearInfoMsg();
    Sys::clearFlash();
}