<?php
/**~controller/default/{table}/dsp_view.php
* 
* View (Controller extension file)
* ---
* 
* @package  BM
* @author   $Autor$
* @modified $Fecha$
* @uses     Current scope: {table}/ModuleController.php
* @uses     $this->*
*/
$id=$this->request->id;
${class_name}=new {class_name}($id);
{joins}
${class_name}->columns("{fk_fields}");
${class_name}->execute();

if(${class_name}->rows<=0 || !isset($id) || $id==''){
    $this->system->setError($this->lang->not_found);
    redirect(url('{table}/list'));
    die();
}

$AE = $this->lang->view;
${class_name}->next();
$row=${class_name}->recordset();
{img_code}

// Get the view
$this->load->view("dsp_view",get_defined_vars());