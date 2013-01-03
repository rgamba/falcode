<?php
/*~ install/import.php
+---------------------------------------------------+
| Software: PHPlus Framework                        |
|  Version: 2.2.0                                   |
|     Author: Ricardo Gamba                         |
| Modified: 09|dic|2010                             |
| --------------------------------------------------|
| Archivo para importar informacion a la base de    |
| datos mediante archivos en formato CSV            |       
+---------------------------------------------------+
*/
session_start();
header("Content-Type:text/html; charset=utf-8"); 
$op=$_REQUEST['op'];
require_once("../engine/lib/_misc.php");
require_once("../engine/lib/CSV.php");
require_once("../engine/lib/Uploader.php");
//error_reporting(0);
if(file_exists("../install/dbsetup.txt")){
    $dbs=file_get_contents("../install/dbsetup.txt");
    $dbs=explode(",",nl2br($dbs));
    foreach($dbs as $line){
        $line=explode("=",$line);
        $db[$line[0]]=$line[1];
    }
}else{
    die("No se encontro el archivo de configuracion dbsetup.txt");
}
$CON=mysql_connect($db['db_host'],$db['db_user'],$db['db_pass'])or die("No se pudo conectar a la base de datos");
mysql_select_db($db['db_name'],$CON) or die("No se pudo seleccionar la base de datos");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="author" content="GALA" />
    <title>PHPlus -  Importador de datos</title>
<style>
body{
    font-family: arial, tahoma, sans-serif;
    font-size: 12px;
    color: #333333;
    background: #e6e6e6;
    background: #e6e6e6 url(bg.gif) repeat-x;
    padding: 0px;
    margin: 0px;
}
.wrapper{
    width: 600px;
    padding: 15px;
    border: solid 2px #dddddd;
    background: #ffffff;
    margin: 10px auto;
    -moz-border-radius: 15px 15px 15px 15px;
}
input[type=text]{
    width: 200px;
    -moz-border-radius: 5px 5px 5px 5px;
}
input[type=submit], input[type=button]{
    border: solid 1px #0d6181;
    padding: 3px 10px;
    color: white;
    background-color: #0076a3;
    -moz-border-radius: 5px 5px 5px 5px;
}
h2{
    border-bottom: 1px solid rgb(230, 230, 230); padding-bottom: 5px;
}
.error{
    padding: 10px; 
    border: solid 1px #e6e6e6;
    background: #f0f0f0;
    margin-top: 10px;
}
thead td{
    font-weight: bold;
    border-bottom: solid 1px #e6e6e6;
    font-size: 14px;
}
</style>
</head>
<body>
<div class="wrapper">
<div>
<img src="phplus_logo.gif" />
<?php
switch($op){
///////////////////////////////////////
// Default
    default: 
    case '':
        $tables=($db['db_engine']=="mysql") ? mysql_list_tables($db['db_name']) : mssql_list_tables($db['db_name']);
        if(empty($tables)){
            echo "No hay tablas en su base de datos";
            break;
        }
        if(!empty($_SESSION['error'])){
            echo '<div class="error">'.$_SESSION['error']."</div>";
            unset($_SESSION['error']);
        }
?>
<form action="import.php?op=config" method="post" enctype="multipart/form-data">
<h2>Tabla destino</h2>
<div>
<p>Seleccione la tabla a la cual se van a importar los registros</p>
<div>
<div style="margin-bottom: 10px;padding: 7px; background: #f7f7f7; border: dashed 1px #e6e6e6; border-left: none; border-right: none; clear: both; float: left">
<?php
    $num_rows = mysql_num_rows($tables);
    for ($i = 0; $i < $num_rows; $i++) {
?>
<div style="width: 290px; float: left; padding: 1px 0"><label><input type="radio" checked="true" id="tabla" name="tabla" value="<?php echo mysql_tablename($tables, $i); ?>" />
        <b><span style="color: #0076a3"><img src="table.png" />&nbsp;<?php echo mysql_tablename($tables, $i); ?></span></b></label>
</div>
<?php
    }


?>
</div>
</div>
</div>
<h2 style="margin-top: 10px">Archivo de origen</h2>
<div><p>Seleccione el archivo CSV de donde se van a obtener los registros.<br />
<span style="font-size: 11px"><b>NOTA</b> La primera fila del registro deben ser los nombres de las columnas.</p></span>
<div><input type="file" name="csv" /></div>
<div style="margin-top: 10px">
<input type="submit" value="Siguiente >>" />
</form>
</div>
</div>
<?php
    break;
///////////////////////////////////////
// Configuracion
    case 'config':
        if($_FILES['csv']['size']<=0){
            $_SESSION['error']="Seleccione un archivo CSV";
            redirect("import.php");
            die();
        }
        $U=new Uploader();
        $U->allow('application/vnd.ms-excel');
        $U->location="tmp";
        $U->newName=rand_string(7);
        try{
            $csv=$U->upload($_FILES['csv']);    
        }catch(Exception $e){
            $_SESSION['error']=$e->getMessage();
            redirect("import.php");
            die(); 
        }
        $Csv=new CSV("tmp/".$U->newFile);
        try{
            $Csv->open();
            $headers=$Csv->headers;
        }catch(Exception $e){
            $_SESSION['error']=$e->getMessage();
            redirect("import.php");
            die(); 
        }
        
        $table=$_REQUEST['tabla'];
        $columnas=array();
        $tbl=mysql_query("SHOW COLUMNS FROM $table");
        if(mysql_num_rows($tbl)==0){
            echo "La tabla seleccionada no tiene columnas";
            break;
        }
        echo '<h2>Configuración de datos</h2>';
        echo '<div><p>Seleccione la fuente de datos para cada una de las columnas de la tabla</p></div>';
        echo '<table style="width: 100%; table-layout: fixed">';
        echo '<thead><tr><td>Campo destino</td><td>Origen de datos</td><td>No repetir</td></tr></thead>';
        $alt=' ';
        while($field=mysql_fetch_assoc($tbl)){
            $alt=empty($alt) ? '#f0f0f0' : '';
?>
<tr style="background: <?php echo $alt; ?>">
    <td><?php echo '<b>'.$field['Field'].'</b><span style="font-size: 11px; margin-left: 10px; color: #666">'.$field['Type'].'</span>'; ?></td>
    <td>
    <select name="columna[<?php echo $field['Field']; ?>]">
    <option value=""></option>
<?php
            foreach($headers as $i => $header){
                echo '<option value="'.$header.'">'.$header.'</option>';
            }
?>
    </select>
    </td>
    <td>
    <input type="checkbox" name="unique[<?php echo $field['Field']; ?>]" />
    </td>
</tr>
<?php  
        }
        echo '</table>';
        
?>

<?php  
    break;
///////////////////////////////////////
// Importacion
    case 'import':
?>

<?php
    break;
}
?>
</div></div>
<div style="text-align: center; color: #999999; margin: 10px; 0px">PHPlus Framework &copy; 2.2.0  | </div>
</body>
</html>