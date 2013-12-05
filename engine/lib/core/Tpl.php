<?php
/**~engine/lib/Tpl.php
* 
* Template Configuration (Static)
* ---
* 
* @package     FALCODE  
* @author      FALCODE
* @copyright   $Copyright$
* @version     $Version$
*/
class Tpl{
	private static $params;
    public static $tabs;
	
	public static function set($key='',$val=''){
		self::$params[$key]=$val;
		return;
	}
	
	public static function get($key=''){
		return empty(self::$params[$key]) ? null : self::$params[$key];
	}
    
    public static function templatePath(){
        return PATH_CONTENT_TEMPLATES.self::$params['ACTIVE'].'/';
    }
    
    public static function htmlPath(){
        return PATH_CONTENT_TEMPLATES.self::$params['ACTIVE'].'/html/';
    }
    
    public static function moduleHtmlPath(){
        $folder = empty(self::$params['HTML_FOLDER']) ? DSP_MODULE : self::$params['HTML_FOLDER'];
        if(!isset(self::$params['ERROR']) || !self::$params['ERROR'])
            return PATH_CONTENT_TEMPLATES.self::$params['ACTIVE'].'/html/'.$folder.'/';
        else
            return PATH_CONTENT_TEMPLATES.self::$params['ACTIVE'].'/html/_errors/';
    }
}