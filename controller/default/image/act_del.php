<?php
if(empty($_REQUEST['main_table']) || empty($_REQUEST['image_table']) || empty($_REQUEST['main_table_pk']) || empty($_REQUEST['image_table_pk']) || empty($_REQUEST['id']) || empty($_REQUEST['image_field']))
    die();
$Db = Db::getInstance();
if($_REQUEST['main_table'] == $_REQUEST['image_table']){
    $q = $Db->query("SELECT * FROM ".$Db->escape($_REQUEST['main_table'])." WHERE ".$Db->escape($_REQUEST['main_table_pk'])." = '".$Db->escape($_REQUEST['id'])."'");
    if($Db->numRows()<=0)
        die();
    $img = $Db->getRow($q);
    if($img['id_usuario'] != User::get('id_usuario') && User::getRoleId() != "super_admin")
        die("Acceso denegado");
    unlink(PATH_CONTENT_FILES.'images/'.$_REQUEST['main_table'].'/'.$img['image_field']);
    $Db->query("DELETE FROM ".$Db->escape($_REQUEST['main_table'])." WHERE ".$Db->escape($_REQUEST['main_table_pk'])." = '".$Db->escape($_REQUEST['id'])."'");
}else{
    $q = $Db->query("SELECT * FROM ".$Db->escape($_REQUEST['image_table'])." i INNER JOIN ".$Db->escape($_REQUEST['main_table'])." m USING(".$Db->escape($_REQUEST['main_table_pk']).") WHERE ".$Db->escape($_REQUEST['image_table_pk'])." = '".$Db->escape($_REQUEST['id'])."'");
    if($Db->numRows()<=0)
        die();
    $img = $Db->getRow($q);
    if($img['id_usuario'] != User::get('id_usuario') && User::getRoleId() != "super_admin")
        die("Acceso denegado");
    unlink(PATH_CONTENT_FILES.'images/'.$_REQUEST['main_table'].'/'.$img['image_field']);
    $Db->query("DELETE FROM ".$Db->escape($_REQUEST['image_table'])." WHERE ".$Db->escape($_REQUEST['image_table_pk'])." = '".$Db->escape($_REQUEST['id'])."'");
}

if($_REQUEST['response'] == "json" || DSP_AJAX === true){
    echo json_encode(array('result' => true));
    exit(0);
}

Sys::setInfoMsg($this->lang->deleted_image);
redirect(url($_REQUEST['fwd_module'].(!empty($_REQUEST['fwd_action']) ? '/'.$_REQUEST['fwd_action'] : "").(!empty($_REQUEST['fwd_id']) ? "?id=".$_REQUEST['fwd_id'] : '')));
