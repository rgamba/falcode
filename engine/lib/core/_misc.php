<?php
/**~engine/lib/_misc.php
* ---
* Miscelaneous
* ---
* System repository functions
* 
* @package      FALCODE
*/
/**
* Include file and retrieve it's output without printing it
* in the output buffer
* 
* @param mixed $file
* @return string
*/
function get_include($file){
	if(@file_exists($file)){
		ob_start();
		include_once($file);
		return ob_get_clean();
	}
	return false;
}

/**
 * Redirecciona el navegador
 */
function redirect($url,$poblarGet=false,$poblarPost=false,$post=NULL){
	if($poblarGet){
		// Creamos el GET string
		$url.="?".http_build_query($_GET);
	}
	if($poblarPost){
		// Damos fwd al post
		$_SESSION['_POST']=($post!=NULL) ? $post : $_POST;
	}
    if (!headers_sent()){    
        header('Location: '.$url); exit;
    }else{
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
		// Si tenemos JS desactivado
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>'; exit;
    }
}

/**
 * Devuelve el arreglo de la variable _GET
 * @param method 	get=querystring post=formulario
 * @param except	arreglo de las variables a excluir
 * @param return 	tipo de retorno httpquery=devuelve variables en tipo
 * http query, 'form' devuelve las variables como inputs para formularios
 */
function get($method='get',$except=array(),$return='httpquery',$no_empty=true){
	$get=$_GET;
	$request=$get;
	$post=$_POST;
    foreach($post as $k => $v)
        $request[$k]=$v;
    if(sizeof($except)>0){
	    foreach($except as $i => $val){
		    unset($get[$val]);
		    unset($request[$val]);
		    unset($post[$val]);
	    }
    }
    if($no_empty){
        foreach(${$method} as $k => $v)
            if($v=="")
                unset(${$method}[$k]);
    }
	if($method=='get'){
		$ret=http_build_query($get);
		return (!empty($ret)) ? '?'.$ret : '';
	}elseif($method=='request'){
		$ret=http_build_query($request);
		return (!empty($ret)) ? '?'.$ret : '';
	}elseif($method=='post'){
		$ret='';
		if($return == 'httpquery'){
			$ret=http_build_query($post);
			return $ret;
		}
		foreach($post as $i => $val){
			$ret.='<input type="hidden" name="'.$i.'" id="'.$i.'" value="'.$val.'" />';
		}
		return $ret;
	}
}

/**
 * Funcion usada por el cliente http
 */
