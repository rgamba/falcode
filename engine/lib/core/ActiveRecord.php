<?php
/**
* ActiveRecord
* ---
* Database abstraction layer
* 
* @package     FALCODE  
* @author      FALCODE
* @copyright   $Copyright$
* @version     $Version$
* @uses        Db.php
*/
abstract class ActiveRecord{
    /**
    * Table name
    * MUST be overridden inside the child class declaration
    * @var mixed
    */
    protected static $table_name=null;
    /**
    * Table primary key
    * If not set, it will be id_tablename
    * @var mixed
    */
    protected static $primary_key=null;
    /**
    * Foreign key and relations array declaration
    * Syntax:
    * array( 
    *       'ProductoImagen' => array( 
    *                               'table' => table_name, 
    *                               'local_key' => local_key, 
    *                               'foreign_key' => foreign_key_name, 
    *                               'instance' => null, 
    *                               'rel_type' => [Db::REL_ONE_TO_MULTIPLE | Db::REL_MULTIPLE_TO_ONE]
    * ) );
    * @var mixed
    */
    protected static $fk=array();
    /**
    * Database name (for multi-database systems)
    * 
    * @var mixed
    */
    protected static $db_name;
    /**
    * Table prefix or alias
    * 
    * @var mixed
    */
    protected static $prefix=null;
    /**
    * Relational constraints
    * 
    * @var mixed
    */
    protected $constraint=array();
    /**
    * Recordset current ID
    * 
    * @var mixed
    */
    public $id=null;
    /**
    * Number of rows returned by a query
    * 
    * @var mixed
    */
    public $rows=0;
    
    private $Db;
    private $rs;
    private $sql=null;
    private $cursor=0;
    private $query=array(
        'select' => null,
        'join' => array(),
        'where' => null,
        'group_by' => null,
        'having' => null,
        'limit' => null,
        'order_by' => null
    );
    private $memcached=false;
    private $result;
    
    /**
    * Constructor
    * 
    * @param mixed $id
    * @param mixed $constraint
    * @return ActiveRecord
    */
    public function __construct($id=NULL,$constraint=array()){
        $this->Db=Db::getInstance();
        if(empty(static::$table_name)){
            $class=get_called_class();
            $class[0]=strtolower($class[0]);
            $func=create_function('$c', 'return "_" . strtolower($c[1]);');
            $t_name=preg_replace_callback('/([A-Z])/', $func, $class);
            static::$table_name=$t_name;
        }
        //if(empty(static::$prefix))
        static::$prefix=substr(static::$table_name,0,1);
        if(empty(static::$primary_key))
            static::$primary_key="id_"+static::$table_name;
        if(!empty($id) || isset($id))
            $this->id=$this->Db->escape($id);
        if(!empty($constraint)){
            $this->addConstraint($constraint);
        }
    }
    
    /**
    * Dynamic constructor
    * @args can be:
    * (['all'|'']): will return all records
    * ('last'): will return the last recordset
    * (n[,n[,n[, ... ]]]): will return id inside params
    */
    final public static function find(){
        $params=func_get_args();
        $class=get_called_class();
        $obj=new $class();
        if(empty($params) || $params[0]=="all"){
            // All
            $obj->select();
        }elseif($params[0]=="last"){
            // Last record
            $obj->select();
            while($obj->next()){
                if($obj->Cunter==($obj->rows-1))    
                    break;
            }
        }elseif(count($params)==1 && is_numeric($params[0])){
            // Primary key
            $obj->select($params[0])->first();
        }elseif(count($params)>1){
            $db = Db::getInstance();
            // Primary key, multiple
            foreach($params as $k => $v)
                $params[$k]=$db->escape($v);
            $obj->select(NULL,"WHERE ".static::$prefix.".".static::$primary_key." IN ('".implode("','",$params)."')");
        }else{
            $obj->select(NULL,$params[0]);
        }
        return $obj;
    }
    
    /**
    * Dynamic smart constructor
    * 
    */
    final public static function __callStatic($name,$args){
        $db = Db::getInstance();
        if(substr($name,0,8)!="find_by_")
            trigger_error("Call to invalid method $name on class ".__CLASS__);
        $sel=substr($name,8);
        if(empty($sel)){
            trigger_error("Uncomplete method calling");
        }
        $class=get_called_class();
        $obj=new $class();
        $where=array();
        if(strpos($sel,'_and_')!==false){
            // And arguments
            $sel=explode('_and_',$sel);
            foreach($sel as $i => $s){
                $where[]="$s = '".$db->escape($args[$i])."'";
            }
            $obj->select(NULL,"WHERE ".implode(' AND ',$where));
        }elseif(strpos($sel,'_or_')!==false){
            // Or arguments
            $sel=explode('_or_',$sel);
            foreach($sel as $i => $s){
                $where[]="$s = '".$db->escape($args[$i])."'";
            }
            $obj->select(NULL,"WHERE ".implode(' OR ',$where));
        }else{
            if($sel=="id")
                $sel=static::$primary_key;
            foreach($args as $k => $v)
                $args[$k]=$db->escape($v);
            $arg=implode("' OR $sel = '",$args);
            $obj->select(NULL,"WHERE $sel = '$arg'");
        }
        return $obj;
    }
    
