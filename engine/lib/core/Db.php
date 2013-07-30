<?php
/**
* Db
* ---
* Db access layer
* 
* @package     FALCODE  
* @author      FALCODE
* @copyright   $Copyright$
* @version     $Version$
*/
final class Db{
    private static $driver = NULL;
    private static $instance = NULL;
    public static $engine;
    public static $host;
    public static $user;
    public static $pass;
    public static $name;
    const REL_ONE_TO_MULTIPLE=1;
    const REL_MULTIPLE_TO_ONE=0;
    const REL_1_TO_N=1;
    const REL_1_TO_1=0;
    
    public static function connect(){
        // Database setup
        Db::$host=Sys::get('config')->db_host;
        Db::$user=Sys::get('config')->db_user;
        Db::$pass=Sys::get('config')->db_pass;
        Db::$name=Sys::get('config')->db_name;
        Db::$engine=Sys::get('config')->db_engine;

        if(self::$driver == NULL){
            require_once(PATH_CORE.'db_drivers/'.self::$engine.'.php');
            self::$driver = new self::$engine();
            self::$driver->connect(self::$host,self::$user,self::$pass,self::$name); 
        }
    }
    
    public static function getInstance(){
        self::connect();
        if(is_null(self::$instance)){
            self::$instance = new Db();
        }
        return self::$instance;
    }
    
    public static function load(){
        return self::getInstance();
    }
    
    private function __construct(){
        // Private
    }
    
    public function query($sql){
        // Important: this line prevents the user from using the db server date, instead it always uses GMT date
        // remember that datetime is always saved in db as GMT
        $sql = str_replace(array('now()','NOW()'),"'".$this->now()."'",$sql);
        $ret = self::$driver->query($sql);
        if($ret === false)
            $this->handleError($sql);
        return $ret;
    }
    
    public function getRow($res){
        return self::$driver->getRow($res);
    }
    
    public function getRows($res){
        return self::$driver->getRows($res);
    }
    
    public function selectDb($name){
        return self::$driver->selectDb($name);
    }
    
    public function lastId(){
        return self::$driver->lastId();
    }
    
    public function startTransaction(){
        return self::$driver->startTransaction();
    }
    
    public function commitTransaction(){
        return self::$driver->commitTransaction();
    }
    
    public function rollbackTransaction(){
        return self::$driver->rollbackTransaction();
    }
    
    public function escape($str){
        return self::$driver->escape($str);
    }
    
    public function affectedRows(){
        return self::$driver->affectedRows();
    }
    
    public function numRows(){
        return self::$driver->affectedRows();
    }
    
    public function listTables(){
        return self::$driver->listTables();
    }
    
    public function getError(){
        return self::$driver->getError();
    }
    
    public function fieldExists($table,$field){
        return self::$driver->fieldExists($table,$field);
    }
    
    public function maxVal($tabla,$columna,$extraSQL=""){
        return self::$driver->maxVal($tabla,$columna,$extraSQL);
    }
    
    public function fetch($sql){
        $query = $this->query($sql);
        $obj = new stdClass;
        $obj->num_rows = $this->numRows();
        $obj->row = array();
        $obj->rows = array();
        if($obj->num_rows > 0){
            while($r = $this->getRow($query))
                $obj->rows[] = $r;
            reset($obj->rows);
            $obj->row = current($obj->rows);    
        }
        return $obj;
    }
    
    public function getVal($tabla,$columna,$ref,$valorBuscado,$extraSQL='',$join='',$backticks=true){
        $tabla=$this->escape($tabla);
        $columna=$this->escape($columna);
        $ref=$this->escape($ref);
        $valorBuscado=$this->escape($valorBuscado);
        $prefix=substr($tabla,0,1);
        if($backticks)
            $bt = "`";
        else
            $bt = "";
        $sql="SELECT $bt".$valorBuscado."$bt FROM $bt".$tabla."$bt $prefix $join WHERE $bt".$columna."$bt = '".$ref."' $extraSQL";

        $query=$this->query($sql);
        if($this->numRows() > 0){
            $valor=$this->getRow($query);
            return $valor[$valorBuscado];
        }
        return false;
    }
    
