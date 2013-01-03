<?php
/**
 * Recorta el texto
 * @param texto        Texto a recortar
 * @param len        Longitud minima para evaluar recorte
 * @param puntos     AÃ±adir tres puntos en caso de recortar?
 */
function truncate($texto,$len,$puntos=true){
    if(strlen($texto)<=$len){
        return $texto;
    }else{
        if($puntos){
            return substr($texto,0,$len-1)."...";
        }else{
            return substr($texto,0,$len-1);
        }
    }
}

/**
 * Devuelve una cadena aleatoria
 * @param len longitud de la cadena
 */
function rand_string($len=10){
    $str="abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $res="";
    for($i=0;$i<=($len-1);$i++){
        $res.=substr($str,ceil(rand(0,(strlen($str)-1))),1);
    }
    return $res;
}

/**
 * Devuelve el texto entre dos carÃ¡cteres
 */
function text_between($texto,$inicio,$fin){
    $firstChar=strpos($texto,$inicio);
    if($firstChar!=false){
        $char="";
        $offset=$firstChar;
        while($char!=$fin){
            $char=substr($texto,$offset,1);
            $ret.=$char;
            $offset++;
        }
        $ret=substr($ret,0,strlen($ret)-1);
        return $ret;
    }else{
        return false;
    }
}

/**
* Add leading zeros
* 
* @param mixed $texto
* @param mixed $digitos
*/
function zero_fill($texto,$digitos){
    if(strlen($texto)<$digitos){
        $ceros=$digitos-strlen($texto);
        for($i=0;$i<=$ceros-1;$i++){
            $ret.="0";
        }
        $ret=$ret.$texto;
        return $ret;
    }else{
        return $texto;
    }
}

/**
 * percent()
 * 
 * Devuelve el numero en formato de porcentaje
 * @param mixed $number
 * @param integer $decimals
 * @return
 */
function percent($number=NULL,$decimals=2){
    return number_format(($number<0 ? $number*100 : $number),$decimals)."%";
}

/**
* Parse a csv formatted line
* 
* @param string $str    Linea de texto a parsear
* @param mixed $sep     Separador de valor
* @param mixed $lim     Limitador de valor
*/
function parse_csv_line($str,$sep=",",$lim='"'){
    $str=trim($str);
    $inside=false;
    $arr=array();
    $tmp="";
    $escaped=false;
    for($i=0;$i<strlen($str);$i++){
        $char=$str[$i];
        if($escaped && $inside){
            $tmp.=$char;
            $escaped=false;
            continue;
        }
        switch($char){
            case $sep:
                if($inside){
                    $tmp.=$char;
                    break;
                }
                $arr[]=$tmp;
                $tmp="";
                break;
            case $lim:
                $inside=(!$inside);
                break;
            case '\\':
                $escaped=true;
                break;
            default:
                $tmp.=$char;
        }    
    }
    $arr[]=$tmp;
    return $arr;
}

/**
* Hex to bin
* 
* @param mixed $str
* @return string
*/
function hex2bin($str){
    return pack('H*',$str);
}

/**
* Mask credit card number
* 
* @param mixed $no
*/
function hide_card_number($no){
    $ret="";
    for($i=1;$i<=strlen($no);$i++){
        if($i<=strlen($no)-4)
            $ret.="*";
        else
            $ret.=substr($no,$i-1,1);   
    }
    return $ret;
}

function to_url_friendly($str){
    return trim(mb_strtolower(str_replace(
        array('á','é','í','ó','ú','ñ',' ','Á','É','Í','Ó','Ú','Ñ'),
        array('a','e','i','o','u','n','-','A','E','I','O','U','N'),
        $str
    ),"UTF-8"));
}

function utf8_strtolower($str){
    return mb_strtolower($str,"UTF-8");
}

function quitar_acentos($str){
    return str_replace(array('á','é','í','ó','ú'),array('a','e','i','o','u'),$str);
}