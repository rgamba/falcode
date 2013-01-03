<?php
class Config{
    private $items=array();
    private static $instance=null;
    
    private function __construct(){
        
    }
    
    public static function getInstance(){
        if(self::$instance == null)
            self::$instance = new Config();
        return self::$instance;
    }
    
    public function __get($key){
        return $this->items[$key];
    }
    
    public function __set($key,$val){
        $this->items[$key] = $val;
    }
    
    public function setItems($array=array()){
        foreach($array as $k => $v)
            $this->items[$k] = $v;
    }
    
    public function load($file){
        require_once(PATH_ENGINE_CONF.$file);
        $this->setItems($config);
    }
}
