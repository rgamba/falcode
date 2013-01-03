<?php
// TODO
final class MySQL{
    private $con;
    
    public function __construct(){
        
    }
    
    public function connect($host="localhost",$user,$pass="",$dbname=""){
        $this->con = @mysql_connect($host,$user,$pass) or die("Couldn't connect to database");
        if($this->con){
            $this->setCharset();
            $this->selectDb($dbname);
        }
        return $this->con;
    }
    
    public function query($sql){
        return @mysql_query($sql,$this->con);
    }
    
    public function getRow($res){
        return @mysql_fetch_assoc($res);
    }
    
    public function getRows($res){
        $ret = array();
        while($row = $this->getRow()){
            $ret[] = $row;
        }
        return $ret;
    }
    
    public function selectDb($name){
        return @mysql_select_db($name,$this->con) or die("Couldn't select database");
    }
    
    public function setCharset($charset="utf8"){
        return @mysql_query("SET NAMES '".$charset."'",$this->con);
    }
    
    public function startTransaction(){
        return @mysql_query("START TRANSACTION",$this->con);
    }
    
    public function commitTransaction(){
        return @mysql_query("COMMIT",$this->con);
    }
    
    public function rollbackTransaction(){
        return @mysql_query("ROLLBACK",$this->con);
    }
    
    public function escape($str){
        return @mysql_real_escape_string($str,$this->con);
    }
    
    public function affectedRows(){
        $af = @mysql_affected_rows($this->con);
        if($af == -1)
            return false;
        return $af;
    }
    
    public function lastId(){
        return @mysql_insert_id();
    }
    
    public function listTables($database){
        return @mysql_list_tables($database,$this->con);
    }
    
    public function getError(){
        return mysql_error();
    }
    
    public function fieldExists($table,$column){
        $fields = @mysql_query("SHOW COLUMNS FROM ".$this->escape($table));
        $f = array();
        if($fields){
            while ($row = mysql_fetch_assoc($result)) {
                $f[] = $row['Field'];
            }
        }
        return in_array($column,$f);
    }
}
