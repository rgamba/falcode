<?php
/**~controller/default/{table}/dsp_ae.php
* 
* Add / Edit (Include controller file)
* ---
* Controlador de Add / Edit
* 
* @package  FALCODE
* @author   $Autor$
* @modified $Fecha$
* @uses     Current scope: {table}/ModuleController.php
* @uses     $this->*
*/
$this->load->helper('uri');

$id=$this->request->id;
${class_name}=new {class_name}($id);

// Page title
$this->title((DSP_CONTROL=='add' ? $this->lang->create : $this->lang->edit)." {table}");

${class_name}->select();
if(${class_name}->rows>0 && isset($id) && $id!=''){
	$AE = $this->lang->edit;
	${class_name}->next();
	$row=${class_name}->recordset();
    $row['uri_alias'] = uri_find_key("{table}/view?id=$row[{pk}]"); // URI alias
	{fk_code}
	{img_code}
}else{
	$AE = $this->lang->create;
	$row=array();
	{fk_code}
	{img_code}
}

// Load view
$this->load->view("dsp_ae",get_defined_vars());
