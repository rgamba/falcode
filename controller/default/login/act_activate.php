<?php
/**
* ---
* act_del (Include action file)
* ---
* Eliminar registro
* 
* @package  PHPlus
* @author   $Autor$
* @modified $Fecha$
* @uses     Metodos y funciones en el scope de ModuleController 
* @uses     $this->*
*/
$Usuario=new Usuario();

//Verificamos si el mail del usuario existe, 
//de ser así , checar que no esté activado y 
/// enviar mail para la activación de su cuenta

$codigo = $_GET['code'];
$fwd = url('login/join');

$Usuario->select(NULL,"WHERE codigo_act = '".$codigo."'");
if($Usuario->rows>0){
    $Usuario->next();
    if($Usuario->activo == "0"){
        //Activar la cuenta del usuario
        Db::_query("UPDATE usuario SET activo = 1 WHERE id_usuario = '".$Usuario->id_usuario."'");
        Sys::setInfoMsg("Tu cuenta ha sido activada con éxito");
    }else{
        Sys::setInfoMsg("Tu cuenta ya ha sido activada previamente");
    }
    $fwd = url('login/login');
}else{
    Sys::setInfoMsg("Código de activación inválido.");    
}


// Regresamos
redirect($fwd);