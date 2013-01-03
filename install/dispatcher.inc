<?php
/**
* ---
* dispatcher
* ---
* Despachador y controlador de acciones
* 
* @package      gala fwk
* @version      2.0
* @author       $Autor$
* @modified     $Fecha$
* 
*/
// Descomentar la siguiente línea
// en caso de ocupar otro template
//Tpl::set(ACTIVE,'default');
// Descomentar la siguiente línea en caso
// de querer cambiar el título de la página
//Tpl::set(PAGE_TITLE,COMERCIO);
// Descomentar la siguiente línea para cambiar
// el header Content-type de la página
//Sys::set(CONTENT_TYPE,'text/html');
switch(DSP_CONTROL){
	case '':
	case 'list':
		// Listado
		$INCLUDE='dsp_list';
		break;
	case 'add':
	case 'edit':
		// Editar o Agregar
		$INCLUDE='dsp_ae';
		break;
	case 'view':
	case 'show':
		// Ver
		$INCLUDE='dsp_view';
		break;
	case 'ajx_save':
	case 'save':
		// Guardar
		$BLANK=true;
		$INCLUDE='act_save';
		break;
	case 'del':
		// Eliminar
		$BLANK=true;
		$INCLUDE='act_del';
		break;
	case 'del_img':
		// Eliminar imagen
		$BLANK=true;
		$INCLUDE='act_del_img';
		break;
}