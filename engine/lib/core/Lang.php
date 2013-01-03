<?php
/**
 * Lang
 */
class Lang{
    private static $dict=array();
    
    public function __construct(){
        return false;
    }

    public static function get(){
        $params = func_get_args();
        $key = $params[0];
        $vars = array_slice($params,1);
        if(!isset(self::$dict[$key]))
            return false;
        $val=self::$dict[$key];
        if(!empty($vars)){
            foreach($vars as $i => $v){
                $val=str_replace('%'.($i+1),$v,$val);
            }
        }
        return $val;
    }
    
    public static function set($key,$val=""){
        self::$dict[$key]=$val;
    }
    
    public static function load($file){
        if(file_exists(PATH_ENGINE_LANG.$file)){
            include_once(PATH_ENGINE_LANG.$file);
            if(!empty($lang)){
                foreach($lang as $k => $v){
                    self::set($k,$v);
                }
            }
        }
    }
    
    public static function getDictionary(){
        return self::$dict;
    }
    
    public function __get($key){
        return self::$dict[$key];
    }
    
    public function item(){
        $args=func_get_args();
        $key=$args[0];
        $vars=array_slice($args,1);
        return self::get($key,$vars);
    }
}

