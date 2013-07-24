<?php
/**~engine/conf/config.php
* ---
* config
* ---
* Main system configuration
*/
// General
$config['enable_content_cache'] = false;
$config['cache_lifetime'] = 60;
$config['enable_memcached'] = false;
$config['memcached_servers'] = array(array("localhost", 11211)); // <- CHANGE THIS
$config['rows_per_page'] = 20;
$config['enable_subdomain'] = false;
$config['timezone'] = DEF_TIMEZONE;
$config['http_vars_secure'] = false;
$config['enable_uri_alias'] = true;
$config['tmp_files_lifetime'] = 24; // In hours
$config['cron_token'] = "cronsecure"; // <- CHANGE THIS
$config['base_currency'] = "USD"; 
$config['session_save_on_db'] = false;
$config['session_expire'] = 86000;
$config['session_name'] = "falcode"; // Just alphanumeric characters!
$config['session_domain'] = ".falcode.org";
// Database
$config['db_host'] = "localhost";
$config['db_user'] = "root"; // <- CHANGE THIS
$config['db_pass'] = ""; // <- CHANGE THIS
$config['db_name'] = "falcode"; // <- CHANGE THIS
$config['db_engine'] = "MySQL";
$config['db_auto_connect'] = false;
$config['db_show_errors'] = false;
// Templates & views
$config['tpl_default'] = "default";
$config['tpl_blank'] = "_blank.html";
$config['tpl_default_layout'] = "layout.html";
$config['tpl_title'] = APP_NAME;
$config['tpl_append_title'] = true;
$config['tpl_default_extension'] = "html";
// SMTP Email
$config['mail_host'] = ""; // <- CHANGE THIS
$config['mail_port'] = 587; // <- CHANGE THIS
$config['mail_user'] = ""; // <- CHANGE THIS
$config['mail_pass'] = ""; // <- CHANGE THIS
$config['mail_from'] = 'noreply@yourapp.com'; // <- CHANGE THIS
// Controller
$config['ctrl_default_module'] = "index";
$config['ctrl_enable_mod_rewrite'] = true;
$config['ctrl_controller_file'] = "ModuleController.php";
// Lang
$config['lang_default'] = "en";
// Login
$config['login_encode_pass'] = false;
$config['login_required'] = false; // db_auto_connect MUST be set to true in order for this to work!
$config['login_set_cookie'] = true;
$config['login_cookie_expire_days'] = 30;
$config['login_error_msg'] = "Incorrect login credentials";
$config['login_max_tries'] = "Maximum login attempts reached";
$config['login_attempts'] = 100;
// User system
$config['user_table'] = "user";
$config['user_username_field'] = "username";
$config['user_password_field'] = "pass";
$config['user_lvl_field'] = "rol";
$config['user_login_query'] = "SELECT u.*,ur.name as rol FROM user u INNER JOIN user_role ur USING(id_user_role) WHERE username = '{0}' AND pass = '{1}'";
// PayPal API
/* Uncomment if needed
$config['paypal_user'] = "";
$config['paypal_pwd'] = "";
$config['paypal_signature'] = "";
*/
// Facebook API
/* Uncomment if needed
$config['fb_app_id'] = "";
$config['fb_secret'] = "";
*/




