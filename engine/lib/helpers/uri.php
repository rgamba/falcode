<?php
function uri_alias_update($redirect,$set_error = true, $generate_unique=true){
    if(isset($_POST['uri_alias'])){
        $_POST['uri_alias']=str_replace(array('á','é','í','ó','ú','ñ',' '),array('a','e','i','o','u','n','-'),mb_strtolower($_POST['uri_alias'],"UTF-8"));
        $_POST['uri_alias']=str_replace(array('"','(',')','!','¡','¿','?',"'",",",";",":",".","[","]",'\\','*','=','$','#'),'',$_POST['uri_alias']);
        $_POST['uri_alias'] = str_replace('/','_SLASH_',$_POST['uri_alias']);
        $_POST['uri_alias']=urlencode($_POST['uri_alias']);
        $_POST['uri_alias'] = str_replace('_SLASH_','/',$_POST['uri_alias']);
        $ua = explode('-',$_POST['uri_alias']);
        $_POST['uri_alias'] = implode('-',array_slice($ua,0,15));

        if(class_exists('Uri')){
            $Uri = new Uri();
            $Uri->select(NULL,"WHERE redirect = '$redirect'");
            if($Uri->rows>0)
                $Uri->next();
            if(empty($_POST['uri_alias'])){
                $Uri->delete();   
            }else{
                if($generate_unique){
                    $db = Db::getInstance();
                    $i = 0;
                    while(true){
                        $_uri = $_POST['uri_alias'].($i==0 ? '' : '-'.$i);
                        $qry = $db->fetch("SELECT * FROM uri WHERE uri = '".$db->escape($_uri)."' AND redirect != '$redirect'");
                        if($qry->num_rows > 0){
                            $i++;
                        }else{
                            $_POST['uri_alias'] = $_uri;
                            break;
                        }
                    }
                }

                $Uri->uri = $_POST['uri_alias'];
                $Uri->redirect = $redirect;
                $Uri->save();
                if($Uri->lastQueryFailed()){
                    if($set_error)
                        Sys::setErrorMsg(Lang::get("uri_save_failed"));
                }
            }
        }
    }
}

function uri_find_key($redirect){
    if(class_exists('Uri') && Sys::get('config')->db_auto_connect){
        $Uri = new Uri();
        $Uri->select(NULL,"WHERE redirect = '$redirect'");
        if($Uri->rows<=0)
            return NULL;
        $Uri->next();
        return $Uri->uri;
    }
    return NULL;
}
