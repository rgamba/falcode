<?php
/**
* ---
* act_del
* ---
* Eliminar registro
* 
* @package  PHPlus
* @author   $Autor$
* @modified $Fecha$
*/
$id=!empty($_GET['id_{table}']) ? $_GET['id_{table}'] : $_POST['id_{table}'];
if(!isset($id))
	$id=$_REQUEST['id'];
${class_name}=new {class_name}();
if(is_array($id)){
	foreach($id as $i => $_id){
		${class_name}->select($_id);
		${class_name}->delete();
        ${class_name}->clear();
	}
}else{
	${class_name}->select($id);
	${class_name}->delete();
}
redirect(url('{table}/list')); 