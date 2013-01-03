<?php
/**~controller/default/{table}/act_del.php
* 
* Delete (Controller extension file)
* ---
* Delete record
* 
* @package  BM
* @author   $Autor$
* @modified $Fecha$
* @uses     Current scope: {table}/ModuleController.php
* @uses     $this->*
*/
${class_name}=new {class_name}();
$id=$this->GET('id');

if(empty($id))
    $id=$_POST['{pk}'];

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

if(!Sys::hasErrorMsg())
    Sys::setInfoMsg("Registro '$id' eliminado correctamente");

// Regresamos
redirect((!empty($_REQUEST['fwd']) ? $_REQUEST['fwd'] : url('{table}/list')));