    /**
    * SQL select statement
    * 
    * @param mixed $cols
    */
    final public function columns(){
        $params=func_get_args();
        $cols=$params[0];
        if(empty($cols)) $cols="*";
        if(count($params)>1){
            $rep=array_slice($params,1);
            self::formatString($cols,$rep);
        }
        $this->query['select']=$cols;
        return $this;
    }
    
    /**
    * SQL where statement
    * 
    * @param mixed $sql
    */
    final public function where(){
        $params=func_get_args();
        $sql=$params[0];
        if(!empty($this->id)){
            if(!is_array($sql)){
                $sql="id_producto = ".$this->id." ".$sql;
            }else{
                $sql[]="id_producto = ".$this->id;
            }
        }
        if(substr(ltrim(strtolower($sql)),0,5)=="where"){
            $sql=substr(ltrim($sql),6);
        }
        if(substr(ltrim(strtolower($sql)),0,3)=="and"){
            $sql=substr(ltrim($sql),4);
        }
        if(count($params)>1){
            $rep=array_slice($params,1);
            self::formatString($sql,$rep);
        }
        $this->query['where'][]=$sql;
        return $this;
    }
    
    /**
    * SQL [inner|left] join statement
    * 
    * @param mixed $sql
    * @param mixed $inner_join
    */
    final public function join($sql,$inner_join=true){
        $this->query['join'][]=($inner_join ? 'INNER JOIN ' : 'LEFT JOIN ').$sql;
        return $this;
    }
    
    /**
    * SQL order by statement
    * 
    * @param mixed $sql
    * @param mixed $order
    */
    final public function orderBy($sql,$order="ASC"){
        if(trim(strtolower($sql)) == "rand()")
            $order = "";
        $this->query['order_by']='ORDER BY '.$sql." $order"; 
        return $this;   
    }
    
    /**
    * SQL group by statement
    *  
    * @param mixed $sql
    */
    final public function groupBy($sql){
        $this->query['group_by']='GROUP BY '.$sql;
        return $this;
    }
    
    /**
    * SQL having statement
    * 
    * @param mixed $sql
    */
    final public function having(){
        $params=func_get_args();
        $sql=$params[0];
        if(count($params)>1){
            $rep=array_slice($params,1);
            self::formatString($sql,$rep);
        }
        $this->query['having']='HAVING '.$sql;
        return $this;
    }
    
    /**
    * SQL limit statement (only MySQL)
    * 
    * @param mixed $sql
    */
    final public function limit($sql){
        $this->query['limit']='LIMIT '.$sql;
        return $this;
    }
    
    final public function findById($id){
        $this->where(static::$primary_key." = '{0}'",$id)->execute();
        return $this;
    }
    
    /**
    * Build and execute SQL query
    * 
    */
    final public function execute($make_query=true){
        //$this->memcached=$memcached;
        $wh = "";
        if(empty($this->query))
            $this->query['select']="*";
        else{
            if(is_array(@$this->query['select'])){
                $this->query['select'] = implode(',',$this->query['select']);
            }
        }
        if(!empty($this->query['where'])){
            $wh="WHERE ".implode(' AND ',$this->query['where']);
        }
        if(!empty($this->id) && empty($this->query['where'])){
            $wh="WHERE ".static::$primary_key." = ".$this->id;
        }
        $this->buildConstraintQuery($wh);
        $this->sql=sprintf("SELECT %s FROM `".static::$table_name."` ".static::$prefix." %s %s %s %s %s %s",empty($this->query['select']) ? '*' : $this->query['select'],@implode(' ',$this->query['join']),$wh,@$this->query['group_by'],@$this->query['having'],@$this->query['order_by'],@$this->query['limit']);
        if($make_query){
            $this->result = $this->Db->query($this->sql);
            $this->rows=$this->Db->numRows();
        }
        return $this;
    }

    /**
     * Overload method for select. This one actually especifies the fields to be selected.
     * This is a better version of columns()
     * @param $sel
     */
    private function _select($sel){
        $this->query['select'][] = $sel;
        return $this;
    }
    
