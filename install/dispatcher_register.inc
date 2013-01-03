<?php
/**
* ---
* dispatcher
* ---
* Despachador y controlador de acciones
* por metodo de Controller::register()
* 
* @package      PHPlus
* @version      2.0
* @author       $Autor$
* @modified     $Fecha$
* 
*/
/**
* Listado
*/
Controller::register(array('','list'),array(
    'use'   => 'dsp_list',
    'title' => 'Listado'
));

/**
* Agregar / Editar
*/
Controller::register(array('add','edit'),array(
    'use'   => 'dsp_ae',
    'title' => 'Agregar / Editar'
));

/**
* Vista individual
*/
Controller::register(array('view','show'),array(
    'use'   => 'dsp_view',
    'title' => 'Ver'
));

/**
* Guardar
*/
Controller::register('save',array(
    'use'   => 'act_save',
    'blank' => true  
));

/**
* Eliminar
*/
Controller::register('del',array(
    'use'   => 'act_del',
    'blank' => true  
));

/**
* Eliminar imagen
*/
Controller::register('del_img',array(
    'use'   => 'act_del_img',
    'blank' => true  
));