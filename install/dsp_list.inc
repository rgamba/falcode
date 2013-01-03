<?php
/**
* ---
* dsp_list
* ---
* Controlador de Listado
* 
* @package  PHPlus
* @author   $Autor$
* @modified $Fecha$
*/
// Instancias de clase
${class_name}=new {class_name}();
$Pag=new Paginator(); 
$tpl=new Template(${template_path});
$tpl->add_file("dsp_list.html");
// Extras para consulta
if(!empty($_REQUEST['search']))
	$where[]="({search_fields})";
{filtros}
// Orden
if(!empty($_REQUEST['ord']))
	$order="ORDER BY {$_REQUEST[ord]} ".(!empty($_REQUEST['ord_ref']) ? strtoupper($_REQUEST['ord_ref']) : 'DESC');
// Extra SQL
$extraSQL=(!empty($where) ? "WHERE ".implode(" AND ",$where) : '').' '.$order.' ';
${class_name}->select(NULL,$extraSQL{joins}{fk_fields});
// Verificamos si el request requiere respuesta de tipo JSON
if(substr(DSP_CONTROL,0,3)=="ajx"){
	$response=${class_name}->resultArray();
	// Imprimimos JSON
	echo json_encode($response);
	// Detenemos la ejecución
	exit();
}
if($tpl->set_condition("HayRegistros",(${class_name}->rows>0))){
	// Paginación
	$Pag->sql=${class_name}->sql;
	$Pag->navRowsPerPg=ROWS_PER_PAGE;
	$Pag->paginate();
	$tpl->set('Paginator',$Pag->tplHtml);
	// Column reorder
	$_get=get('request',array('i','ord','ord_ref','PHPSESSID'));
{column_order}
	${class_name}->select(NULL,$extraSQL.$Pag->limitQuery(){joins}{fk_fields});
	$tpl->set_section("Registros");
	$alt=' ';
	while($row=${class_name}->next()){
		$alt=$alt!="none" ? 'none' : '#f1f1f1';
		$row['bg_color']=$alt;
		$tpl->render_vars(get_defined_vars(),'row',NULL,true);
		$tpl->next_section_iteration();
	}
}
$tpl->finish_section();

// Determinamos necesita respuesta en blanco
$tpl->set_condition('BLANK',DSP_CONTROL=='bnk_list');

// Imprimimos
$tpl->print_output();

// Si es blank, detenemos ejecucion
if(DSP_CONTROL=='bnk_list')
	exit;