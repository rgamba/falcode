<?php
/**~controller/default/{table}/dsp_list.php
* 
* List (Controller extension file)
* ---
* 
* @package  BM
* @author   $Autor$
* @modified $Fecha$
* @uses     Current scope: {table}/ModuleController.php
* @uses     $this->*
*/
${class_name}=new {class_name}();
$Pag=new Paginator();

// Search fitlers
if(!empty($this->request->search))
    ${class_name}->where("({search_fields})",$this->request->search);
{filtros}
// Order
if(!empty($this->request->ord))
    ${class_name}->orderBy($this->request->ord,(!empty($this->request->ord_ref) ? strtoupper($this->db->escape($this->request->ord_ref)) : 'DESC'));
// Joins
{joins}
// Fields
${class_name}->select("{fk_fields}");
// Execute query
${class_name}->execute(false);

// Verify if request needs JSON response
// Comment out the whole first block of the condition if you don't want
// to enable this behaviour
if($this->request->isAjax()){
    $this->response->isJSON();
    ${class_name}->execute();
	// Print JSON
    echo json_encode(${class_name}->resultArray());
}else{
    // Pagination
    $Pag->sql=${class_name}->Sql;
    $Pag->navRowsPerPg = $this->config->rows_per_page;
    $Pag->paginate();
    $Paginator = $Pag->tplHtml;

    // Column reorder
    $_get=get('request',array('i','ord','ord_ref','PHPSESSID'));
    $col_order = array();
{column_order}
    // Exacute again with limits
    ${class_name}->limit($Pag->limitQuery(false))->execute();
    if(${class_name}->rows > 0){
        $recordset = ${class_name}->resultArray();
    }

    $this->load->view("dsp_list",get_defined_vars());
}