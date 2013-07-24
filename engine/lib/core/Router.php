<?php
/**~engine/lib/Router.php
* 
* Router (Singleton - No instanciable)
* ---
*
* @package      FALCODE
* @version      3.0
* @author       FALCODE
*/
class Router{
    const CONTROL_VAR='i';
    const CONTROL_SEP='/';
    const ROUTE_VAR='_route_';

    public static $Control=array();
    public static $module=NULL;
    public static $action=NULL;
    private static $control=NULL;
    private static $defControl=NULL;
    public static $dspFile=NULL;
    private static $mapRegistry=array();
    public static $path=array();

    // Clase no instanciable
    private function __construct(){
        return false;
    }

    /**
    * Genera el control con base en los POST o GET enviados
    * desde el cliente
    *
    */
    public static function parseControl(){
        if(!empty($_POST[self::CONTROL_VAR])){
            self::$control=$_POST[self::CONTROL_VAR];
            unset($_POST[self::CONTROL_VAR]);
        }elseif(!empty($_GET[self::CONTROL_VAR])){
            self::$control=$_GET[self::CONTROL_VAR];
            unset($_GET[self::CONTROL_VAR]);
        }else{
            self::$control=Sys::get('config')->ctrl_default_module;
        }
        unset($_REQUEST[self::CONTROL_VAR]);
        self::$control=explode(self::CONTROL_SEP,self::$control);
        // URL alias
        self::uriAlias();
        $dspFile=file_exists(PATH_CONTROLLER_MODULES.self::$control[0].'/'.Sys::get('config')->ctrl_controller_file)
            ? Sys::get('config')->ctrl_controller_file
            : NULL;
        self::$dspFile=$dspFile;
        self::$defControl=explode(self::CONTROL_SEP,Sys::get('config')->ctrl_controller_file);
        self::$Control=array(
            'module' => self::$control[0],
            'control' => (empty(self::$control[1]) ? self::$defControl[1] : self::$control[1]),
            // Execution directory
            'dir' => (is_dir(PATH_CONTROLLER_MODULES.self::$control[0]) ? PATH_CONTROLLER_MODULES.self::$control[0] : false),
            // Executable file
            'file' => (PATH_CONTROLLER_MODULES.self::$control[0]."/".$dspFile),
            // Pathway
            'path' => $_GET['_path_'],
            // Hash
            'hash' => $_GET['_hash_']
        );
        unset($_GET['_path_'],$_GET['_hash_']);
        if(substr(self::$Control['control'],-3)=='ajx'){
            self::$Control['control']=substr(self::$Control['control'],0,-4);
            self::$Control['ajax']=true;
        }
        if(substr(self::$Control['control'],-3)=='bnk'){
            self::$Control['control']=substr(self::$Control['control'],0,-4);
            self::$Control['blank']=true;
        }
    }

    public static function parseCliControl(){
        $dspFile=Sys::get('config')->ctrl_controller_file;
        self::$Control=array(
            'module' => $_SERVER['argv'][1],
            'control' => $_SERVER['argv'][2],
            // Execution directory
            'dir' => 'controller/cli/'.$_SERVER['argv'][1],
            // Executable file
            'file' => ('controller/cli/'.$_SERVER['argv'][1]."/".$dspFile)
        );
    }
    
    public static function uriAlias(){
        if(Sys::get('config')->enable_uri_alias == false OR !Sys::get('config')->db_auto_connect)
            return;
        
        if(!empty(self::$control[1]))
            return;
        // Check if there is a module with the specified name
        if(is_dir(PATH_CONTROLLER_MODULES.self::$control[0]))
            return;
        // If nor a directory, check if we have an URI alias
        if(!class_exists('Uri'))
            return;
        $complete = substr($_SERVER['REQUEST_URI'],1);
        $complete = explode('?',$complete);
        $complete = explode('#',$complete[0]);
        $complete = $complete[0];
        if(substr($complete,-1) == "/")
            $complete = substr($complete,0,-1);

        $Uri = new Uri();
        $Uri->select(NULL,"WHERE uri = '".Sys::get('db')->escape(self::$control[0])."' OR uri = '".Sys::get('db')->escape($complete)."'");

        if($Uri->rows<=0)
            return;
        $Uri->next();
        $uri = explode('?',$Uri->redirect);
        self::$control = explode(self::CONTROL_SEP,$uri[0]);
        $get_vars = $uri[1];
        if(!empty($get_vars)){
            $gvars = array();
            parse_str($get_vars,$gvars);
            foreach($gvars as $k => $v){
                $_GET[$k] = $v; 
                $_REQUEST[$k] = $v;   
            }
        }
    }

