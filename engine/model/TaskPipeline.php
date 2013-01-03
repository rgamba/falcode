<?php
/**
* [Table:task_pipeline]
* TaskPipeline.php
* 
* @package     BM
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class TaskPipeline extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="task_pipeline";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_task_pipeline";
    /**
    * Prefix
    */
    protected static $prefix="t";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}