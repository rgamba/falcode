<?php
/**~engine/core/init.php
* 
* System Init and Config
* ---
* 
* @package      FALCODE
* @version      2.0
* @author       FALCODE
*/
Sys::get('response')->setHeader("Content-Type: text/html; charset=utf-8");
$BIN_INCLUDE_FILES=array();
$SYS_INCLUDED_MODULES=array();
$SYS_JS=array();

// Template engine setup
Tpl::set('ACTIVE',Sys::get('config')->tpl_default);
Tpl::set('DEF',Sys::get('config')->tpl_default);
Tpl::set('BLANK',Sys::get('config')->tpl_blank);
Tpl::set('DEF_MAIN_TEMPLATE',Sys::get('config')->tpl_default_layout);
Tpl::set('MAIN_TEMPLATE',Tpl::get('DEF_MAIN_TEMPLATE'));
Tpl::set('PAGE_TITLE',Sys::get('config')->tpl_title);
Tpl::set('PATH',PATH_CONTENT_TEMPLATES.Tpl::get('ACTIVE')."/"); // Relative path
Tpl::set('PATH_COMMON',PATH_CONTENT_TEMPLATES.'common/');
Tpl::set('PATH_EMAILS',PATH_CONTENT_TEMPLATES.'common/email/');
Tpl::set('ABS_PATH',HTTP.'/'.PATH_CONTENT_TEMPLATES.Tpl::get('ACTIVE')."/"); // Absolute path
Tpl::set('APPEND_PAGE_TITLE',Sys::get('config')->tpl_append_title);
Tpl::set('META_DESCRIPTION',Sys::get('config')->def_meta_desc);
Tpl::set('META_KEYWORDS',Sys::get('config')->def_meta_keys);

// Email server setup
Mail::$host=Sys::get('config')->mail_host;
Mail::$port=Sys::get('config')->mail_port;
Mail::$user=Sys::get('config')->mail_user;
Mail::$pass=Sys::get('config')->mail_pass;
Mail::$from=Sys::get('config')->mail_from;

if(Sys::get('config')->enable_memcached){
    Sys::$Memcached_servers=Sys::get('config')->memcached_servers;
    if(empty(Sys::$Memcached_servers))
        die("No Memcached servers established");
    Sys::$Memcached=new Memcache;
    if(!Sys::$Memcached)
        die("Couldn't find Memcached extension");
    foreach(Sys::$Memcached_servers as $server)
        Sys::$Memcached->addServer($server[0],$server[1]);
}

/**
* Login fwd
*/
if(!empty($_SESSION['_login_fwd_'])){
    require_once(PATH_CORE.'_misc.php');
    if(ThisUser::islogged()){
        if(!empty($_SESSION['_login_fwd_']['post']))
            $_SESSION['_POST'] = $_SESSION['_login_fwd_']['post'];
        $red=$_SESSION['_login_fwd_']['module'].(!empty($_SESSION['_login_fwd_']['control']) ? '/' . $_SESSION['_login_fwd_']['control'] : '');
        if(!empty($_SESSION['_login_fwd_']['get']))
            $red.="?".http_build_query($_SESSION['_login_fwd_']['get']);
        unset($_SESSION['_login_fwd_']);
        redirect(url($red));
        exit(0);
    }
}

/**
 * Temp POST
 */
if(!empty($_SESSION['_POST'])){
    $_POST=$_SESSION['_POST'];
    foreach($_REQUEST as $k => $v)
        $_REQUEST[$k]=$v;
    unset($_SESSION['_POST']);
}

// Hash to server
if(!empty($_REQUEST['__hash'])){
    $_SERVER['HTTP_REFERER'] .= "#". $_REQUEST['__hash'];
}

/**
* Subdomains
*/
if(Sys::get('config')->enable_subdomain==true){
    define('SUBDOMAIN',Core::getSubDomain());
}else
    define('SUBDOMAIN','');  
define('PATH_CONTROLLER_MODULES',PATH_CONTROLLER.(SUBDOMAIN=="" ? "default/" : SUBDOMAIN."/"));

/**
* Route uri structure:
* [] = optional
* module[.action][/id/][/titulo_/][/var,val/+]
* Ejemplo:
* news.view/1525/titulo_de_la_noticia_/var1,val1/
*/
require_once(PATH_ENGINE_CONF.'map.php');
Router::enableMapping();
Router::parseFriendlyUrl();

/**
 * Controller
 */
Router::parseControl();

