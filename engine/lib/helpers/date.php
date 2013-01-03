<?php
/**
 * Nombre del mes
 */
function month_name($mes){
    $months = Lang::get("month");
    return $months[$mes-1];
}

/**
 * Convierte el formato de fecha de mysql a arreglo
 */
function mysql_date_to_array($date,$yearFirst=true,$sep="-"){
    $date=substr($date,0,10);
    $date=explode($sep,$date);
    if($yearFirst==true){
        return array(intval($date[2]),intval($date[1]),intval($date[0]));
    }else{
        return array($date[0],$date[1],$date[2]);
    }
}

/**
 * Convierte formato de fecha de mysql a normal
 */
function mysql_date_to_normal($date,$sep="/"){
    $fecha=mysql_date_to_array($date);
    return     $fecha[0].$sep.$fecha[1].$sep.$fecha[2];
}

/**
 * Resta dias a la fecha actual
 */
function today_minus_days($dias=0,$retArray=false){
    $date = date('d/m/Y', strtotime("-$dias days"));
    if($retArray==false){
        return $date;
    }else{
        $d=explode("/",$date);
        return $d;
    }
}

/**
* Returns the week day name
* 
* @param mixed $dia
* @return mixed
*/
function week_day_name($dia){
    $names = Lang::get('week_days');
    return $names[$dia-1];
}

/**
* Get time in a friendly string
* 
* @param mixed $timestamp
* @param mixed $aprox
* @param mixed $format
* @return string
*/
function friendly_date($timestamp=NULL,$aprox=true,$format='d-m-Y'){
    if(empty($timestamp))
        return false;
    $now=time();
    if(date('d-m-Y',$timestamp)==date('d-m-Y',$now)){
        // Today
        return "hoy";
    }
    if($timestamp<$now){
        // Past
        $dias=floor(((($now/60)/60)/24)-((($timestamp/60)/60)/24));
        if($dias==1)
            return Lang::get("yesterday");
        if($dias>1 && $dias<=31)
            return Lang::get("time_ago",$dias,Lang::get("day")."s");
        if($dias>31 && $dias<=365){
            $meses=floor(($dias/30));
            return Lang::get("time_ago",$meses,Lang::get("month").($meses>1 ? 's' : ''));
        }
        if($dias>365){
            $anos=round(($dias/365));
            if($aprox)
                return Lang::get("time_ago",$anos,Lang::get("year").($anos>1 ? 's' : ''));
            return date($format,$timestamp);
        }
    }else{
        // Future
        $dias=ceil(((($timestamp/60)/60)/24)-((($now/60)/60)/24));
        if($dias==1)
            return Lang::get("tomorrow");
        if($dias>1 && $dias<=31)
            return Lang::get('in_time',$dias,Lang::get('day').'s');
        if($dias>31 && $dias<=365){
            $meses=floor(($dias/30));
            return Lang::get('in_time',$meses,Lang::get('month').($meses>1 ? 's' : ''));
        }
        if($dias>365){
            $anos=round(($dias/365));
            if($aprox)
                return Lang::get('in_time',$anos,Lang::get('year').($anos>1 ? 's' : ''));
            return date($format,$timestamp);
        }
    }
    
}

/**
* Converts the date to a timestamp
* 
* @param array $date
* @return int
*/
function date_to_timestamp($date){
    if(empty($date))
        return false;
    $fecha=explode(' ',$date);
    $date=explode('-',$fecha[0]);
    $time=explode(':',$fecha[1]);
    return mktime((int)$time[0],(int)$time[1],(int)$time[2],(int)$date[1],(int)$date[2],(int)$date[0]);
}

/**
* Add days
* 
* @param mixed $timestamp
* @param mixed $dias
*/
function forward_days(&$timestamp,$dias=1){
    $timestamp=$timestamp+(86400*$dias);
    return $timestamp;
}

/**
* Converts to mysql date format
* 
* @param mixed $date
*/
function to_mysql_date(&$date){
    $d=explode('/',$date);
    if(!empty($date))
        $date=$d[2]."-".$d[1]."-".$d[0];
    return $date;
}