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
    public static $mode=MCRYPT_MODE_CBC;
    
    private function __construct(){
        return false;
    }
    
    /**
    * Encripta los datos
    * 
    * @param mixed $text
    * @return string
    */
    public static function encrypt($text=NULL,$key=NULL){
        $key_size = mcrypt_get_key_size(self::$cipher, self::$mode);
        $iv_size = mcrypt_get_iv_size(self::$cipher, self::$mode);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $key = substr(hash("sha256",$key),0,$key_size);
        $enc=@mcrypt_encrypt(self::$cipher,$key,$text,self::$mode,$iv);
        return base64_encode($iv.$enc);
    }
    
    /**
    * Desencripta los datos
    * 
    * @param string $enc
    * @return string
    */
    public static function decrypt($enc=NULL,$key=NULL){
        $key_size = mcrypt_get_key_size(self::$cipher, self::$mode);
        $iv_size = mcrypt_get_iv_size(self::$cipher, self::$mode);
        $key = substr(hash("sha256",$key),0,$key_size);
        $enc=base64_decode($enc);
        $iv = substr($enc, 0, $iv_size);
        $enc = substr($enc, $iv_size);
        return @mcrypt_decrypt(self::$cipher,$key,$enc,self::$mode,$iv);
    }
}