define('DSP_MODULE',Router::$Control['module']);
define('DSP_CONTROL',Router::$Control['control']);
define('DSP_DIR',Router::$Control['dir']);
if(!defined('DSP_FILE'))
    define('DSP_FILE',(((DSP_DIR!=false) && file_exists(Router::$Control['file'])) ? Router::$Control['file'] : CTRL_ERR_FILE));
define('DSP_AJAX',isset(Router::$Control['ajax']) && Router::$Control['ajax']==true);
define('DSP_BLANK',isset(Router::$Control['blank']) && Router::$Control['blank']==true);

unset($_ctrl,$_CTRL);
 
// Safe mode para los _POST y _GET
if(Sys::get('config')->http_vars_secure)
    Core::secureHttpVars();

// Handle includes
Core::autoIncludeFiles();

// Access control list
if(Sys::get('config')->login_required===true){
    // Check for cookies to auto login
    if(Sys::get('config')->login_set_cookie && !ThisUser::islogged()){
        if(Auth::hasCookie()){
            try{
                Auth::attemptLogin();
            }catch(Exception $e){
                Auth::logout();
            }
        }
    }
    Sys::set('acl',new Acl());
}

// Ejecutamos archivo autorun del controller
if(@file_exists(PATH_CONTROLLER_MODULES.'autorun.php'))
    @include_once(PATH_CONTROLLER_MODULES.'autorun.php');
  
// Module metadata and sitemap
$modules=scandir(PATH_CONTROLLER_MODULES);
foreach($modules as $m){
    ob_start();
    if(strpos($m,'.')===false){
        @include(PATH_CONTROLLER_MODULES.$m.'/metadata.php');
    }
    ob_get_clean();
}

// Multi lang
Core::setLang();

// Currency
Core::setCurrency();

// FB
/*if(!ThisUser::isLogged() && Sys::get('config')->login_required){
    Auth::initFb();
    $fb_login_url = Sys::get('fb')->getLoginUrl(array(
        'scope' => 'email,user_about_me,user_location,publish_actions'
    ));

    $fb_login_url_home = Sys::get('fb')->getLoginUrl(array(
        'scope' => 'email,user_about_me,user_location,publish_actions',
        'redirect_uri' => url('home')
    ));
}*/

// Timezone
Core::setTimezone();

// Helpers
$dias = array();
for($i = 1; $i <= 31; $i++)
    $dias[]=$i;
$meses = array();
for($i = 1; $i <= 12; $i++)
    $meses[]=$i;
$anos = array();
for($i = date('Y') - 100; $i <= date('Y'); $i++)
    $anos[]=$i;
$anos_futuro = array();
for($i = date('Y'); $i <= date('Y') + 20; $i++)
    $anos_futuro[]=$i;

// Global template vars
TemplateEngine::setGlobals(array(
   'system' => array(
       'now' => date('Y-m-d H:i:s'),
       'get' => $_GET,
       'request' => $_REQUEST,
       'post' => $_POST,
       'session' => $_SESSION,
       'session_id' => session_id(),
       'server' => $_SERVER,
       'const' => get_defined_constants(),
       'user' => ThisUser::getLoginSession(),
       'user_pic' => ThisUser::getPic(),
       'user_rol' => ThisUser::getRoleId(),
       'user_role' => ThisUser::getRoleId(),
       'pathway' => Router::$path,
       'module' => DSP_MODULE,
       'control' => DSP_CONTROL,
       'action' => DSP_CONTROL,
       'path' => array(
           'http' => HTTP
       ),
       'is_logged' => ThisUser::isLogged(),
       'files' => array(
           'css' => Sys::$CSS_Files,
           'js' => Sys::$JS_Files
       ),
       'info' => Sys::getInfoMsg(),
       'error' => Sys::getErrorMsg(),
       'flash' => Sys::getFlash(),
       'dates' => array(
            'months' => $meses,
            'years' => $anos,
            'future_years' => $anos_futuro,
            'days' => $dias
       ),
       'lang' => Lang::getDictionary(),
       'currency' => (empty($_SESSION['currency']) ? Sys::get('config')->base_currency : $_SESSION['currency']),
       'fb' => array(
            'login_url' => empty($fb_login_url) ? '' : $fb_login_url,
            'login_home_url' => empty($fb_login_url_home) ? '' : $fb_login_url_home,
            'logout_url' => empty($fb_logout_url) ? '' : $fb_logout_url
       )
   ),
   'lang' => Lang::getDictionary()  
));

unset($meses,$anos,$anos_futuro,$dias,$fb_login_url,$fb_logout_url);
