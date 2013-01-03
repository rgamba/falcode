<?php
/**
* ---
* dsp_list 
* ---
* Controlador de Agregar y Editar
* 
* @package  PHPlus
* @author   $Autor$
* @modified $Fecha$
*/
$id=$_REQUEST['id'];
${class_name}=new {class_name}($id);
$tpl=new Template(${template_path});
$tpl->add_file("dsp_ae.html");
${class_name}->select();
if(${class_name}->rows>0 && isset($id) && $id!=''){
	$tpl->set('AE',"Editar");
	${class_name}->next();
	$row=${class_name}->recordset();
	{fk_code}
	{img_code}
	$tpl->render_vars(get_defined_vars(),'row',NULL,true);
}else{
	$tpl->set('AE',"Agregar");
	$row=array();
	{fk_code}
	{img_code}
	$tpl->render_vars(get_defined_vars(),NULL,NULL,true);
}
$tpl->print_output();