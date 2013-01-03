<?php
class SiteNode{
    private $map;
    private $name;
    
    public function __construct($name,$desc,$url){
        $this->name=$name;
        $this->map[$name]=array(
            'desc' => $desc,
            'url' => $url
        );
    }
    
    public function setParent($name){
        $this->map[$this->name]['parent']=$name;
    }
    
    public function position($i){
        $this->map[$this->name]['position']=(int)$i;
    }
    
    public function addChild($name,$desc,$url){
        if(empty($this->map[$name])){
            $this->map[$name]=array(
                'parent' => $this->name,
                'desc' => $desc,
                'url' => $url
            );
        }  
    }
    
    public function getMap(){
        return $this->map;
    }
}
