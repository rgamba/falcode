<?php
/**
* Convierte el total de una moneda a otra
* tomando como moneda base el USD
* 
* @param mixed $amt Total a convertir
* @param mixed $currency_code_from Codigo de la moneda de origen
* @param mixed $currency_code_to Codigo de la moneda a la que se quiere convertir
*/
function convert_currency($amt,$currency_code_from,$currency_code_to){
    $db = Db::getInstance();
    $cur = $db->fetch("SELECT rate FROM moneda WHERE currency_code = '".$db->escape($currency_code_from)."'");
    if($cur->num_rows <= 0)
        return false;
    $amt_def_cur = $amt / $cur->row['rate'];
    
    $cur_to = $db->fetch("SELECT rate FROM moneda WHERE currency_code = '".$db->escape($currency_code_to)."'");
    if($cur_to->num_rows <= 0)
        return false;
    return $amt_def_cur * $cur_to->row['rate'];
}

function format_currency($amt,$currency_code,$suffix = true,$decimals=2){
    $db = Db::getInstance();
    $cur = $db->fetch("SELECT * FROM moneda WHERE currency_code = '".$db->escape($currency_code)."'");
    if($cur->num_rows <= 0)
        return false;
    return $cur->row['signo'].number_format($amt,$decimals)." ".strtoupper($currency_code);
}