<?php
/**
* [Table:usuario_permiso]
* UsuarioPermiso.php
* 
* @package     FALCODE  
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class UsuarioPermiso extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="usuario_permiso";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_usuario_permiso";
    /**
    * Prefix
    */
    protected static $prefix="u";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}