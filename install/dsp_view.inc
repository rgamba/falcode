<?php
/**
* ---
* dsp_view
* ---
* Controlador de vista individual
* 
* @package  PHPlus
* @author   $Autor$
* @modified $Fecha$
*/
$id=$_REQUEST['id'];
${class_name}=new {class_name}($id);
$tpl=new Template(${template_path});
$tpl->add_file("dsp_view.html");
${class_name}->select();
if(${class_name}->rows>0 && isset($id) && $id!=''){
	$tpl->set('AE',"Editar");
	${class_name}->next();
	$row=${class_name}->recordset();
	{fk_code}
	{img_code}
	$tpl->render_vars(get_defined_vars(),'row',NULL,true);
}else{
	$_SESSION['error']="Registro no encontrado.";
	redirect(url('{table}/list'));
}
$tpl->print_output();