    /**
     * Seleccionar registro(s)
     * @param    id: Id del registro a seleccionar
     * @param     extraSQL: Sentencias SQL extras
     * @param     join: Joins de tablas (INNER, LEFT...)
     */
    final public function select($id=NULL,$extraSQL=NULL,$join=NULL,$fields="*"){
        if(!is_null($id) && !is_numeric($id))
            return $this->_select($id);
        if(empty($fields))
            $fields="*";
        $this->buildConstraintQuery($extraSQL);
        if(!empty($id) || isset($id))
            $this->id=$id;
        $where=null;
        if(!empty($this->id)){
            $where="WHERE ".static::$prefix.".".static::$primary_key." = ".$this->id;
            $extraSQL = str_replace("WHERE","AND",$extraSQL);
        }
        if(!empty($extraSQL)){
            if(!empty($where)){
                $where.=" $extraSQL";
            }else{
                $where.=$extraSQL;
            }
        }
        // MS SQL Server no soporta Limit
        $mlimit=array();
        $regex='/LIMIT\s+([0-9]+)(\s+)?(,(\s+)?([0-9]+))?/i';
        preg_match($regex,$extraSQL,$mlimit);
        if(Db::$engine != "MySQL" && !empty($mlimit)){
            $where=preg_replace($regex,'',$extraSQL);
            if(!empty($mlimit[5])){
                // Tiene dos limites
                $n1=$mlimit[1]+1;
                $n2=$mlimit[1]+$mlimit[5];
                $this->sql="
                    SELECT * FROM (
                        SELECT $fields,ROW_NUMBER() 
                            OVER(ORDER BY p.id_producto) as seq 
                        FROM producto p $join $where
                    )
                    as t1
                    WHERE t1.seq BETWEEN $n1 AND $n2";
            }else{
                // Un limite
                $this->sql="SELECT TOP ".$mlimit[1]." $fields FROM ".static::$table_name." ".static::$prefix." $join $where";
            }
        }else{
            $this->sql="SELECT $fields FROM ".static::$table_name." ".static::$prefix." $join $where";
        }
        $this->result = $this->Db->query($this->sql);
        $this->rows = $this->Db->numRows();
        return $this;
    }
    
    /**
     * Avanza un registro en la consulta
     */
    final public function next($walk=true,$use_fks=true){
        if($walk){
            $this->rs = $this->Db->getRow($this->result);
            $this->cursor++;
            if(!$this->rs)
                return $this->rs;
        }
        $fks=array();
        if(!empty(static::$fk)){
            foreach(static::$fk as $key => $fk){
                if(!empty($this->constraint)){
                    // Evitamos recursion inifinita
                    if($this->constraint['table']==$fk['table']){
                        continue;
                    }
                }
                $fks[$key]=&$this->{$key};
            }
        }
        //$fks['Counter']=$this->counter;
        $this->id=@$this->rs[static::$primary_key];
        return ($use_fks) ? array_merge($this->rs,$fks) : $this->rs;
    }
    
    /**
     * Revuelve un arreglo del recordset actual
     */
    final public function recordset(){
        return $this->next(($this->rs==null ? true : false));
    }
    
    /**
    * Igual que recordset() pero devuelve el objeto
    * 
    */
    final public function first(){
        $this->next(($this->rs==null ? true : false));
        return $this;    
    }
    
    /**
     * Devuelve un arreglo con todos los registros
     * de la consulta
     */
    final public function resultArray(){
        return $this->Db->getRows($this->result);
    }
    
    /**
     * Devuelve el valor de la variable en el
     * registro acutal
     * @param    key: Nombre del campo
     */
    final public function &__get($key=NULL){
        if(empty($key)){
            return false;
        }
        if($key=="Counter")
            return $this->cursor;
        if($key=="Sql")
            return $this->sql;
        
        if(count(static::$fk)>0){
            if(!empty(static::$fk[$key])){
                if(!empty($this->constraint)){
                    if($this->constraint['table']==static::$fk[$key]['table']){
                        trigger_error("Children object can't make direct reference to parent.");
                    }
                }
                $fk=static::$fk[$key];
                if(is_null($fk['instance'])){
                    $constraint=array(
                        'local_key' => $fk['foreign_key'],
                        'value' => @$this->rs[$fk['local_key']],
                        'table' => static::$table_name
                    );
                    $fk['instance']=new $key(NULL,$constraint);
                    $fk['instance']->select();
                    if($fk['rel_type']==Db::REL_MULTIPLE_TO_ONE){
                        $fk['instance']->next();
                    }
                }
                return $fk['instance'];
            }
        }
        return $this->rs[$key];
    }
    
