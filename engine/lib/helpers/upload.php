<?php
function handle_image_upload(array $params){
    $Load = Loader::getInstance();
    $Load->helper("string");
    
    $Db = Db::getInstance();
    if($params['mode'] == "multiple"){
        if(!empty($_POST[$params['uploaded_files_index']])){
            // Ya hemos cargado los archivos
            $files = explode(",",$_POST[$params['uploaded_files_index']]);
            foreach($files as $fname){
                if(file_exists(PATH_CONTENT.'tmp/'.$fname)){
                    $ext = pathinfo($fname);
                    $ext = $ext['extension'];
                    $new_img_name = md5(($Db->maxVal($params['image_table'],$params['image_table_pk'])+1)).".$ext";
                    if(rename(PATH_CONTENT.'tmp/'.$fname,PATH_CONTENT_FILES.'images/'.$params['main_table'].'/'.$new_img_name)){
                        $Db->insert($params['image_table'],array(
                            $params['main_table_pk'] => $params['main_table_id'],
                            $params['image_table_img_field'] => $new_img_name
                        )); 
                    }
                }
            }
        }else{
            // No se han cargado los archivos
            $Image=new Image();
            if(empty($_FILES[$params['http_files_index']]['error'])){
                $Image->path=PATH_CONTENT_FILES.'images/'.$params['main_table'].'/';
                $Db->query("SELECT * FROM $params[image_table] WHERE $params[main_table_pk] = '$params[main_table_id]'");
                if(is_array($_FILES[$params['http_files_index']]['name'])){
                    foreach($_FILES[$params['http_files_index']]['name'] as $j => $file){
                        $new_img_name= md5($Db->maxVal($params['image_table'],$params['image_table_pk'])+1);
                        $img_name=$Image->upload($_FILES[$params['http_files_index']],$new_img_name,500,false,true,$j);
                        if($img_name!=false){
                            $Db->insert($params['image_table'],array(
                                $params['main_table_pk'] => $params['main_table_id'],
                                $params['image_table_img_field'] => $img_name
                            ));
                        }
                    }
                }else{
                    $new_img_name= $Db->maxVal($params['image_table'],$params['image_table_pk'])+1;
                    $img_name=$Image->upload($_FILES[$params['http_files_index']],$new_img_name,500,false,true,$j);
                    if($img_name!=false){
                        $Db->insert($params['image_table'],array(
                            $params['main_table_pk'] => $params['main_table_id'],
                            $params['image_table_img_field'] => $img_name
                        ));
                    }
                }
            }   
        }
    }else{
        // Una sola imagen por registro
        if(!empty($_POST[$params['uploaded_files_index']])){
            $fname = $_POST[$params['uploaded_files_index']];
            if(file_exists(PATH_CONTENT.'tmp/'.$fname)){
                $ext = pathinfo($fname);
                $ext = $ext['extension'];
                $new_img_name = md5($params['main_table_id']).".$ext";
                if(rename(PATH_CONTENT.'tmp/'.$fname,PATH_CONTENT_FILES.'images/'.$params['main_table'].'/'.$new_img_name)){
                    $Db->insert($params['main_table'],array(
                        $params['main_table_pk'] => $params['main_table_id'],
                        $params['main_table_img_field'] => $new_img_name
                    )); 
                }
            }
        }else{
            $Image=new Image();
            $Image->max_size = 5000; // 5 megas
            if(!empty($_FILES)){
                $Image->path=PATH_CONTENT_FILES.'images/'.$params['main_table'].'/';
                try{
                    $img_name=$Image->upload($_FILES[$params['http_files_index']],rand_string(10),700);
                    if($img_name!=false){
                        $_POST[$params['main_table_img_field']]=$img_name;
                    }
                }catch(Exception $e){
                    Sys::setErrorMsg($e->getMessage());
                }
            }
        }
    }
}

function files_get_index($index,$i){
    $ret = array();
    if(isset($_FILES[$index]['name'][$i])){
        foreach($_FILES[$index] as $sub => $arr){
            $ret[$sub] = $_FILES[$index][$sub][$i];
        }
    }
    return $ret;
}

function move_tmp_file($name,$location,$new_name=null,$auto_name=true){
    if(!file_exists(PATH_CONTENT.'tmp/'.$name))
        return false;
    if($new_name == null && $auto_name){
        $new_name = uniqid().".".end(explode(".",$name));
    }
    if($new_name == null)
        $new_name = $name;
    $ren = rename(PATH_CONTENT.'tmp/'.$name,PATH_CONTENT_FILES.$location.'/'.$new_name);
    return $new_name;
}