    public function exists($tabla,$columna,$ref,$extraSQL='',$join='',$backticks=true){
        $tabla=$this->escape($tabla);
        $columna=$this->escape($columna);
        $ref=$this->escape($ref);
        $prefix=substr($tabla,0,1);
        if($backticks)
            $bt = "`";
        else
            $bt = "";
        $sql="SELECT * FROM $bt".$tabla."$bt $prefix $join WHERE $bt".$columna."$bt = '".$ref."' $extraSQL";

        $query=$this->query($sql);
        return $this->numRows() > 0;
    }
    
    public function insert($tabla,$campos,$extraSQL=''){
        $keys="`".implode("`,`",array_keys($campos))."`";
        $valores=array();
        foreach(array_values($campos) as $val){
            if($val === 0)
                $val = "0";
            // Replace now for GMT now
            if($val == "now()" || $val == "NOW()"){
                $val = $this->now();
            }
            if($val=='null' || $val=='now()'){
                $ct="";
            }else{
                $ct="'";
            }
            // Si empieza con "_", no se agrega comillas simples
            if(substr($val,0,1)=="_"){
                $ct="";
                $val=substr($val,1);
            }
            $valores[]= "$ct".$this->escape($val)."$ct";
        }
        $valores=implode(",",$valores);
        //echo "INSERT INTO $tabla($keys) VALUES($valores) $extraSQL<br>";
        $qry=$this->query("INSERT INTO `$tabla`($keys) VALUES($valores) $extraSQL");
        if($this->affectedRows() === false)
            return false;
        return true;
    }
    
    public function update($tabla,$campos,$extraSQL=''){
        foreach($campos as $key => $val){
            if($val=='null' || $val=='now()'){
                $ct="";
            }else{
                $ct="'";
            }
            // Si empieza con "_", no se agrega comillas simples
            if(substr($val,0,1)=="_"){
                $ct="";
                $val=substr($val,1);
            }
            $set[]="`".$this->escape($key)."` = $ct".$this->escape($val)."$ct";
        }
        $set=implode(",",$set);
        $sql="UPDATE `$tabla` SET $set $extraSQL";
        $res=$this->query($sql);
        return $this->affectedRows() !== false;
    }
    
    public function delete($tabla,$extraSQL){
        if(empty($tabla) || empty($extraSQL))
            return false;
        $table=$this->escape($tabla);
        $sql="DELETE FROM `".$table."` $extraSQL";
        $this->query($sql);
        return $this->affectedRows() > 0;
    }
    
    public function setTimezone(){
        return self::$driver->setTimezone();
    }
    
    public function now(){
        return date('Y-m-d H:i:s', time() - date('Z', time()));
    }

    public function createDatabase($name){
        $ret = self::$driver->createDatabase($name);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function deleteDatabase($name){
        $ret =  self::$driver->deleteDatabase($name);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function createTable($name,$fields){
        $ret =  self::$driver->createTable($name,$fields);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function deleteTable($table){
        $ret =  self::$driver->deleteTable($table);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function addField($table,$field){
        $ret =  self::$driver->addField($table,$field);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function deleteField($table,$field){
        $ret =  self::$driver->deleteField($table,$field);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function modifyField($table,$field){
        $ret =  self::$driver->modifyField($table,$field);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function addKey($table,$field,$type="INDEX",$name = NULL){
        $ret =  self::$driver->addKey($table,$field,$type,$name);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    public function deleteKey($table,$key){
        $ret =  self::$driver->deleteKey($table,$key);
        if($this->affectedRows() === false){
            throw new Exception($this->getError());
        }
        return $ret;
    }

    private function handleError($sql){
        if(Sys::get("config")->db_show_errors){
            echo '<div style="padding: 10px; background: #FDE8E9; margin-bottom: 10px; border: solid 1px #CC0000; color: #CC0000">Error on the query: <br /><span style="font-family: courier">'.$sql.'</span><br />Error: <b>'.self::$driver->getError().'</b></div>';
            //Sys::setErrorMsg(self::$driver->getError());
        }
    }
}