function not_null($value) {
	if (is_array($value)) {
		if (sizeof($value) > 0) {
			return true;
		} else {
			return false;
		}
	} else {
		if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Funcion alterna a empty()
 */
function is_empty($var){
	if(!isset($var))
		return false;
	if($var=='')
		return false;
	if($var==0)
		return false;
	if(is_array($var) && sizeof($var)==0)
		return false;
	return true;
}

/**
 * Dinamycally generate URLs
 * @param $url string uri en formato normal con http query
 * @example module/action?var=val&var2=val2
 * @return string formateada con url absoluta
 * en el caso de tener ENABLE_MODREWRITE, el formato sera SEL
 */
function url($url='',$subdomain=NULL,$port=NULL){
    $path_http = HTTP;
    $url_prefix = explode(':',$url,2);
    if(count($url_prefix) > 1){
        $path_http = str_replace('http',$url_prefix[0],$path_http);
        $url = $url_prefix[1];
    }
    if(!is_null($port)){
        $seg = explode('/',$path_http);
        $seg[2] .= ":$port";
        $path_http = implode('/',$seg);
    }
    if(!is_null($subdomain)){
        $seg = explode('/',$path_http);
        $seg[2] = $subdomain.".".$seg[2];
        $path_http = implode('/',$seg);
    }
	$link=array();
	$links_pat="/(([a-zA-Z0-9_]+)(\/([a-zA-Z0-9_]+))?(\?(.*))?)/";
	preg_match($links_pat,$url,$link);
	if(empty($link))
		return false; // No tenemos liga
	$qry=array();
    $path = array();
	$ctrl=array();
	$ctrl=@array($link[2],$link[4]); // act/sec
	if(!empty($link[6])){
        $link[6]=str_replace('&amp;','&',$link[6]);
		$kvp=explode("&",$link[6]);
		foreach($kvp as $j => $kv){
			$pair=explode("=",$kv);
            if($pair[0]=="_path"){
                $path[] = $pair[1];
                continue;
            }
			@$qry[$pair[0]]=$pair[1];
		}
	}
	$link=array(
		'code'	=> $link[0], // Code to replace
		'ctrl'	=> $ctrl,
		'get'	=> $qry,
        'path'  => $path
	);
    
    // Check URI alias
    if(Sys::get('config')->enable_uri_alias == true){
        $Load =& Loader::getInstance();
        $Load->helper('uri');
        $key = uri_find_key($url);
        if(!empty($key))
            return $path_http.$key.'/';
    }
    
	//if(LINK_PREPEND_URL){
		$replace=$path_http; // Base host url
    //}
	if(Sys::get('config')->ctrl_enable_mod_rewrite==true){
		$replace.=$link['ctrl'][0].(!empty($link['ctrl'][1]) ? ".".$link['ctrl'][1] : '')."/";
		if(!empty($link['get'])){
			// Basic replacings
			if(!empty($link['get']['id'])){
				$replace.=$link['get']['id']."/";
				unset($link['get']['id']);
			}
            if(!empty($link['path'])){
                foreach($link['path'] as $p)
                    $replace.=urlencode($p) . "/";
            }
			foreach($link['get'] as $key => $val){
				if(!empty($key))
					$replace.=urlencode($key).",".urlencode($val)."/";
			}
		}else{
            if(!empty($link['path'])){
                foreach($link['path'] as $p)
                    $replace.=urlencode($p) . "/";
            }
        }
	}else{
		$replace.="?".Router::CONTROL_VAR."=".$link['ctrl'][0];
		$replace.=(!empty($link['ctrl'][1]) ? Router::CONTROL_SEP.$link['ctrl'][1] : '')."&".http_build_query($link['get']);
	}
    if(substr($replace,-1,1) == "&")
        $replace = substr($replace,0,-1);
	return $replace;
}

/**
* Funcion similar a mysql_real_escape_string() para usar con conexiones
* a Ms SQL Server
* 
* @param mixed $string
* @return mixed
*/
function mssql_real_escape_string($string=NULL){
    return str_replace("'","`",$string);
}

/**
* Devuelve un listado de tablas de la base de datos actual
* Unicamente para conexiones a MS SQL Server
* 
*/
function mssql_list_tables($dbname=NULL){
    $query=mssql_query("SELECT * FROM $dbname.INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG = '$dbname'");
    if(mssql_num_rows($query)>0){
        while($row=mssql_fetch_array($query)){
            $table[]=$row['TABLE_NAME'];
        }
        return $table;
    }
    return false;
}

/**
* Devuelve la descripcion del campo
* Unicamente funciona para conexiones a MS SQL Server
* 
* @param mixed $field
* @param mixed $table
* @return mixed
*/
function mssql_field_desc($field=NULL,$table=NULL){
    $sql="
        SELECT
            [table] = OBJECT_NAME(c.object_id), 
            [column] = c.name, 
            [description] = ex.value
        FROM 
            sys.columns c 
        INNER JOIN 
            sys.extended_properties ex
        ON 
            ex.major_id = c.object_id
            and ex.minor_id = c.column_id
            and ex.name = 'MS_Description'
        WHERE
            table = '$table'
        AND
            column = '$field'
    ";
    $query=mssql_query($sql);
    if(mssql_num_rows($query)>0){
        $row=mssql_fetch_array($query);
        return $row['description'];
    }
    return false;
}

/**
* Devuelve el nombre de la columna asignada como Primary Key
* de la tabla
* 
* @param mixed $table
* @param mixed $db
*/
function table_primary_key($table=NULL,$db=NULL){
    $PK=NULL;
    if(DB_ENGINE==DB_ENGINE_MYSQL || !defined('DB_ENGINE')){
        // MySQL
        $p_query=mysql_query("
            SELECT COLUMN_NAME 
            FROM KEY_COLUMN_USAGE 
            WHERE CONSTRAINT_SCHEMA = '".$db."'
            AND TABLE_NAME = '$table' AND CONSTRAINT_NAME = 'PRIMARY'
        ");
        if(mysql_num_rows($p_query)>0){
            $primk=mysql_fetch_assoc($p_query);
            $PK=$primk['COLUMN_NAME'];
        }
    }elseif(DB_ENGINE==DB_ENGINE_MSSQL){
        // MS SQL Server 2005 y 2000
        $p_query=mssql_query("
            SELECT KU.*
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC
            INNER JOIN
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KU
            ON TC.CONSTRAINT_TYPE = 'PRIMARY KEY' AND
            TC.CONSTRAINT_NAME = KU.CONSTRAINT_NAME
            WHERE TC.TABLE_NAME = '$table'
        ");
        if(mssql_num_rows($p_query)>0){
            $primk=mssql_fetch_assoc($p_query);
            $PK=$primk['COLUMN_NAME'];
        }
    }
    return $PK;
}

function include_package($name=NULL,$default=NULL,$include_all=false,$autoloader=NULL){
    if($name==NULL)
        return false;
        
    if(!is_dir(PATH_EXTENSIONS.'/'.$name)){
        trigger_error("El paquete $name no existe en ./engine/lib/",E_USER_WARNING);
        return false;
    }
    if(!empty($default)){
        include_once(PATH_EXTENSIONS."/$name/$default");    
    }
    
    if($include_all){
        $files=scandir(PATH_EXTENSIONS.'/'.$name);
        foreach($files as $filename){
            if($filename=="." || $filename=="..")
                continue;
            include_once(PATH_EXTENSIONS."/$name/$filename");
        }   
    }
    Sys::$Loaded_Packages[$name]=$autoloader;
    return true;
}

function include_package_file($package,$file,$return=false){
    include_once(PATH_EXTENSIONS."/$package/$file");    
}

if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

function has_access_to_location($uri){
    $url_parts=explode('?',$uri);
    $url_parts=$url_parts[0];
    $ac=explode('/',$url_parts);
    return Sys::$Acl->isAllowedOnLocation($ac[0],$ac[1]);
}

function now_gmt(){
    return date('Y-m-d H:i:s', time() - date('Z', time()));
}

function time_gmt(){
    return date('U', time() - date('Z', time()));
}

function date_to_gmt($time,$format='Y-m-d H:i:s'){
    $time = strtotime($time);
    return date($format, $time - date('Z', time()));
}

function date_to_local($time,$format='Y-m-d H:i:s'){
    $time = strtotime($time);
    return date($format, $time + date('Z', time()));
}

function stripslashes_rec(&$element)
{
     $element = stripslashes($element);
}

function log_event($event,$msg){
    $db = Db::getInstance();
    $db->insert("logger",array(
        'event' => $event,
        'message' => $msg,
        'date' => "now()"
    ));
}

/*
* Formato para el tiempo (x) dias (y) horas (z) segundos
* + Faltan dias   - Ya pasaron
*/
function  time_format($timestamp,$lang="es",$format="d-h-s"){
    if(empty($timestamp))
        return false;
    if($lang=="es"){
        $txt_d="dias";
        $txt_h="horas";
        $txt_s="segundos";
    }else{
        $txt_d="days";
        $txt_h="hours";
        $txt_s="seconds";
    }
    
    $tiempo = ($timestamp)/(60*60); //horas
    if($tiempo>24){
        $dias = floor($tiempo/24);
        $horas = $tiempo%24;
        $result = $dias." ".$txt_d." ".$horas." ".$txt_h;  
    }else{
        $horas = floor($tiempo);
        $mins = $tiempo-$horas;
        $mins = $mins*60;
        $segs = $mins-floor($mins);
        $mins = floor($mins);
        $segs = floor($segs*60);
        $result = $horas." hours ".$mins." mins ";//$segs." segs";
    }
    return $result;    
}

function get_common_mime($filename)
{
    preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

    switch(strtolower($fileSuffix[1]))
    {
        case "js" :
            return "application/x-javascript";

        case "json" :
            return "application/json";

        case "jpg" :
        case "jpeg" :
        case "jpe" :
            return "image/jpg";

        case "png" :
        case "gif" :
        case "bmp" :
        case "tiff" :
            return "image/".strtolower($fileSuffix[1]);

        case "css" :
            return "text/css";

        case "xml" :
            return "application/xml";

        case "doc" :
        case "docx" :
            return "application/msword";

        case "xls" :
        case "xlsx" :
        case "xlt" :
        case "xlm" :
        case "xld" :
        case "xla" :
        case "xlc" :
        case "xlw" :
        case "xll" :
            return "application/vnd.ms-excel";

        case "ppt" :
        case "pptx" :
        case "pps" :
            return "application/vnd.ms-powerpoint";

        case "rtf" :
            return "application/rtf";

        case "pdf" :
            return "application/pdf";

        case "html" :
        case "htm" :
        case "php" :
            return "text/html";

        case "txt" :
            return "text/plain";

        case "mpeg" :
        case "mpg" :
        case "mpe" :
            return "video/mpeg";

        case "mp3" :
            return "audio/mpeg3";

        case "wav" :
            return "audio/wav";

        case "aiff" :
        case "aif" :
            return "audio/aiff";

        case "avi" :
            return "video/msvideo";

        case "wmv" :
            return "video/x-ms-wmv";

        case "mov" :
            return "video/quicktime";
        case "rar":
        case "zip" :
            return "application/zip";

        case "tar" :
            return "application/x-tar";

        case "swf" :
            return "application/x-shockwave-flash";

        default :
            if(function_exists("mime_content_type"))
            {
                $fileSuffix = mime_content_type($filename);
            }

            return "unknown/" . trim($fileSuffix[0], ".");
    }
}