    /**
     * Establece el valor de un campo
     * @param    key: Nombre del campo
     * @param     val: Valor del campo
     */
    final public function __set($key=NULL,$val=NULL){
        if(empty($key))
            return false;
        if($key==static::$primary_key && $val=="")
            return;
        if($key=="Sql"){
            $this->sql=$val;
            return;
        }
        if($this->Db->fieldExists(static::$table_name,$key)){
            $this->rs[$key]=$val;
            return true;
        }
        return false;
    }
    
    /**
    * Devuelve true si el campo tiene algun valor
    * 
    * @param mixed $name
    * @return mixed
    */
    final public function __isset($key){
        return isset($this->rs[$key]);
    }
    
    /**
    * Borra un campo
    * 
    * @param mixed $key
    */
    final public function __unset($key){
        unset($this->rs[$key]);
    }
    
    /**
    * Cuando llamamos al objeto de la clase como funcion
    * 
    * @example echo $instance("ORDER BY name DESC")->name
    * @return object this
    */
    public function __invoke(){
        $args=func_get_args();
        if(empty($args)){
            $this->select();
        }else{
            if(is_string($args[0])){
                // extraSQL
                $this->select(NULL,$args[0],$args[1],$args[2]);
            }else{
                // With id
                $this->select($args[0],$args[1],$args[2],$args[3]);
            }
        }
        return $this;
    }
    
    /**
     * Llena el recordset con los datos de un arreglo
     * (_POST)
     * @param    post: Arreglo
     * @example array('campo' => 'valor', 'campo2' => 'otro')
     */
    public function populate($post=NULL){
        foreach($post as $key => $val){
            try{
                $this->__set($key,$val);
            }catch(Exception $e){
                return $e; 
            }
        }
        return $this;
    }
    
    /**
     * Guarda los valores del recordset en base de datos
     * Si no existe, crea un nuevo registro
     */
    final public function save(){
        if(empty($this->rs))
            return false;
        $id=empty($this->rs[static::$primary_key]) ? $this->id : $this->rs[static::$primary_key];
        $campos=$this->rs;
        if($this->Db->exists(static::$table_name,static::$primary_key,$id)){
            foreach($campos as $k => $v){
                if(!$this->Db->fieldExists(static::$table_name,$k))
                    unset($campos[$k]);
            }
            unset($campos[static::$primary_key]);
            return $this->Db->update(static::$table_name,$campos,"WHERE ".static::$primary_key." = $id");    
        }else{
            if($this->Db->insert(static::$table_name,$campos) != false){
                $this->rs[static::$primary_key] = $this->Db->lastId();
                $this->id = $this->rs[static::$primary_key];
                return $this->id;
            }
            return false;
        }
    }
    
    /**
     * Elimina el recordset actual de la base de datos
     */
    final public function delete(){
        $id=empty($this->rs[static::$primary_key]) ? $this->id : $this->rs[static::$primary_key];
        if($this->Db->delete(static::$table_name,"WHERE ".static::$primary_key." = $id")){
            $this->clear();
            return true;
        }
        return false;
    }
    
    /**
     * Libera los resultados del recordset actual
     */
    final public function clear(){
        $this->rs=array();
        $this->id=NULL;
        $this->rows=0;
        $this->sql=NULL;
        $this->cursor=0;
        $this->query=array();
        $this->result=NULL;
        return $this;
    }
    
    /**
    * Get the number of affected rows in previous query
    * 
    */
    final public function affectedRows(){
        return $this->Db->affectedRows();
    }
    
    /**
    * Get the number of affected rows in previous query
    * 
    */
    final public function lastQueryFailed(){
        return $this->Db->affectedRows() === false;
    }
    
    /**
    * Get the last error
    * 
    */
    final public function getError(){
        return $this->Db->getError();
    }
    
    
    private function addConstraint($constraint=array()){
        if(!empty($constraint)){
            $this->constraint=array(
                'table' => $constraint['table'],
                'local_key' => $constraint['local_key'],
                'foreign_key' => @$constraint['foreign_key'],
                'value' => $constraint['value']
            );
        }
    }
    
    private function buildConstraintQuery(&$extraSQL){
        if(empty($this->constraint))
            return false;
        $x=sprintf("WHERE %s = '%s'",$this->constraint['local_key'],$this->constraint['value']);
        $extraSQL=sprintf("%s %s",$x,str_replace('WHERE','AND',$extraSQL));    
    }
    
    private static function formatString(&$str,$rep){
        $db = Db::getInstance();
        $matches=array();
        preg_match_all('/{([0-9]+)}/',$str,$matches);
        if(!empty($matches[1])){
            foreach($matches[1] as $i => $index){
                $str=str_replace($matches[0][$i],$db->escape($rep[$index]),$str);
            }
        }    
    }
}