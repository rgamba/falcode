<?php
/**
* Check http vars
* 
* The names with _ prefix are required
* The names with @ prefix must be emails
* 
* @param     array     $post
* @return     bool
*/
function form_check(&$post=NULL,$setSesError=true){
    if($post==NULL)
        return false;
    $obl="_";
    $mail="@";
    $err=array();
    foreach($post as $name => $val){
        // Check required
        if(substr($name,0,1)==$obl){
            if($val==''){ 
                $err[]=str_replace("_"," ",strtoupper(substr($name,1)));
            }
            $post[substr($name,1)]=$val;
            unset($post[$name]);
        }
        // Check email
        if(substr($name,0,1)==$mail){
            $mail_pat='/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
            if(!preg_match($mail_pat,$val)){
                $err[]=Lang::get("error_email");
            }
        }
    }
    if(empty($err))
        return true;
    // Set error
    if($setSesError)
        Sys::setErrorMsg(Lang::get("error_check_fields").": <br />".join("<br />\t- ",$err));
    return $err;
}
