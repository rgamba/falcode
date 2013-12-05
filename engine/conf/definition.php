<?php
/**~engine/conf/config.php
* ---
* definition
* ---
* System definition
* 
* @package      FALCODE
* @version      3.0
* @author       FALCODE
*/
/**
 * Application definition
 */
define('APP_NAME','Signer');
define('APP_VER','1.0');

/**
 * Path definition
 */
// View
$port=@$_SERVER['SERVER_PORT']==443 ? 'https' : 'http';
$root_path=$port."://".@$_SERVER['HTTP_HOST'];
$root=$port."://".@$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);

define('HTTP',$root."/");
define('HTTP_CONTENT',HTTP.'content/');
define('HTTP_CONTENT_FILES',HTTP_CONTENT."_files/");
define('HTTP_CONTENT_TMP',HTTP_CONTENT."_tmp/");
define('HTTP_CONTENT_TEMPLATES',HTTP_CONTENT."templates/");

// Model    
define('PATH_SYSTEM', dirname($_SERVER['SCRIPT_FILENAME']));
define('PATH_CACHE',PATH_SYSTEM.'/cache/');
define('PATH_CONTENT',PATH_SYSTEM.'/content/');
define('PATH_CONTENT_TEMPLATES',PATH_CONTENT."templates/");
define('PATH_CONTENT_FILES',PATH_CONTENT.'_files/');
define('PATH_ENGINE',PATH_SYSTEM.'/engine/');
define('PATH_ENGINE_BIN',PATH_ENGINE."model/");
define('PATH_ENGINE_MODEL',PATH_ENGINE."model/");
define('PATH_ENGINE_LIB',PATH_ENGINE."lib/");
define('PATH_CORE',PATH_ENGINE_LIB.'core/');
define('PATH_EXTENSIONS',PATH_ENGINE_LIB.'extensions/');
define('PATH_ENGINE_CONF',PATH_ENGINE."conf/");
define('PATH_ENGINE_MIGRATIONS',PATH_ENGINE_CONF."migration/");
define('PATH_ENGINE_LANG',PATH_ENGINE."conf/lang/");
// Controller 
define('PATH_CONTROLLER',PATH_SYSTEM.'/controller/');

/**
 * System definition
 */
define('DEF_TIMEZONE','America/Mexico_City');
//define('SYS_ROOT',$_SERVER['DOCUMENT_ROOT']);
define('SYS_BIN_AUTO_INCLUDE',true);
define('SYS_BIN_AUTO_ONLY_REQ',true); // Only include required '_' or .req
define('SYS_AUTO_INCLUDE_MOD_JS',true); // Auto include js with name of the module
define('HTTP_VARS_SAFE_MODE',false);

/**
 * Controller
 */
define('CTRL_ERR','_error.html');
define('CTRL_ERR_FILE',PATH_CONTENT_TEMPLATES.CTRL_ERR);
define('CTRL_ROUTE_VAR','_route_');
define('CTRL_CONTROL_VAR','i');

/**
 * Lang
 */
define('LANG_CTRL_VAR','lang');

/**
 * DB prefix
 */
define('SYS_DB_PREFIX','fc_');

define('CRYPTO_KEY','5tgeydhtuh367dheubd74584rud7');
