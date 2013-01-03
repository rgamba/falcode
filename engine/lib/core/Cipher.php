<?php
/**~engine/lib/Cipher.php
* 
* Cipher
* ---
* Encripcion y desencripcion de datos
* 
* @package      FALCODE
* @version      2.0
* @author       FALCODE
*/
class Cipher{
    public static $cipher=MCRYPT_RIJNDAEL_256;
    public static $key=CRYPT_KEY;
    public static $mode=MCRYPT_MODE_CBC;
    public static $iv=CRYPT_IV;
    
    private function __construct(){
        return false;
    }
    
    /**
    * Encripta los datos
    * 
    * @param mixed $text
    * @return string
    */
    public static function encrypt($text=NULL,$key=NULL,$iv=NULL){
        $key=empty($key) ? self::$key : $key;
        $iv=empty($iv) ? self::$iv : $iv;
        $enc=mcrypt_encrypt(self::$cipher,$key,$text,self::$mode,$iv);
        return base64_encode($enc);
    }
    
    /**
    * Desencripta los datos
    * 
    * @param string $enc
    * @return string
    */
    public static function decrypt($enc=NULL,$key=NULL,$iv=NULL){
        $key=empty($key) ? self::$key : $key;
        $iv=empty($iv) ? self::$iv : $iv;
        $enc=base64_decode($enc);
        return rtrim(mcrypt_decrypt(self::$cipher,$key,$enc,self::$mode,$iv));
    }
}
