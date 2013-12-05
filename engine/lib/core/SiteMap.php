<?php
class SiteMap{
    private static $map=array();
    
    private function __construct(){
        // Not instanciable
    }
    
    public static function addNode($name,$desc,$url){
        if(!empty(self::$map[$name]))
            return false;
        self::$map[$name]=array(
            'desc' => $desc,
            'url' => $url
        );
    }
    
    public static function appendToNode($parent,$name,$desc,$url){
        if(!empty(self::$map[$name]))
            return false;
         self::$map[$name]=array(
            'desc' => $desc,
            'parent' => $parent,
            'url' => $url
        );   
    }
    
    public function append(SiteNode $node){
        $node_map=$node->getMap();
        self::$map=array_merge(self::$map,$node_map);
    }
    
    public static function getNodeChildren($parent){
        $c=array();
        foreach(self::$map as $name => $m){
            if($m['parent']==$parent)
                $c[$name]=$m;
        }
        return empty($c) ? NULL : $c;
    }
    
    public static function findByUrl($url,$ret_name=false){
        foreach(self::$map as $key => $det){
            if($det['url']==$url){
                return ($ret_name) ? $key : $det;
            }
        }
    }
    
    public static function getIndex(){
        $highest=null;
        foreach(self::$map as $name => $m){
            if(empty($m['parent']) && !empty($m['position'])){
                if($m['position']<$highest['position'] || $highest==null){
                    $highest=$m;
                }
            }
        }
        if($highest!=null)
            return $highest;
        foreach(self::$map as $name => $m){
            if(empty($m['parent'])){
                return $m;
            }
        }
    }
    
    public static function getAncestors($name,&$array){
        $m=@self::$map[$name];
        if(empty($m))
            return;
        if(!empty($m['parent'])){
            $array[]=self::$map[$m['parent']];
        }else{
            self::getAncestors($m['parent'],$array);
        }
    }
    
    public static function getTopNodes(){
        $c=array();
        $order=array();
        $i=0;
        foreach(self::$map as $name => $m){
            if(empty($m['parent'])){
                $order[(empty($m['position']) ? $i : $m['position'])]=array('name' => $name,'array' => $m);
                $i++;
            }
        }
        ksort($order);
        foreach($order as $k => $v){
            $c[$v['name']]=$v['array'];
        }
        return empty($c) ? NULL : $c;
    }
}
