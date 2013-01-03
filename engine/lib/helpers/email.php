<?php
/**
* Send email shortcut function
* 
* @param mixed $de
* @param mixed $aliasDe
* @param mixed $para
* @param mixed $titulo
* @param mixed $mensaje
* @param mixed $is_html
*/
function send_mail($de,$aliasDe,$para,$titulo,$mensaje,$is_html=true){
    $Mail=new Mail();
    $Mail->to($para);
    $Mail->from($de,$aliasDe);
    $Mail->replyTo($de,$aliasDe);
    $Mail->subject($titulo);
    $Mail->body($mensaje);
    $Mail->html($is_html==true);
    $Mail->send();
    return;
}