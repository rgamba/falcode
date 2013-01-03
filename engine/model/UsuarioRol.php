<?php
/**
* [Table:usuario_rol]
* UsuarioRol.php
* 
* @package     FALCODE  
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class UsuarioRol extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="usuario_rol";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_usuario_rol";
    /**
    * Prefix
    */
    protected static $prefix="u";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array( 'Usuario' => array( 'table' => 'usuario', 'local_key' => 'id_usuario_rol', 'foreign_key' => 'id_usuario_rol', 'instance' => null, 'rel_type' => Db::REL_ONE_TO_MULTIPLE ) );  
}