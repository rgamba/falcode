<?php
/**
* [Table:{table}]
* {file_name}.php
* 
* @package     BM
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class {class_name} extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="{table}";
    /**
    * Primary key column name
    */
    protected static $primary_key="{pk}";
    /**
    * Prefix
    */
    protected static $prefix="{prefix}";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array( {fk_model} );  
}