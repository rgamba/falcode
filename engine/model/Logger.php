<?php
/**
* [Table:logger]
* Logger.php
* 
* @package     FALCODE  
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class Logger extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="logger";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_logger";
    /**
    * Prefix
    */
    protected static $prefix="l";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}