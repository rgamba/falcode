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
class UserPermission extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="user_permission";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_user_permission";
    /**
    * Prefix
    */
    protected static $prefix="u";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}