<?php
/**
* [Table:usuario_config]
* UsuarioConfig.php
* 
* @package     FALCODE  
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class UsuarioConfig extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="usuario_config";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_usuario_config";
    /**
    * Prefix
    */
    protected static $prefix="u";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}