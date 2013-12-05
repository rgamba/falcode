<?php
/**
* [Table:contact]
* Contact.php
* 
* @package     BM
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class Contact extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="contact";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_contact";
    /**
    * Prefix
    */
    protected static $prefix="c";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array(  );  
}