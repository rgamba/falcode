<?php
/**
* MySQL Database Driver
*/
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
        return mysql_fetch_assoc($res);
    }
    
    public function getRows($res){
        $ret = array();
        while($row = @$this->getRow($res)){
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
        return mysql_real_escape_string($str,$this->con);
    }
    
    public function affectedRows(){
        $af = mysql_affected_rows($this->con);
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
            while ($row = mysql_fetch_assoc($fields)) {
                $f[] = $row['Field'];
            }
        }
        return in_array($column,$f);
    }
    
    public function maxVal($tabla,$columna,$extraSQL=""){
        $tabla=$this->escape($tabla);
        $columna=$this->escape($columna);
        $query=$this->query("SELECT MAX($columna) as max FROM $tabla $extraSQL");
        if($query){
            $max=$this->getRow($query);
            return $max['max'];
        }
    }
    
    public function setTimezone(){
        $now = new DateTime();
        $mins = $now->getOffset() / 60;
        $sgn = ($mins < 0 ? -1 : 1);  
        $mins = abs($mins);  
        $hrs = floor($mins / 60);  
        $mins -= $hrs * 60;
        $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);  
        $this->query("SET time_zone = '$offset'"); 
    }

    // Database manipulation functions

    public function createDatabase($name){
        return mysql_query("CREATE DATABASE $name");
    }

    public function deleteDatabase($name){
        return mysql_query("DROP DATABASE $name");
    }

    public function createTable($name,array $fields,$if_not_exists = false,$engine = "InnoDB"){
        $sql = "CREATE TABLE `$name`(";
        $fields_sql = array();
        $pk = "";
        $unique_key = array();
        $key = array();
        foreach($fields as $i => $f){
            foreach($f as $var => $val)
                $f[strtolower($var)] = $val;

            $f_sql = "";

            if(empty($f['name']))
                return false;
            if(empty($f['type'])){
                $f['type'] = "VARCHAR";
                if(empty($f['size'])){
                    $f['size'] = '255';
                }
            }
            if(!empty($f['size'])){
                $f['type'] = $f['type']."($f[size])";
            }

            $f_sql .= "`$f[name]` $f[type]";
            if($f['signed'] === false){
                $f_sql .= " unsigned";
            }elseif($f['signed'] == true){
                $f_sql .= " signed";
            }

            if($f['not_null'])
                $f_sql .= " NOT NULL";
            if($f['auto_increment'])
                $f_sql .= " AUTO_INCREMENT";
            if(!empty($f['default']))
                $f_sql .= "DEFAULT " . (strtolower($f['default']) == 'null' ? "NULL" : "'$f[default]'");
            if(!empty($f['pk']) || !empty($f['primary_key'])){
                $pk = $f['name'];
            }
            if(!empty($f['unique']))
                $unique_key[$f['unique']] = $f['name'];
            if(!empty($f['key']))
                $key[$f['key']] = $f['name'];

            $fields_sql[] = $f_sql;
        }

        $sql .= implode($fields_sql,", ");
        if(!empty($pk))
            $sql .= ", PRIMARY KEY (`$pk`)";
        if(!empty($unique_key)){
            $sql .= ", UNIQUE KEY (`".implode($unique_key,"`), UNIQUE KEY (`") . "`)";
        }
        if(!empty($key)){
            $sql .= ", KEY (`".implode($unique_key,"`), UNIQUE KEY (`") . "`)";
        }
        $sql .= ") ENGINE=$engine DEFAULT CHARSET=utf8";

        return mysql_query($sql);
    }

    public function deleteTable($name){
        return mysql_query("DROP TABLE `$name`");
    }

    public function addField($table,array $f){
        foreach($f as $var => $val)
            $f[strtolower($var)] = $val;

        $f_sql = "";

        if(empty($f['name']))
            return false;
        if(empty($f['type'])){
            $f['type'] = "VARCHAR";
            if(empty($f['size'])){
                $f['size'] = '255';
            }
        }
        if(!empty($f['size'])){
            $f['type'] = $f['type']."($f[size])";
        }

        $f_sql .= "`$f[name]` $f[type]";
        if($f['signed'] === false){
            $f_sql .= " unsigned";
        }elseif($f['signed'] == true){
            $f_sql .= " signed";
        }

        if($f['not_null'])
            $f_sql .= " NOT NULL";
        if($f['auto_increment'])
            $f_sql .= " AUTO_INCREMENT";
        if(!empty($f['default']))
            $f_sql .= "DEFAULT " . (strtolower($f['default']) == 'null' ? "NULL" : "'$f[default]'");

        $sql = "ALTER TABLE `$table` ADD $f_sql";
        return mysql_query($sql);
    }

    public function addKey($table,$field,$type="INDEX",$name = NULL){
        switch(strtolower($type)){
            case 'primary_key':
            case 'primary key':
                return mysql_query("ALTER TABLE `$table` ADD PRIMARY KEY (`$field`)");
                break;
            case 'unique':
                return mysql_query("ALTER TABLE `$table` ADD UNIQUE (`$field`)");
                break;
            case 'index':
            case 'key':
                return mysql_query("ALTER TABLE `$table` ADD INDEX (`$field`)");
                break;
        }
    }

    public function deleteKey($table,$name = NULL){
        if(strtolower($name) == "primary key" || strtolower($name) == "primary_key")
            return mysql_query("ALTER TABLE `$table` DROP PRIMARY KEY");
        return mysql_query("ALTER TABLE `$table` DROP INDEX (`$name`)");
    }

    public function deleteField($table,$field){
        return mysql_query("ALTER TABLE `$table` DROP `$field`");
    }

    public function modifyField($table,$f){
        foreach($f as $var => $val)
            $f[strtolower($var)] = $val;

        $f_sql = "";

        if(empty($f['name']))
            return false;
        if(empty($f['type'])){
            $f['type'] = "VARCHAR";
            if(empty($f['size'])){
                $f['size'] = '255';
            }
        }
        if(!empty($f['size'])){
            $f['type'] = $f['type']."($f[size])";
        }
        if(empty($f['new_name']))
            $f['new_name'] = $f['name'];

        $f_sql .= "`$f[name]` `$f[new_name]` $f[type]";
        if(@$f['signed'] === false){
            $f_sql .= " unsigned";
        }elseif(@$f['signed'] == true){
            $f_sql .= " signed";
        }

        if(@$f['not_null'])
            $f_sql .= " NOT NULL";
        if(@$f['auto_increment'])
            $f_sql .= " AUTO_INCREMENT";
        if(!empty($f['default']))
            $f_sql .= "DEFAULT " . (strtolower($f['default']) == 'null' ? "NULL" : "'$f[default]'");

        $sql = "ALTER TABLE `$table` CHANGE COLUMN $f_sql";
        return mysql_query($sql);
    }
    
}