    /**
    * Transforma la URL amigable en un formato reconocible por el sistema
    *
    */
    public static function parseFriendlyUrl(){
        if(Sys::get('config')->ctrl_enable_mod_rewrite){
            $route=$_GET[self::ROUTE_VAR];
            list($route,$getvars)=explode('?',$route,2); // Httpquery vars
            // Parte get standard
            if(!empty($getvars)){
                $gv=array();
                parse_str($getvars,$gv);
                if(count($gv)>0){
                    foreach($gv as $k => $v){
                        if(isset($_GET[$k]) && is_array($_GET[$k])){
                            $_GET[$k]=array_merge($v,$_GET[$k]);
                        }else{
                            $_GET[$k]=$v;
                            $_REQUEST[$k]=$v;
                        }
                    }
                }
            }
            if(!empty($route)){
                //list($route,$hash)=explode('#',$route,2); // Hash vars
                // Parte get tipo FW
                $rp=explode("/",$route);
                if(!empty($rp)){
                    $path=array();
                    $has_id = false;
                    foreach($rp as $i => $p){
                        if(empty($p))
                            continue;
                        if($i==0){
                            // Main control module/section
                            $c=explode(".",$p);
                            $_GET[self::CONTROL_VAR]=$c[0].(!empty($c[1]) ? "/{$c[1]}" : '');
                            $_REQUEST[self::CONTROL_VAR]=$c[0].(!empty($c[1]) ? "/{$c[1]}" : '');
                            continue;
                        }
                        if(strpos($p,",")===false){
                            // Pathway, title o id
                            if(is_numeric($p) && !$has_id){
                                // ID
                                $_GET['id'] = $p;
                                $_REQUEST['id'] = $p;
                                $has_id = true;
                                continue;
                            }
                            if(substr($p,strlen($p)-1,1)=="_"){
                                // Title
                                $_GET['title']=substr($p,0,strlen($p)-1);
                                continue;
                            }
                            $path[]=$p;
                            continue;
                        }else{
                            // Extra variables
                            $kv=explode(",",$p);
                            $_GET[$kv[0]]=$kv[1];
                            $_REQUEST[$kv[0]]=$kv[1];
                        }
                    }
                    if(count($path)>0){
                        $_GET['_path_']=$path;
                        self::$path=$path;
                    }
                }
                // Hash
                if(!empty($hash)){
                    parse_str($hash,$_GET['_hash_']);
                }
            }
            unset($_GET[self::ROUTE_VAR],$_REQUEST[self::ROUTE_VAR]);
        }else{
            if(!empty($_REQUEST['_path']) && is_array($_REQUEST['_path'])){
                $_GET['_path_']=$_REQUEST['_path'];
                self::$path=$_REQUEST['_path'];
            }
        }
    }

    /**
    * Establece una ruta 'alias' para acceder a un determinado modulo y acción !PENDIENTE
    *
    * @param mixed $alias La ruta a la que debe responder la petición
    * @param mixed $mod_act Arreglo que contiene como indice 0 el modulo y opcionalmente 1: la accion o control
    */
    public static function map($find,$route,$final=false){
        self::$mapRegistry[]=array(
            'find' => $find,
            'route' => $route,
            'final' => $final
        );
    }

    /**
    * Funcion para habilitar el mapeo de modulos y acciones establecidos
    * en el arreglo mapRegistry
    *
    */
    public static function enableMapping(){
        if(empty(self::$mapRegistry))
            return;
        $uri=$_GET[self::ROUTE_VAR];
        foreach(self::$mapRegistry as $i => $det){
            $find=str_replace(
                array(':number',':alpha'),
                array('([0-9]+)','([a-zA-Z0-9_\-]+)'),
                $det['find']
            );
            $regex='/'.str_replace('/','\\/',addslashes($find)).'/';
            $found=preg_replace($regex,$det['route'],$uri);
            if($uri!=$found){
                $uri=$found;
                if($det['final']==true)
                    break;
            }
        }
        if($uri!=$_GET[self::ROUTE_VAR])
            $_GET[self::ROUTE_VAR]=$uri;
    }

    /**
    * Funcion para generar ligas
    * @param $url string uri en formato normal con http query
    * @example module/action?var=val&var2=val2
    * @return string formateada con url absoluta
    * en el caso de tener ENABLE_MODREWRITE, el formato sera SEL
    * @author Ricardo
    */
    public static function url($url=NULL){
        return url($url);
    }
}
