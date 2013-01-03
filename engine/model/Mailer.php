<?php
/**
* [Table:mailer]
* Mailer.php
* 
* @package     BM
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class Mailer extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="mailer";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_mailer";
    /**
    * Prefix
    */
    protected static $prefix="m";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}