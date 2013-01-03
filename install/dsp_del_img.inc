<?php
/**
* ---
* act_del_img 
* ---
* Eliminar imagen
* 
* @package  PHPlus
* @author   $Autor$
* @modified $Fecha$
*/
${class_name}=new {class_name}();
${class_name}->select(NULL,"WHERE id_producto = '".$_REQUEST['id_{table}']."'");
${class_name}->next();
unlink(PATH_CONTENT_FILES.'{parent_table}/'.${class_name}->{field_name});
{edit_delete}
redirect((!empty($_REQUEST['fwd']) ? $_REQUEST['fwd'] : url('{parent_table}/list')));