<?php
/**~controller/default/{table}/act_save.php
* 
* Save record (Controller extension file)
* ---
* 
* @package  BM
* @author   $Autor$
* @modified $Fecha$
* @uses     Current scope: {table}/ModuleController.php
* @uses     $this->*
*/
$this->load->helper('form');
$this->load->helper('upload');
$this->load->helper('uri');
$this->db->startTransaction();

${class_name}=new {class_name}();

{img_code_s}
// Populate class fields
${class_name}->populate($_POST);
// Save
${class_name}->save();
// Check for errors
if(${class_name}->lastQueryFailed())
    $this->system->setError(${class_name}->getError());

// ID
$tmp_id=empty($_POST['{pk}']) ? $this->db->lastId() : $_POST['{pk}'];
{img_code_m}

// URI alias
uri_alias_update('{table}/view?id='.$tmp_id);

// Commit / rollback
if($this->system->hasError()){
    $this->db->rollbackTransaction();
}else{
    $this->db->commitTransaction();
}

// Response
if($this->request->isAjax()){
    // If AJAX request, send response in JSON
	if(!$this->system->hasError()){
		$result=array(
			'result' => true,
			'msg' => $this->system->getError()
		);
	}else{
		$result=array(
			'result' => false,
			'msg' => $this->system->getInfo()
		);
	}
    $this->response->isJSON();
	// Print result and exit
	echo json_encode($result);
}else{
    // Normal request, set flash message and redirect
    if(!$this->system->hasError())
        $this->system->setInfo($this->lang->record_saved);

    // Return
    redirect((!empty($this->request->fwd) ? $this->request->fwd : url('{table}/list')));
}