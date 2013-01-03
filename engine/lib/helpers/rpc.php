<?php
/**
* Opens sock connection to remote host
* 
* @param mixed $host
* @param mixed $uri
* @param mixed $params
* @param mixed $port
* @return mixed
*/
function sock_post($host='',$uri='',$params=array(),$port=443){
    if(empty($host) || empty($params)) return false;
    $proxy=fsockopen($host,$port,$errNo,$errDesc,10);
    if(!$proxy) return false;
    $postQry=http_build_query($params);
    // Command to send
    $cmd="POST ".$uri." HTTP/1.1\r\n";
    $cmd.="Host: $host\r\n";
    $cmd.="Content-Type: application/x-www-form-urlencoded\r\n";
    $cmd.="Content-Length: ".strlen($postQry)."\r\n";
    $cmd.="Connection: close\r\n\r\n";
    $cmd.=$postQry;
    if(!fputs($proxy,$cmd)){
        fclose($proxy);
        return false;
    }
    $response='';
    while(!feof($proxy)){
        $response.=fgets($proxy,128);
    }
    fclose($proxy);
    $lines=explode("\r\n",$response);
    foreach($lines as $i => $line){
        $line=explode(":",$line);
        $ret[trim($line[0])]=trim($line[1]);
    }
    return print_r($ret);
}

/**
* Send cURL request
* 
* @param mixed $url
* @param mixed $postfields
* @param mixed $urlencoded
* @param mixed $port
* @return mixed
*/
function send_curl($url,$postfields,$urlencoded=true,$port=NULL) {
    if(!function_exists('curl_init')) {        
        die("No existe la function del curl");
    }else{
        // Modificar el formato del array
        // postfields a string
        if($urlencoded==true){
            if(is_array($postfields)){
                $pf=http_build_query($postfields);
                $postfields=$pf;
            }
        }
         //Attempt HTTPS connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; WINDOWS; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset'=>'utf-8,*'));
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1); // Debug
        // -- Cookies
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "httpsdocs/tmp/");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "httpsdocs/tmp/"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        if(!empty($port)) curl_setopt($ch, CURLOPT_PORT, $port);
        if (defined('CURLOPT_ENCODING')) curl_setopt($ch, CURLOPT_ENCODING, "");
        $res=curl_exec ($ch);
        if($res==null){
            return false;
        }        
        if (!defined('CURLOPT_ENCODING')) {
            return false;
        }
        curl_close ($ch);
    }
    
    return $res;
}
