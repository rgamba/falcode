<?php
/*~ Db.php
+---------------------------------------------------+
| Software: GALA Framework							|
|  Version: 1.5										|
| 	Author: Ricardo Gamba							|
| Modified: 24/jun/09								|
| ------------------------------------------------- |
| Guardar registros									|				
+---------------------------------------------------+
*/
${class_name}=new {class_name}();
$Db=new Db();
unset($_POST['i']);

// Validación de PHP
if(form_check($_POST)===true){
{img_code_s}
${class_name}->populate($_POST);
${class_name}->save();
{img_code_m}
}

// Mensajes de respuesta
if(empty($_SESSION['error']))
	$_SESSION['msg']="Registro guardado correctamente";

// Redireccionamos o enviamos respuesta	
if(DSP_CONROL=='ajx_save'){
	if(empty($_SESSION['error'])){
		$result=array(
			'result' => 'ok',
			'msg' => $_SESSION['msg']
		);
	}else{
		$result=array(
			'result' => 'error',
			'msg' => $_SESSION['error']
		);
	}
	// Imprimimos resultado y salimos
	die(json_encode($result));
}
redirect((!empty($_REQUEST['fwd']) ? $_REQUEST['fwd'] : url('{table}/list')));