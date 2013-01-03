<?php
/**
* [Table:uri]
* Uri.php
* 
* @package     BM
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class Uri extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="uri";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_uri";
    /**
    * Prefix
    */
    protected static $prefix="u";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}