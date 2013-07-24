<?php
error_reporting(0);
header("Content-Type:text/html; charset=utf-8"); 
$op=$_REQUEST['op'];
require_once("../engine/lib/core/_misc.php");
require_once("../engine/lib/helpers/array.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<meta name="author" content="GALA" />
	<title>Falcode Installer</title>
<script language="javascript">
checked=false;
function checkAll () {
    if (checked == false){
    		checked = true
	}else{
		checked = false
	}
	for (var i = 0; i < document.getElementById('tablas').elements.length; i++) {
	  document.getElementById('tablas').elements[i].checked = checked;
	}
}

function validarForm(){
	if(document.getElementById('app_name').value==''){
		alert('¿Cual es el nombre de la aplicación?');
		document.getElementById('app_name').focus();
		return false;
	}
	if(document.getElementById('db_host').value==''){
		alert('¿Cual es el host de la base de datos?');
		document.getElementById('db_host').focus();
		return false;
	}
	if(document.getElementById('db_name').value==''){
		alert('¿Cual es el nombre de la base de datos?');
		document.getElementById('db_name').focus();
		return false;
	}
	return true;
}
</script>
<style>
body{
	font-family: arial, tahoma, sans-serif;
	font-size: 12px;
	color: #444444;

	padding: 0px;
	margin: 0px;
}
h1{
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: solid 1px #cccccc;
}
h2{
    font-size: 18px;
    font-weight: normal;
    margin-bottom: 15px;
}
.wrapper{
	width: 700px;
	padding: 0px;

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
    padding: 5px 10px;
    color: white;
    background-color: #0076a3;
    border-radius: 6px;
    font-size: 16px;
    font-weight: normal;
    box-shadow: 0 1px 1px #cccccc;
}
</style>
</head>
<body>
<div class="wrapper">
<div>
<h1>Falcode Installer</h1>
<?php
switch($op){

	case 'config':
    if($_REQUEST['del']==1){
        unlink("../install/dbsetup.txt");
    }
	if(file_exists("../install/dbsetup.txt"))
		redirect("?op=connect");
/**
 ***********************************************
 * MAIN SCREEN
 ***********************************************
 */
		chmod("../htaccess",0777);
		chmod("../content/_files/docs",0777);
		chmod("../content/_files/images",0777);
		chmod("../content/_files/media",0777);
		chmod("../content/_files/thumbs",0777);
		chmod("../content/_tmp",0777);
		chmod("../content/_files/docs",0777);
		chmod("../engine/conf/config.php",0777);
		chmod("../engine/model",0777);
		chmod("../controller/default",0777);
		chmod("../content/templates/default/html",0777);
		chmod("../content/templates/default/layout.html",0777);
?>
<h2>Configuración del Sistema</h2>
<div>
<p>Por favor, introduce los datos de tu aplicación y datos de conexión a la base de datos:</p>
<form action="" method="post" onsubmit="return validarForm();">
<input type="hidden" name="op" value="connect" />
<table cellpadding="" cellspacing="" width="100%">
<tr>
    <td colspan="2" style="border-bottom: solid 1px #e6e6e6"><b>Información General</b></td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px; width: 280px">Nombre de la aplicación:</td><td><input type="text" name="app_name" id="app_name" /></td>
</tr>

<tr>
    <td style="text-align: right; padding-right: 4px">Subdominio:</td><td>
    <select name="subdomain">
    <?php
    $controller=scandir('../controller/');
    foreach($controller as $i => $dir){
        if($dir=='.' || $dir=='..' || $dir=='.htaccess')
            continue;
        ?>
        <option value="<?php echo $dir?>" <?php echo $dir=='default' ? 'selected="selected"' : '' ?>><?php echo $dir=='default' ? 'Default (ninguno)' : $dir?></option>
        <?php
    }
    ?>
    </select>
    </td>
</tr>
<tr>
	<td colspan="2" style="border-bottom: solid 1px #e6e6e6"><b>Base de datos</b></td>
</tr>
<tr>
    <td style="text-align: right; padding-right: 4px">Motor de base de datos:</td>
    <td>
    <select name="db_engine">
    <option value="mysql">MySQL</option>
    <option value="mssql_2005">MS SQL Server 2005</option>
    <option value="mssql_2000">MS SQL Server 2000</option>
    </select>
    </td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">Host:</td><td><input type="text" name="db_host" id="db_host" value="localhost" /></td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">Usuario:</td><td><input type="text" name="db_user" id="db_user" /></td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">Password:</td><td><input type="text" name="db_pass" id="db_pass" /></td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">Nombre de la base:</td><td><input type="text" name="db_name" id="db_name" /></td>
</tr>
<tr>
    <td style="text-align: right; padding-right: 4px">Modalidad de conexión:</td><td>
    <label><input type="radio" name="db_mode" value="simple" checked="checked" />Simple (1 BD)</label>&nbsp;&nbsp;
    <label><input type="radio" name="db_mode" value="multi" />Múltiple (2+ BD)</label>
    </td>
</tr>

<tr>
	<td colspan="2" style="border-bottom: solid 1px #e6e6e6"><b>Directorios</b></td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/.htaccess</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../.htaccess')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/content/_files/docs</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../content/_files/docs')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/content/_files/images</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../content/_files/images')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/content/_files/media</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../content/_files/media')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/content/_files/thumbs</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../content/_files/thumbs')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/content/_tmp</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../content/_tmp')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/engine/conf/config.php</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../engine/conf/config.php')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/engine/model</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../engine/model')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/controller/default</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../controller/default')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/content/templates/default/html</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../content/templates/default/html')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 4px">/content/templates/default/layout.html</td>
	<td>
	<?php
	echo (substr(sprintf('%o', fileperms('../content/templates/default/layout.html')), -3)>=666) 
		? '<span style="color: green; font-weight: bold">Se puede escribir</span>'
		: '<span style="color: red; font-weight: bold">No se puede escribir</span>'
	?>
	</td>
</tr>
<tr>
    <td colspan="2" style="border-bottom: solid 1px #e6e6e6"><b>Configuración de PHP</b></td>
</tr>
<tr>
    <td style="text-align: right; padding-right: 4px">Short open tags</td>
    <td>
    <?php
    ini_set('short_open_tags','On');
    echo ini_get('short_open_tags') != "Off" 
        ? '<span style="color: green; font-weight: bold">Habilitadas</span>'
        : '<span style="color: red; font-weight: bold">Deshabilitadas</span>'
    ?>
    </td>
</tr>
<tr>
    <td style="text-align: right; padding-right: 4px">Magic quotes</td>
    <td>
    <?php
    echo get_magic_quotes_gpc() == 1 
        ? '<span style="color: red; font-weight: bold">Habilitadas</span>'
        : '<span style="color: green; font-weight: bold">Deshabilitadas</span>'
    ?>
    </td>
</tr>
<tr>
    <td style="text-align: right; padding-right: 4px">Versión de PHP</td>
    <td>
    <?php
    echo version_compare(PHP_VERSION,'5.0.0') >= 0 
        ? '<span style="color: green; font-weight: bold">'.PHP_VERSION.'</span>'
        : '<span style="color: red; font-weight: bold">'.PHP_VERSION.'</span>'
    ?>
    </td>
</tr>
</table>
<br />
<input type="submit" value="Continuar >>" />
</form>
</div>
<?php
		break;
    default:
    case '':
	case 'connect':

    include("../engine/conf/config.php");


    if(file_exists("../install/dbsetup.txt")){
	    $dbs=file_get_contents("../install/dbsetup.txt");
	    $dbs=explode(",",nl2br($dbs));
	    foreach($dbs as $line){
		    $line=explode("=",$line);
		    $_REQUEST[$line[0]]=$line[1];
	    }
    }
    
    // Si es MSSQL entonces verificamos la version
    if($_REQUEST['db_engine']=='mssql_2005' || $_REQUEST['db_engine']=='mssql_2000'){
        $_REQUEST['db_version']=($_REQUEST['db_engine']=='mssql_2005') ? '2005' : '2000';
        $_REQUEST['db_engine']='mssql';
    }
/**
 ***********************************************
 * CONECTAMOS A LA BASE DE DATOS Y ACTUALIZAMOS
 * EL CONFIG
 ***********************************************
 */
?>
<h2 style="border-bottom: solid 1px #e6e6e6; padding-bottom: 5px">Module, Model and CRUD creation</h2>
<div>
<p>A module, model and CRUD will be created for the selected tables:
</p>
<div>
<div style="float: left: height: auto; clear: both; float: left; margin-bottom: 15px">
<form action="?op=create" method="post" id="tablas">
<input type="hidden" name="db_host" value="<?php echo $config['db_host']?>" />
<input type="hidden" name="db_user" value="<?php echo $config['db_user']?>" />
<input type="hidden" name="db_pass" value="<?php echo $config['db_pass']?>" />
<input type="hidden" name="db_name" value="<?php echo $config['db_name']?>" />
<input type="hidden" name="subdomain" value="<?php echo $config['subdomain']?>" />
<?php
if(strpos($_REQUEST['db_engine'],"_")!==false){
    $_REQUEST['db_version']=substr($_REQUEST['db_engine'],-4);
    $_REQUEST['db_engine']=substr($_REQUEST['db_engine'],0,-5);
}

$engine=strtolower($config['db_engine']);
$con=($engine=="mysql")
    ? mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']) or die("Couldn't connect to the database. Check the database credentials in engine/conf/config.php")
    : mssql_connect($config['db_host'],$config['db_user'],$config['db_pass']) or die("Couldn't connect to the database. Check the database credentials in engine/conf/config.php");
    
if($engine=="mysql")
    mysql_select_db($config['db_name']) or die("Couldn't select database. Check \$conf['db_name'] in engine/conf/config.php");
else
    mssql_select_db($config['db_name'],$con) or die("Couldn't select database. Check \$conf['db_name'] in engine/conf/config.php");
$tables=($engine=="mysql") ? mysql_list_tables($config['db_name']) : mssql_list_tables($config['db_name']);
if(empty($tables)){
	echo "The database is empty.";
}else{

	$subdomain=empty($_REQUEST['subdomain']) ? 'default' : $_REQUEST['subdomain'];
	$config=file_get_contents('../engine/conf/config.php');
	$config=str_replace('{DB_HOST}',$_REQUEST['db_host'],$config);
	$config=str_replace('{DB_USER}',$_REQUEST['db_user'],$config);
	$config=str_replace('{DB_PASS}',$_REQUEST['db_pass'],$config);
	$config=str_replace('{DB_NAME}',$_REQUEST['db_name'],$config);
	$config=str_replace('{APP_NAME}',$_REQUEST['app_name'],$config);
    $config=str_replace('{DB_ENGINE}',("Db::ENGINE_".strtoupper($_REQUEST['db_engine'])),$config);
	$f=fopen("../engine/conf/config.php","w");
    if(!file_exists("../engine/conf/config")){
        $fo=fopen("../engine/conf/config","w");
        fwrite($fo,$config);
        fclose($fo);
    }
	fwrite($f,$config);
	fclose($f);
    echo '<div style="padding: 7px; background: #f7f7f7; border: dashed 1px #e6e6e6; border-left: none; border-right: none; clear: both; float: left">';
    if($engine=="mysql"){
        $num_rows = mysql_num_rows($tables);
	    for ($i = 0; $i < $num_rows; $i++) {
	        ?><div style="width: 290px; float: left; padding: 1px 0"><label><input type="checkbox" checked="true" id="tabla" name="tabla[]" value="<?php echo mysql_tablename($tables, $i)?>" />
            <?php
            if(!is_dir('../controller/'.$subdomain.'/'.mysql_tablename($tables, $i)))
                echo '<b><span style="color: #0076a3"><img src="table.png" />&nbsp;';
            else
                echo '<span style="color: #666666"><img src="table_blur.png" />&nbsp;';
            echo mysql_tablename($tables, $i); 
            echo '</span></b>';
            ?>
            </label></div><?php
	    }
    }else{
        foreach($tables as $i => $table){
            ?><div style="width: 290px; float: left"><label><input type="checkbox" checked="true" id="tabla" name="tabla[]" value="<?php echo $table?>" />
            <?php
            if(!is_dir('../controller/'.$subdomain.'/'.$table))
                echo '<b><span style="color: #0076a3">';
            echo $table; 
            if(!is_dir('../controller/'.$subdomain.'/'.$table))
                echo '</span></b>';
            ?>
            </label></div><?php
        }
    }
    echo "</div>";
}
?>
</div>
    <div>
        <input type="button" value="Select/unselect all" style="font-size: 12px" onclick="checkAll()" />
    </div>
    <p>If a module, model or CRUD already exist, then nothing will be overwritten.</p>
<div style="clear: both"></div>
<!--<h2 style="border-bottom: solid 1px #e6e6e6; padding-bottom: 5px">3. Plugins disponibles</h2>
<div style="margin-bottom: 15px; float: left">
<p>Selecciona los complementos que deseas instalar.</p>
<div style="padding: 7px; background: #f7f7f7; border: dashed 1px #e6e6e6; border-left: none; border-right: none; clear: both; float: left">
<?php
/*if(is_dir('../plugins')){
    if ($dh = opendir('../plugins/')) {
        $cn=0;
        while (($file = readdir($dh)) !== false) {
            $dir='../controller/'.$subdomain.'/'.substr($file,0,-4)."/";
            if(substr($file,-3)!='zip')
                continue;
            $cn++;
            ?>
    <div style="width: 290px; float: left; padding: 1px 0"><label><input type="checkbox" id="tabla" name="plugin[]" value="<?php echo $file?>" <?php echo (in_array(substr($file,0,-4),$tables)) ? 'disabled="disabled"' : ''  ?> />
            <?php
            if(in_array(substr($file,0,-4),$tables)){
                echo "<span style='color: red'><img src='package_add.png' />&nbsp;<b>".substr($file,0,-4)."</b><span style='font-size: 11px'> (Conflicto de nombre)</span></span></label></div>";
            }elseif(!is_dir($dir)){
                echo "<span style='color: #0076a3'><img src='package_add.png' />&nbsp;<b>".substr($file,0,-4)."</b></span></label></div>";
            }else{
                echo substr($file,0,-4)."</label></div>";
            }
        }
        if($cn==0)
            echo "No hay complementos disponibles";
        closedir($dh);
    }
}*/

?>
</div>
</div>-->
    <input type="hidden" name="despachador" value="module_controller" />
<!--<div style="clear: both"></div>
<h2 style="border-bottom: solid 1px #e6e6e6; padding-bottom: 5px">4.</h2>
<div style="clear: both;">
Tipo de despachador: <select name="despachador">
<option value="module_controller">Por clase controladora (default)</option>
<option value="dispatcher">Por archivo despachador</option>
</select>
</div>-->

<div style="clear: both"></div>
<h2 style="border-bottom: solid 1px #e6e6e6; padding-bottom: 5px">Database structure</h2>
<div style="clear: both;">
<?php
$config_file=json_decode(file_get_contents("db.config"),true);
if($config_file==false && file_exists(db.config)){
    echo "<div style='color: red'>The structure file db.config contains syntax errors, it must be written in JSON format.</div>";
}
if(!empty($config_file)){
    echo "<table cellpadding='2' width='100%'>";
    foreach($config_file as $table => $arr){
        echo "<tr><td style='font-size: 13px; font-weight: bold; border-bottom: solid 1px #e6e6e6' colspan='2'><img src='table.png' />&nbsp;$table</td></tr>";
        foreach($arr as $k => $v){
            echo "<tr>";
            echo "<td style='background: #f0f0f0'>$k</td><td>";
            $t=array();
            foreach($v as $j => $val){
                $t[]="$j:$val";
            }
            echo implode(", ",$t);
            echo "</td></tr>";
        }
    }
    echo "</table>";
}else{
?>
<p>No database structure file found.
<?php
}
?>

<br /><br />
<input type="button" value="Go back" onclick="document.location.href='?del=1'" />&nbsp;<input type="submit" value="Create" style="float: right" /></div>
</div>
</form>
</div>
<?php
	break;
	case 'create':
?>
<h2 style="border-bottom: solid 1px #e6e6e6; padding-bottom: 5px">Module creation</h2>
<?php
/**
 ***********************************************
 * CREAMOS ARCHIVOS NECESARIOS
 ***********************************************
 */
        include_once("../engine/conf/config.php");
        $conf = $config;
        $conf['db_engine'] = strtolower($conf['db_engine']);
        $conf['db_mode'] = "simple";
        $engine=$conf['db_engine'];
		if(!empty($_POST['tabla'])){
			if(file_exists("db.config")){
				$config=json_decode(file_get_contents("db.config"));
				$config=get_object_vars($config);
			}
			$con=($engine=="mysql")
                ? mysql_connect($conf['db_host'],$conf['db_user'],$conf['db_pass'])
                : mssql_connect($conf['db_host'],$conf['db_user'],$conf['db_pass']);
            if($engine=="mysql"){
			    $info_schema=mysql_connect($conf['db_host'],$conf['db_user'],$conf['db_pass']);
			    mysql_select_db($conf['db_name'],$con);
			    mysql_select_db('information_schema',$info_schema);
            }else{
                mssql_select_db($conf['db_name']);
            }
            $subdomain=empty($conf['subdomain']) ? 'default' : $conf['subdomain'];

            $suffix= '_icf';
			// Modelo del sistema
			$file=file_get_contents("model.inc");
			// Despachador
			$dispatcher=file_get_contents($suffix=='_icf' ? 'ModuleController.inc' : 'dispatcher.inc');
			// PHP del listado
			$dsp_list=file_get_contents("dsp_list$suffix.inc");
			// PHP de Add Edit
			$dsp_ae=file_get_contents("dsp_ae$suffix.inc");
			// PHP de View
			$dsp_view=file_get_contents("dsp_view$suffix.inc");
			// PHP para el Save
			$dsp_save=file_get_contents("dsp_save$suffix.inc");
			// PHP para el eliminar
			$dsp_del=file_get_contents("dsp_del$suffix.inc");
			// TEMPLATE para el listado
			$tpl_list=file_get_contents("dsp_list.tpl");
			// TEMPLATE para Add/Edit
			$tpl_ae=file_get_contents("dsp_ae.tpl");
			// TEMPLATE para View
			$tpl_view=file_get_contents("dsp_view.tpl");
			// .HTACCESS
			$htaccess=file_get_contents("../.htaccess");
			// LAYOUT
			$layout=file_get_contents("../content/templates/".($subdomain=='default' ? 'default' : $subdomain)."/layout.html");
			
			// Recorremos cada una de las tablas que necesitan
			// instalador
			foreach($_POST['tabla'] as $i => $table){
				// Catálogo o menu para LAYOUT
                $catalogue.='<li><a href="{url:'.$table.'}">'.$table.'</a></li>'."\n";
				// Campos de la tabla
				$cols=array();
				$_cols=array();
                if($engine=="mysql"){
				    $result = mysql_query("SHOW COLUMNS FROM ".$conf['db_name'].".$table",$con) or die(mysql_error());
				    if (@mysql_num_rows($result) > 0) {
				        while ($col = mysql_fetch_assoc($result)) {
				            $cols[]=$col['Field'];
				            $_cols[]=$col;
				        }
				    }

                }else{
                    $result=mssql_query("SELECT COLUMN_NAME FROM ".$conf['db_name'].".INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'");
                    if (@mssql_num_rows($result) > 0) {
                        while ($col = mssql_fetch_assoc($result)) {
                            $cols[]=$col['COLUMN_NAME'];
                            $_cols[]=$col;
                        }
                    } 
                }
                $PK=NULL;
                // Primary KEY
                if($engine=="mysql"){
                    // MySQL
                    $p_query=mysql_query("
                        SELECT COLUMN_NAME 
                        FROM KEY_COLUMN_USAGE 
                        WHERE CONSTRAINT_SCHEMA = '".$conf['db_name']."'
                        AND TABLE_NAME = '$table' AND CONSTRAINT_NAME = 'PRIMARY'
                    ");
                    if(mysql_num_rows($p_query)>0){
                        $primk=mysql_fetch_assoc($p_query);
                        $PK=$primk['COLUMN_NAME'];
                    }
                }elseif($engine=="mssql"){
                    // MS SQL Server 2005 y 2000
                    $p_query=mssql_query("
                        SELECT KU.*
                        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC
                        INNER JOIN
                        INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KU
                        ON TC.CONSTRAINT_TYPE = 'PRIMARY KEY' AND
                        TC.CONSTRAINT_NAME = KU.CONSTRAINT_NAME
                        WHERE TC.TABLE_NAME = '$table'
                    ");
                    if(mssql_num_rows($p_query)>0){
                        $primk=mssql_fetch_assoc($p_query);
                        $PK=$primk['COLUMN_NAME'];
                    }
                }
				// Comments
				$comments=array();
                if($engine=="mysql"){
                    // MySQL... FACIL!
				    $comm_qry=mysql_query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM COLUMNS
					    WHERE TABLE_SCHEMA = '".$_REQUEST['db_name']."'
					    AND TABLE_NAME = '$table'
					    AND COLUMN_COMMENT != ''",$info_schema);
				    if(@mysql_num_rows($comm_qry)>0){
					    while($cc=@mysql_fetch_array($comm_qry)){
						    $comments[$cc['COLUMN_NAME']]=$cc['COLUMN_COMMENT'];
					    }
				    }
                }else{
                    // MS SQL Server... HVA!
                    $dbname=$dbsetup['db_name'];
                    if($conf['db_version']=='2005'){
                        // Version 2005
                        $comm_qry=mssql_query("
                            SELECT
                                [Table Name] = OBJECT_NAME(c.object_id), 
                                [Column Name] = c.name, 
                                [Description] = ex.value
                            FROM 
                                sys.columns c 
                            INNER JOIN 
                                sys.extended_properties ex
                            ON 
                                ex.major_id = c.object_id
                                and ex.minor_id = c.column_id
                                and ex.name = 'MS_Description'
                            WHERE
                                OBJECT_NAME(c.object_id) = '$table'                      
                        ")or die(mssql_get_last_message());
                    }elseif($conf['db_version']=='2000'){
                        // Version 2000
                        $comm_qry=mssql_query("
                            SELECT 
                                [Table Name] = i_s.TABLE_NAME, 
                                [Column Name] = i_s.COLUMN_NAME, 
                                [Description] = s.value 
                            FROM 
                                INFORMATION_SCHEMA.COLUMNS i_s 
                            LEFT OUTER JOIN 
                                sysproperties s 
                            ON 
                                s.id = OBJECT_ID(i_s.TABLE_SCHEMA+'.'+i_s.TABLE_NAME) 
                                AND s.smallid = i_s.ORDINAL_POSITION 
                                AND s.name = 'MS_Description' 
                            WHERE 
                                OBJECTPROPERTY(OBJECT_ID(i_s.TABLE_SCHEMA+'.'+i_s.TABLE_NAME), 'IsMsShipped')=0 
                                AND i_s.TABLE_NAME = '$table' 
                                AND s.value IS NOT NULL
                            ORDER BY 
                                i_s.TABLE_NAME, i_s.ORDINAL_POSITION                     
                        ")or die(mssql_get_last_message());
                    }
                    if(mssql_num_rows($comm_qry)>0){
                        while($cc=mssql_fetch_array($comm_qry)){
                            $comments[$cc['Column Name']]=$cc['Description'];
                        }
                    }
                }

				// Foreign Keys
				$fk=array();
                $fk_ext=array();
                if($engine=="mysql"){
				    $keys_qry=@mysql_query("SELECT 
					    CONSTRAINT_NAME,REFERENCED_COLUMN_NAME,REFERENCED_TABLE_NAME,COLUMN_NAME
					    FROM KEY_COLUMN_USAGE 
					    WHERE TABLE_SCHEMA = '".$_REQUEST['db_name']."' 
					    AND TABLE_NAME = '$table' 
					    AND CONSTRAINT_NAME != 'PRIMARY' ",$info_schema);
				    if(@mysql_num_rows($keys_qry)>0){
					    while($k=@mysql_fetch_array($keys_qry)){
						    $fk[$k['COLUMN_NAME']]=array(
							    'name'	=> $k['CONSTRAINT_NAME'],
							    'field'	=> $k['REFERENCED_COLUMN_NAME'],
							    'table'	=> $k['REFERENCED_TABLE_NAME']
						    );	
					    }
				    }
                    // Tablas que hacen referencia a la actual
                    $fkeys_qry=@mysql_query("SELECT 
                        CONSTRAINT_NAME,COLUMN_NAME,REFERENCED_COLUMN_NAME,TABLE_NAME
                        FROM KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = '".$_REQUEST['db_name']."' 
                        AND REFERENCED_TABLE_NAME = '$table' 
                        AND CONSTRAINT_NAME != 'PRIMARY' ",$info_schema);
                    if(@mysql_num_rows($fkeys_qry)>0){
                        while($k=@mysql_fetch_array($fkeys_qry)){
                            $fk_ext[$k['COLUMN_NAME']]=array(
                                'name'    => $k['CONSTRAINT_NAME'],
                                'field'    => $k['REFERENCED_COLUMN_NAME'],
                                'table'    => $k['TABLE_NAME']
                            );    
                        }
                    }
                }else{
                    // SQL Server
                    if($conf['db_version']=='2005'){
                        // Version 2005
                        $keys_qry=@mssql_query("
                            SELECT C.TABLE_CATALOG [PKTABLE_QUALIFIER],
                                   C.TABLE_SCHEMA [PKTABLE_OWNER],
                                   C.TABLE_NAME [PKTABLE_NAME],
                                   KCU.COLUMN_NAME [PKCOLUMN_NAME],
                                   C2.TABLE_CATALOG [FKTABLE_QUALIFIER],
                                   C2.TABLE_SCHEMA [FKTABLE_OWNER],
                                   C2.TABLE_NAME [FKTABLE_NAME],
                                   KCU2.COLUMN_NAME [FKCOLUMN_NAME],
                                   RC.UPDATE_RULE,
                                   RC.DELETE_RULE,
                                   C.CONSTRAINT_NAME [FK_NAME],
                                   C2.CONSTRAINT_NAME [PK_NAME],
                                   CAST(7 AS SMALLINT) [DEFERRABILITY]
                            FROM   INFORMATION_SCHEMA.TABLE_CONSTRAINTS C
                                   INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU
                                     ON C.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA
                                        AND C.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME
                                   INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS RC
                                     ON C.CONSTRAINT_SCHEMA = RC.CONSTRAINT_SCHEMA
                                        AND C.CONSTRAINT_NAME = RC.CONSTRAINT_NAME
                                   INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS C2
                                     ON RC.UNIQUE_CONSTRAINT_SCHEMA = C2.CONSTRAINT_SCHEMA
                                        AND RC.UNIQUE_CONSTRAINT_NAME = C2.CONSTRAINT_NAME
                                   INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU2
                                     ON C2.CONSTRAINT_SCHEMA = KCU2.CONSTRAINT_SCHEMA
                                        AND C2.CONSTRAINT_NAME = KCU2.CONSTRAINT_NAME
                                        AND KCU.ORDINAL_POSITION = KCU2.ORDINAL_POSITION
                            WHERE  
                                C.CONSTRAINT_TYPE = 'FOREIGN KEY'
                            AND
                                C.TABLE_NAME = '$table'
                        ");
                    }elseif($conf['db_version']=='2000'){
                        // Version 2000
                        $keys_qry=@mssql_query("
                            SELECT 
                                PKTABLE_NAME  = FK.TABLE_NAME, 
                                PKCOLUMN_NAME = CU.COLUMN_NAME, 
                                FKTABLE_NAME  = PK.TABLE_NAME, 
                                FKCOLUMN_NAME = PT.COLUMN_NAME, 
                                FK_NAME = C.CONSTRAINT_NAME 
                            FROM 
                                INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS C 
                                INNER JOIN 
                                INFORMATION_SCHEMA.TABLE_CONSTRAINTS FK 
                                    ON C.CONSTRAINT_NAME = FK.CONSTRAINT_NAME 
                                INNER JOIN 
                                INFORMATION_SCHEMA.TABLE_CONSTRAINTS PK 
                                    ON C.UNIQUE_CONSTRAINT_NAME = PK.CONSTRAINT_NAME 
                                INNER JOIN 
                                INFORMATION_SCHEMA.KEY_COLUMN_USAGE CU 
                                    ON C.CONSTRAINT_NAME = CU.CONSTRAINT_NAME 
                                INNER JOIN 
                                ( 
                                    SELECT 
                                        i1.TABLE_NAME, i2.COLUMN_NAME 
                                    FROM 
                                        INFORMATION_SCHEMA.TABLE_CONSTRAINTS i1 
                                        INNER JOIN 
                                        INFORMATION_SCHEMA.KEY_COLUMN_USAGE i2 
                                        ON i1.CONSTRAINT_NAME = i2.CONSTRAINT_NAME 
                                        WHERE i1.CONSTRAINT_TYPE = 'PRIMARY KEY' 
                                ) PT 
                                ON PT.TABLE_NAME = PK.TABLE_NAME
                                WHERE
                                    PK.TABLE_NAME = '$table'
                        ");
                    }
                    if(@mssql_num_rows($keys_qry)>0){
                        while($k=@mssql_fetch_array($keys_qry)){
                            $fk[$k['PKCOLUMN_NAME']]=array(
                                'name'     => $k['FK_NAME'],
                                'field'    => $k['FKCOLUMN_NAME'],
                                'table'    => $k['FKTABLE_NAME']
                            );    
                        }
                    }
                }
                
				$file_name=ucwords(str_replace("_"," ",$table));
				$file_name=str_replace(" ","",$file_name);
				$prefix=strtolower(substr($file_name,0,1));
				$_file=$file;
                // FK para la clase de entidad
                $fk_model=array();
                if(!empty($fk)){
                    foreach($fk as $k => $v){
                        if(empty($k) || empty($v['table'])) continue;
                        $cname=ucwords(str_replace("_"," ",$v['table']));
                        $cname=str_replace(" ","",$cname);
                        $fk_model[]="'$cname' => array( 'table' => '$v[table]', 'local_key' => '$k', 'foreign_key' => '$v[field]', 'instance' => null, 'rel_type' => Db::REL_MULTIPLE_TO_ONE )";   
                    }
                }
                // Tablas que hacen referencia a esta
                if(!empty($fk_ext)){
                    foreach($fk_ext as $k => $v){
                        if(empty($k) || empty($v['table'])) continue;
                        $cname=ucwords(str_replace("_"," ",$v['table']));
                        $cname=str_replace(" ","",$cname);
                        $fk_model[]="'$cname' => array( 'table' => '$v[table]', 'local_key' => '$v[field]', 'foreign_key' => '$k', 'instance' => null, 'rel_type' => Db::REL_ONE_TO_MULTIPLE )";   
                    }
                }
                $fk_model=implode(', ',$fk_model);
                $_file=str_replace("{fk_model}",$fk_model,$_file);
				$_file=str_replace("{table}",$table,$_file);
                $_file=str_replace("{multi_db}",($conf['db_mode']=="simple" ? '' : '$this->Db->useDb(\''.$dbsetup['db_name'].'\');'."\n"),$_file);
                $_file=str_replace("{pk}",$PK,$_file);
				$_file=str_replace("{class_name}",$file_name,$_file);
				$_file=str_replace("{file_name}",$file_name,$_file);
				$_file=str_replace("{file_desc}","Clase modelo de $file_name auto generada.",$_file);
				$_file=str_replace("{template_path}",TPL_HTML.$table,$_file);
				$_file=str_replace("{prefix}",$prefix,$_file);
				$f=fopen("../engine/model/".$file_name.".php","x");
				fwrite($f,$_file);
				fclose($f);
				
                // Creamos archivo req
                if($req=fopen("../controller/$subdomain/$table/req.ini","x")){
                    $req_txt="; ---\n; REQUIRED FILES\n; Module: $table\n; ---\n";
                    fwrite($req,$req_txt);
                    fclose($req);
                }
                
				// Creamos directorio en controller
				mkdir("../controller/$subdomain/$table");// or die("Unable to create dir: ../controller/$subdomain/$table. Make sure the parent directory is writeable");

                // --------------------------------------------------------------
				// Creamos Despachador [ModuleController | _dispatcher].php
                // --------------------------------------------------------------
				$_disp=$dispatcher;
                $_disp=str_replace("{table}",$table,$_disp);
				$d=fopen("../controller/$subdomain/$table/".($suffix=='_icf' ? 'ModuleController' : '_dispatcher').".php","x");
				fwrite($d,$_disp);
				fclose($d);

				// *******************************************
				// Creamos los PHPs
				// *******************************************
				// ---------------------------------------
				// PHP Add/Edit
				// ---------------------------------------
				$fk_code='';
				$fk_code_v='';
				$fk_join='';
				$fk_fields='';
				$js='';
				$img_code='';
				$img_code_s=''; // Codigo para el save de imagen simple
				$img_code_m=''; // Codigo para el save de imagenes multiples
				if(@!empty($config[$table]->image)){
					mkdir('../content/_files/images/'.$table); // Creamos directorio en images upload
					if(@empty($config[$table]->image->table)){
						// Una sola imagen
						$target=$class_name;
						$img_code.="\t".'// Imagen'."\n";
						$img_code.="\t".'if(!empty($row[\''.$config[$table]->image->field.'\'])){'."\n";
						$img_code.="\t\t".'$images[]=url(\'image?folder='.$table.'&src=\'.$row[\''.$config[$table]->image->field.'\']);'."\n";
						$img_code.="\t"."}\n";
						// Para el save
                        // Metodo nuevo
                        $img_code_s.=""."// Image upload handler\n";
                        $img_code_s.="".'handle_image_upload(array('."\n";
                        $img_code_s.="\t"."// Mode [single|multiple]\n";
                        $img_code_s.="\t"."'mode' => 'single',\n";
                        $img_code_s.="\t"."// Main table\n";
                        $img_code_s.="\t"."'main_table' => '".$table."',\n";
                        $img_code_s.="\t"."// Main table primary key field name\n";
                        $img_code_s.="\t"."'main_table_pk' => '".$PK."',\n";
                        $img_code_s.="\t"."// Main table image field name (in case of single image only)\n";
                        $img_code_s.="\t"."'main_table_img_field' => '".$config[$table]->image->field."',\n";
                        $img_code_s.="\t"."// POST var name where uploaded file names reside (swfupload)\n";
                        $img_code_s.="\t"."'uploaded_files_index' => 'uploaded_files',\n";
                        $img_code_s.="\t"."// In case of normal file upload, the name of the _FILES array\n";
                        $img_code_s.="\t"."'http_files_index' => '".$config[$table]->image->field."',\n";
                        $img_code_s.="\t"."// Main table actual id\n";
                        $img_code_s.="\t"."'main_table_id' => ".'$tmp_id'."));\n";
                        
                        // Modo anterior...
                        /*
						$img_code_s.="\t".'// Imagen'."\n";
						$img_code_s.="\t".'$Image=new Image();'."\n";
						$img_code_s.="\t".'if(!empty($_FILES)){'."\n";
						$img_code_s.="\t\t".'$Image->path=PATH_CONTENT_FILES.\'images/'.$table.'/\';'."\n";
						$img_code_s.="\t\t".'$img_name=$Image->upload($_FILES[\''.$config[$table]->image->field.'\'],rand_string(10),'.(empty($config[$table]->image->size) ? '500' : $config[$table]->image->size).');'."\n";
						$img_code_s.="\t\t".'if($img_name!=false){'."\n";
						$img_code_s.="\t\t\t".'$_POST[\''.$config[$table]->image->field.'\']=$img_name;'."\n";
						$img_code_s.="\t\t".'}'."\n";
						$img_code_s.="\t".'}'."\n";
                        */
					}else{
						// Multiples imagenes
						$target=str_replace(' ','',ucwords(str_replace('_',' ',$config[$table]->image->table)));
                        $img_code.="\t".'// Load JS files'."\n";
                        $img_code.="\t".'$this->load->js("swfupload.js");'."\n";
                        $img_code.="\t".'$this->load->js("swfupload.queue.js");'."\n";
                        $img_code.="\t".'$this->load->js("swfupload.handlers.js");'."\n";
                        $img_code.="\t".'$this->load->js("swfupload.fileprogress.js");'."\n\n";
                        
						$img_code.="\t".'// Imagenes'."\n";
						$img_code.="\t".'$'.$target.'=new '.$target.'();'."\n";
						$img_code.="\t".'$'.$target.'->select(NULL,"WHERE '.$prefix.'.'.$PK.' = \'".$row[\''.$PK.'\']."\'");'."\n";
						$img_code.="\t".'if($'.$target.'->rows>0){'."\n";
						$img_code.="\t\t".'$images=$'.$target.'->resultArray();'."\n";
						$img_code.="\t\t".'foreach($images as $i => $image){'."\n";
						$img_code.="\t\t\t".'$image[$i]["img_src"]=url(\'image?folder='.$table.'&src=\'.$'.$target.'->'.$config[$table]->image->field.');'."\n";
						$img_code.="\t\t}\n";
						$img_code.="\t}\n";
						// Para el save miltuple
                        // Metodo nuevo
                        $img_code_m.=""."// Image upload handler\n";
                        $img_code_m.="".'handle_image_upload(array('."\n";
                        $img_code_m.="\t"."// Mode [single|multiple]\n";
                        $img_code_m.="\t"."'mode' => 'multiple',\n";
                        $img_code_m.="\t"."// Main table\n";
                        $img_code_m.="\t"."'main_table' => '".$table."',\n";
                        $img_code_m.="\t"."// Image table in case of multiple images per row\n";
                        $img_code_m.="\t"."'image_table' => '".$config[$table]->image->table."',\n";
                        $img_code_m.="\t"."// Main table primary key field name\n";
                        $img_code_m.="\t"."'main_table_pk' => '".$PK."',\n";
                        $img_code_m.="\t"."// Image table primary key field name\n";
                        $img_code_m.="\t"."'image_table_pk' => '".table_primary_key($config[$table]->image->table,$conf['db_name'])."',\n";
                        $img_code_m.="\t"."// Main table image field name (in case of single image only)\n";
                        $img_code_m.="\t"."'main_table_img_field' => '',\n";
                        $img_code_m.="\t"."// Image table image field name (in case of multiple images only)\n";
                        $img_code_m.="\t"."'image_table_img_field' => '".$config[$table]->image->field."',\n";
                        $img_code_m.="\t"."// POST var name where uploaded file names reside (swfupload)\n";
                        $img_code_m.="\t"."'uploaded_files_index' => 'uploaded_files',\n";
                        $img_code_m.="\t"."// In case of normal file upload, the name of the _FILES array\n";
                        $img_code_m.="\t"."'http_files_index' => '".$config[$table]->image->field."',\n";
                        $img_code_m.="\t"."// Main table actual id\n";
                        $img_code_m.="\t"."'main_table_id' => ".'$tmp_id'."));\n";
                        
                        
                        // Metodo viejo
						/*$img_code_m.="\t".'// Imagenes'."\n";
						$img_code_m.="\t".'$Image=new Image();'."\n";
						$img_code_m.="\t".'$'.$target.'=new '.$target.'();'."\n"; // Clase de tabla de imagenes
						$img_code_m.="\t".'if(empty($_FILES[\''.$config[$table]->image->field.'\'][\'error\'])){'."\n";
						$img_code_m.="\t\t".'$Image->path=PATH_CONTENT_FILES.\'images/'.$table.'/\';'."\n";
						$img_code_m.="\t\t".'$tmp_id=empty($_POST[\''.$PK.'\']) ? $Db->maxVal(\''.$table.'\',\''.$PK.'\') : $_POST[\''.$PK.'\'];'."\n";
						$img_code_m.="\t\t".'$Db->query("SELECT * FROM '.$config[$table]->image->table.' WHERE '.table_primary_key($config[$table]->image->table,$dbsetup['db_name']).' = \'$tmp_id\'");'."\n";
						if(@!empty($config[$table]->image->max)) // Si tiene límite de imagenes, limitamos
							$img_code_m.="\t\t".'if($Db->filas()<'.$config[$table]->image->max.'){'."\n";
						$img_code_m.="\t\t".'if(is_array($_FILES[\''.$config[$table]->image->field.'\'][\'name\'])){'."\n";
						$img_code_m.="\t\t\t".'foreach($_FILES[\''.$config[$table]->image->field.'\'][\'name\'] as $j => $file){'."\n";
						$img_code_m.="\t\t\t\t".'$new_img_name= $Db->maxVal(\''.$config[$table]->image->table.'\',\''.table_primary_key($config[$table]->image->table,$dbsetup['db_name']).'\')+1;'."\n";
						$img_code_m.="\t\t\t\t".'$img_name=$Image->upload($_FILES[\''.$config[$table]->image->field.'\'],$new_img_name,'.(empty($config[$table]->image->size) ? '500' : $config[$table]->image->size).',false,true,$j);'."\n";
						$img_code_m.="\t\t\t\t".'if($img_name!=false){'."\n";
						$img_code_m.="\t\t\t\t\t".'$'.$target.'->'.$PK.'=$tmp_id;'."\n";
						$img_code_m.="\t\t\t\t\t".'$'.$target.'->'.$config[$table]->image->field.'=$img_name;'."\n";
						$img_code_m.="\t\t\t\t\t".'$'.$target.'->save();'."\n";
						$img_code_m.="\t\t\t\t".'}'."\n";
						$img_code_m.="\t\t\t".'}'."\n";
						$img_code_m.="\t\t".'}else{'."\n";
						$img_code_m.="\t\t\t".'$new_img_name= $Db->maxVal(\''.$config[$table]->image->table.'\',\''.table_primary_key($config[$table]->image->table,$dbsetup['db_name']).'\')+1;'."\n";
						$img_code_m.="\t\t\t".'$img_name=$Image->upload($_FILES[\''.$config[$table]->image->field.'\'],$new_img_name,'.(empty($config[$table]->image->size) ? '500' : $config[$table]->image->size).',false,true,$j);'."\n";
						$img_code_m.="\t\t\t".'if($img_name!=false){'."\n";
						$img_code_m.="\t\t\t\t".'$'.$target.'->'.$PK.'=$tmp_id;'."\n";
						$img_code_m.="\t\t\t\t".'$'.$target.'->'.$config[$table]->image->field.'=$img_name;'."\n";
						$img_code_m.="\t\t\t\t".'$'.$target.'->save();'."\n";
						$img_code_m.="\t\t\t".'}'."\n";
						$img_code_m.="\t\t".'}'."\n";
						if(@!empty($config[$table]->image->max)) // Cerramos limite de imágenes
							$img_code_m.="\t\t".'}'."\n";
						$img_code_m.="\t".'}'."\n";*/
					}
				}
				if(!empty($fk)){
					$fk_code.="\n".'// Foreign Keys'."\n"; // Foreign keys para add edit
					$fk_coke_v="\n".'// Foreign Keys'."\n"; // Foreign keys para view
					$fk_join=''; // Joins para las views
					$opt=array();
					$fk_fields=$prefix.".*";
					foreach($fk as $k => $v){
						$class_name=str_replace(' ','',ucwords(str_replace('_',' ',$v['table'])));
						if(empty($class_name))
							continue;
						// ADD / EDIT
						$fk_code.="\t".'$'.$class_name.'=new '.$class_name.'();'."\n";
						$fk_code.="\t".'$'.$class_name.'->select();'."\n";
						$fk_code.="\t".'if($'.$class_name.'->rows>0){'."\n";
						$fk_code.="\t\t".'$fk_'.$k.'=$'.$class_name.'->resultArray();'."\n";
						//$fk_code.="\t\t".'foreach($fk_'.$k.' as $i => $fk_row){'."\n";
						//$fk_code.="\t\t\t".'$fk_'.$k.'[$i]["selected"]=($fk_row[\''.$k.'\']==$row['.$k.']) ? \'selected="selected"\' : \'\';'."\n";
						//$fk_code.="\t\t".'}'."\n";
						$fk_code.="\t".'}'."\n";
						// VIEW
						$fk_code_v.="\t".'$'.$class_name.'=new '.$class_name.'();'."\n";
						$fk_code_v.="\t".'$'.$class_name.'->select($row[\''.$k.'\']);'."\n";
						$fk_code_v.="\t".'if($'.$class_name.'->rows>0){'."\n";
						$fk_code_v.="\t\t".'$fk_'.$k.'=$'.$class_name.'->resultArray();'."\n";
						$fk_code_v.="\t\t".'if(empty($row[\''.$k.'\']))'."\n";
						$fk_code_v.="\t\t\t".'empty_array($fk_'.$k.');'."\n";
						$fk_code_v.="\t".'}'."\n";
						// JOINS
						$fk_join.="$".$file_name."->join(\"".$v['table']." ON ".$prefix.".".$k." = ".$v['table'].".".$v['field']."\",false);\n";
						// Opciones
						$col_com=!empty($comments[$k]) ? $comments[$k] : $k; 
						$opt=get_parse_json($col_com);
						$opt=get_object_vars($opt);
						// Si tenemos un show y el show es diferente al id del campo...
						if(!empty($opt['show']) && $opt['show']!=$k)
							$fk_fields.=",".$v['table'].".".$opt['show']." as ".$v['table']."_".$opt['show'];
					}
					$fk_code.="\n";
				}
                
				$_ae=$dsp_ae;
				$_ae=str_replace("{table}",$table,$_ae);
                $_ae=str_replace("{pk}",$PK,$_ae);
				$_ae=str_replace("{class_name}",$file_name,$_ae);
				$_ae=str_replace("{template_path}",$file_name."->tplPath",$_ae);
				$_ae=str_replace("{fk_code}",$fk_code,$_ae);
				$_ae=str_replace("{img_code}",$img_code,$_ae);
				$ae=fopen("../controller/$subdomain/$table/dsp_ae.php","x");
				fwrite($ae,$_ae);
				fclose($ae);
				// ---------------------------------------
				// PHP View
				// ---------------------------------------
				$_view=$dsp_view;
				$_view=str_replace("{table}",$table,$_view);
                $_view=str_replace("{pk}",$PK,$_view);
				$_view=str_replace("{class_name}",$file_name,$_view);
				$_view=str_replace("{template_path}",$file_name."->tplPath",$_view);
				//$_view=str_replace("{fk_code}",$fk_code_v,$_view);
				$_view=str_replace("{img_code}",$img_code,$_view);
                $_view=str_replace("{joins}",$fk_join,$_view);
				$_view=str_replace("{fk_fields}",empty($fk_fields) ? "*" : $fk_fields,$_view);
				$view=fopen("../controller/$subdomain/$table/dsp_view.php","x");
				fwrite($view,$_view);
				fclose($view);
				// ---------------------------------------
				// PHP Listado
				// ---------------------------------------
				$_list=$dsp_list;
				$_list=str_replace("{class_name}",$file_name,$_list);
				$_list=str_replace("{template_path}",$file_name."->tplPath",$_list);
				$code='';
				$search='';
				$search_fields='';
				$col_ord='';
				$col_class='';
				$opt=array();
				foreach($cols as $i => $col){
					// Verificamos comments y opciones
					$col_header=!empty($comments[$col]) ? $comments[$col] : $col; 
					$opt=get_parse_json($col_header);
					$opt=get_object_vars($opt);
					// Si tenemos otro campo para mostrar, ordenamos por ese campo,
					// no por el campo de id (tabla.campo)
					if(!empty($fk[$col]) && !empty($opt['show']))
						$col=$fk[$col]['table'].".".$opt['show'];
                    // Formato de search:
                    // WHERE [t.campo | fk_table.campo] LIKE '%search%' OR ...
					$search.=(!empty($search) ? ' OR ' : '').$prefix.'.'.$col.' LIKE \'%{0}%\'';
					$code.='if(!empty($this->request->'.$col.'))'."\n";
					$code.="\t".'$'.$file_name.'->where("'.$col.' = \'{0}\'",$this->request->'.$col.');'."\n";
					
					// Verificamos que la columna no este escondida o que no se necesite mostrar
					if($opt['hidden']!=true && $opt['hidden']!="list"){
						$col_class.="\t".'$col_order[\'ClassOrd'.$i.'\'] = ($this->request->ord == "'.$col.'" ? (strtoupper($this->request->ord_ref) == \'ASC\' ? \'desc\' : \'asc\') : \'\');'."\n";
						$col_ord.="\t".'$col_order[\'UrlOrd'.$i.'\'] = url(\''.$table.'/list\'.$_get.(empty($_get) ? \'?\' : \'\').\'&ord='.$col.'&ord_ref=\'.($this->request->ord == "'.$col.'" ? (strtoupper($this->request->ord_ref) == \'ASC\' ? \'desc\' : \'asc\') : \'desc\'));'."\n";
					}
				}
				$_list=str_replace("{column_order}",$col_class.$col_ord,$_list);
				$_list=str_replace("{search_fields}",$search,$_list);
				$_list=str_replace("{joins}",$fk_join,$_list);
				$_list=str_replace("{fk_fields}",empty($fk_fields) ? "*" : '"'.$fk_fields.'"',$_list);
				$_list=str_replace("{filtros}",$code,$_list);
				$l=fopen("../controller/$subdomain/$table/dsp_list.php","x");
				fwrite($l,$_list);
				fclose($l);
				// ---------------------------------------
				// PHP Save
				// ---------------------------------------
				$_s=$dsp_save;
				$_s=str_replace("{table}",$table,$_s);
                $_s=str_replace("{pk}",$PK,$_s);
				$_s=str_replace("{class_name}",$file_name,$_s);
				$_s=str_replace("{template_path}",$file_name."->tplPath",$_s);
				$_s=str_replace("{img_code_s}",$img_code_s,$_s);
				$_s=str_replace("{img_code_m}",$img_code_m,$_s);
				$s=fopen("../controller/$subdomain/$table/act_save.php","x");
				fwrite($s,$_s);
				fclose($s);
				// ---------------------------------------
				// PHP Delete
				// ---------------------------------------
				$_d=$dsp_del;
				$_d=str_replace("{table}",$table,$_d);
                $_d=str_replace("{pk}",$PK,$_d);
				$_d=str_replace("{class_name}",$file_name,$_d);
				$_d=str_replace("{template_path}",$file_name."->tplPath",$_d);
				$del=fopen("../controller/$subdomain/$table/act_del.php","x");
				fwrite($del,$_d);
				fclose($del);
				
				// *******************************************
				// Creamos los templates
				// *******************************************
                $tpldir=$subdomain=="default" ? 'default' : $subdomain;
                if(!is_dir("../content/templates/$tpldir")){
                    mkdir("../content/templates/$tpldir");
                    mkdir("../content/templates/$tpldir/html");
                }
				mkdir("../content/templates/$tpldir/html/$table");
				// ---------------------------------------
				// TEMPLATE Lista
				// ---------------------------------------

				$_tpllist=$tpl_list;
				$_tpllist=str_replace("{table}",$table,$_tpllist);
				$tpllist=fopen("../content/templates/$tpldir/html/$table/dsp_list.html","x");
				$fields='';//'<td><input type="checkbox" name="'.$PK.'[]" value="{$'.$PK.'}" /></td>';
				$headers='';
                $filter_fields = '';
				$opt=array();
				foreach($cols as $i => $col){
					$col_header=!empty($comments[$col]) ? $comments[$col] : $col; 
					$opt=get_parse_json($col_header);
					if($opt!=false){
						$opt=get_object_vars($opt);
						$opt['readonly']=empty($opt['readonly']) ? '' : 'readonly';
						// Quitamos el json del comment para sacar el header
						$col_header=str_replace(substr($col_header,0,strrpos($col_header,'}')+1),'',$col_header);
						if(empty($col_header))
							$col_header=$col;
					}
					// Verificamos que el campo no este escondido y si se pueda mostrar
					if($opt['hidden']!="list" && $opt['hidden']!="true"){
						$headers.='<td><a href="{$col_order.UrlOrd'.$i.'}" class="{$col_order.ClassOrd'.$i.'}">'.$col_header.'</a></td>'."\n";
						$filter_fields .= '<td><input type="text" name="'. $col .'" value="{$system.request.'.$col.'}" /></td>';
						// Checamos si el campo es una foreign key, si es
						// verificamos si quiere que mostremos un campo diferente
						// al id de la table (tabla.campo) para evitar conflictos de nombre
						if(!empty($fk[$col]) && !empty($opt['show']))
							$col=$fk[$col]['table']."_".$opt['show'];
						$fields.='<td>{$row.'.$col.'}</td>'."\n";
					}
				}
				$headers.='<td class="options-tab" style="text-align: right">Opciones</td>';
				$fields.='<td class="options-tab" style="text-align: right"><a style="margin-right: 7px" href="{url:'.$table.'/view?id=$row.'.$PK.'}"><img src="{$system.path(img)}view.png" style="border: none;" /></a><a href="{url:'.$table.'/edit?id=$row.'.$PK.'}" style="margin-right: 7px"><img src="{$system.path(img)}edit.png" style="border: none;" /></a><a href="{url:'.$table.'/del?id=$row.'.$PK.'}" onclick="return confirm(\'Delete row?\');"><img src="{$system.path(img)}delete.png" style="border: none;" /></a></td>';
				$_tpllist=str_replace("{headers}",$headers,$_tpllist);
				$_tpllist=str_replace("{campos}",$fields,$_tpllist);
				$_tpllist=str_replace("{filter_fields}",$filter_fields,$_tpllist);
				//fwrite($tpllist, pack("CCC",0xef,0xbb,0xbf));
				fwrite($tpllist,$_tpllist);
				fclose($tpllist);
				// ---------------------------------------
				// Ver
				// ---------------------------------------
				$fields='';
				$_tplview=$tpl_view;
				$_tplview=str_replace("{table}",$table,$_tplview);
                $_tplview=str_replace("{pk}",$PK,$_tplview);
				$tplview=fopen("../content/templates/$tpldir/html/$table/dsp_view.html","x");

				foreach($cols as $i => $col){
					$col_header=!empty($comments[$col]) ? $comments[$col] : $col;
					$opt=get_parse_json($col_header);
					if($opt!=false){
						$opt=get_object_vars($opt);
						// Quitamos el json del comment para sacar el header
						$col_header=str_replace(substr($col_header,0,strrpos($col_header,'}')+1),'',$col_header);
						if(empty($col_header))
							$col_header=$col;
					}
					if(@$config[$table]->image->field==$col) // Si es el campo de imagen, lo saltamos, lo incluiremos adelante
						continue;
					if($opt['hidden']=="true"){
						// Oculto
						$fields.='';
					}else{
                                                if(!empty($fk[$col]) && !empty($opt['show']))
							$col=$fk[$col]['table']."_".$opt['show'];
						$fields.='<tr>'."\n";
                                                $fields.='<td class="field-name">'.$col_header.'</td>'."\n";
						$fields.='<td>{$row.'.$col.'}</td>'."\n";
						$fields.='</tr>'."\n";
					}
				}
				$_tplview=str_replace("{fields}",$fields,$_tplview);
                                
				// ---------------------------------------
				// Add edit
				// ---------------------------------------
				$fields='';
				
				$_tplae=$tpl_ae;
				$_tplae=str_replace("{table}",$table,$_tplae);
				$tplae=fopen("../content/templates/$tpldir/html/$table/dsp_ae.html","x");
				foreach($cols as $i => $col){
					$col_header=!empty($comments[$col]) ? $comments[$col] : $col;
					$opt=get_parse_json($col_header);
					if($opt!=false){
						$opt=get_object_vars($opt);
						$opt['readonly']=empty($opt['readonly']) ? '' : 'readonly="readonly"';
						// Quitamos el json del comment para sacar el header
						$col_header=str_replace(substr($col_header,0,strrpos($col_header,'}')+1),'',$col_header);
						if(empty($col_header))
							$col_header=$col;
					}
					$php_val=''; // Validador de PHP
					$js_val=''; // Validador de JS
					if(empty($opt['validate']) && !empty($opt['check'])) // alias...
						$opt['validate']=$opt['check'];
					if($opt['validate']=="empty" || $opt['validate']=="no-empty"){
						$php_val='_';
						$js_val=' validate="no-empty" ';
					}elseif($col=="email" || $col=="mail" || $col=="correo"){
						$php_val='@';
						$js_val=' validate="email" ';
					}
					
					if(@$config[$table]->image->field==$col) // Si es el campo de imagen, lo saltamos, lo incluiremos adelante
						continue;
					if($opt['hidden']=="true"){
						// Oculto
						$fields.='<input type="hidden" name="'.$col.'" id="'.$col.'" value="{$'.$col.'}" />'."\n";
					}else{
						$fields.='<tr>'."\n";
						$fields.='<td class="field-name">'.$col_header.'</td>'."\n";
						switch($_cols[$i]['Type']){
							default:
								$fields.='<td>'."\n";
								if(!empty($fk[$col]) && !empty($fk[$col]['name']) && !empty($fk[$col]['field']) && !empty($fk[$col]['table'])){
									$fields.='<select name="'.$php_val.$col.'" id="'.$col.'" current="{$row.'.$col.'}" '.
										$opt['readonly'].$js_val.'>'."\n";
									$fields.='{loop:$fk_'.$col.',item=fk_row,key=j}'."\n";
									$fields.='<option value="{$fk_row.'.$col.'}">{$fk_row.'.(!empty($opt['show']) ? $opt['show'] : $col).'}</option>'."\n";
									$fields.='{/loop}'."\n";
									$fields.='</select>'."\n";
								}else{
									if(!empty($opt['input'])){
										if($opt['input']=='checkbox' || $opt['input']=='check'){
											$fields.='<input type="checkbox" name="'.$php_val.$col
												.'" id="'.$col.'" value="1" '.$opt['readonly'].$js_val.' />'."\n";
										}
									}else{
										$fields.='<input type="text" name="'.$php_val.$col.'" id="'.$col.'" value="{$row.'.$col.'}" '.$opt['readonly'].$js_val.' />'."\n";
									}
								}
								$fields.='</td>'."\n";
								break;
							case 'text':
							case 'tinytext':
							case 'mediumtext':
							case 'longtext':
							case 'tinyblob':
							case 'mediumblob':
							case 'longblob':
								$fields.='<td><textarea name="'.$php_val.$col.'" id="'.$col.'" '.$opt['readonly'].$js_val.' >{$row.'.$col.'}</textarea></td>';
								break;	
							case 'datetime':
							case 'date':
								$fields.='<td><input type="text" name="'.$php_val.$col.'" id="'.$col.'" value="{$row.'.$col.'}" '.$opt['readonly'].$js_val.' /></td>'."\n";
								$js.="\t".'$("#'.$col.'").datepicker();'."\n";
								$js.="\t".'$("#'.$col.'").datepicker(\'option\', {dateFormat: \'yy-mm-dd\'});'."\n";
								break;
						}
						$fields.='</tr>'."\n";
					}
				}
				// ---------------------------------------
				// Revisamos imagen del archivo de configuracion
				// para la tabla actual
				// ---------------------------------------
				$fields_img='';
				if(@!empty($config[$table]->image)){
					if(@empty($config[$table]->image->table)){
						// El archivo de imagen se encuentra en la misma tabla
						// unicamente se puede agregar una imagen
                        $fields_img.='<tr>'."\n";
						$fields_img.='{if:$images}'."\n";
						$fields_img.='<td class="field-name">Imagen</td><td><div class="img-cont"><img src="{$images.0}" /><a href="{url:'.$table.'/del_img?'.$PK.'=$row.'.$PK.'}">[Eliminar]</a></div></td>'."\n";
						$fields_img.='{else}'."\n";
						$fields_img.='<td class="field-name">Cargar una imagen</td><td><input type="file" name="'.$config[$table]->image->field.'" /></td>'."\n";
						$fields_img.='{/if}'."\n";
                        $fields_img.='</tr>'."\n";
					}else{
						// El registro puede mantener varias imagenes
						// JS
						/* Metodo anterior
                        $js.='$("#agregar_imagen").click(function(){';
						$js.="\t".'$("#img_cont").append(\'<div class="img-upload"><input type="file" name="'.$config[$table]->image->field.'[]" />\'); return false;'."\n";
						$js.='});'."\n";
                        */
                        
                        $js.='$("a.del_img").click(function(){'."\n";
                        $js.='var self = this;'."\n";
                        $js.='if(confirm(Lang.confirm_delete_image)){'."\n";
                        $js.='$(self).parent().css("opacity","0.5");'."\n";
                        $js.='$.post($(this).attr("href"),{"response":"json"},function(data){'."\n";
                        $js.='if(data.result == true){'."\n";
                        $js.='$(self).parent().fadeOut("slow");'."\n";
                        $js.='}'."\n";
                        $js.='},"json");'."\n";
                        $js.='}'."\n";
                        $js.='return false;'."\n";
                        $js.='})'."\n";
                        
                        $js_out.='window.onload = function() {'."\n";
                        $js_out.='swfu = new SWFUpload({'."\n";
                        $js_out.='flash_url : "{$system.path(swf)}swfupload.swf",'."\n";
                        $js_out.='upload_url: "{url:upload/tmp}",'."\n";
                        $js_out.='post_params: {"PHPSESSID" : "{$system.session_id}"},'."\n";
                        $js_out.='file_size_limit : "1 MB",'."\n";
                        $js_out.='file_types : "*.png;*.jpg;*.gif",'."\n";
                        $js_out.='file_types_description : Lang.all_files,'."\n";
                        $js_out.='file_upload_limit : 10,'."\n";
                        $js_out.='file_queue_limit : 10,'."\n";
                        $js_out.='custom_settings : {'."\n";
                        $js_out.='progressTarget : "upload_progress",'."\n";
                        $js_out.='cancelButtonId : "cancel_upload"'."\n";
                        $js_out.='},'."\n";
                        $js_out.='debug: false,'."\n";
                        $js_out.='file_post_name: "file",'."\n";
                        $js_out.='button_width: "250",'."\n";
                        $js_out.='button_height: "29",'."\n";
                        $js_out.='button_placeholder_id: "upload_button",'."\n";
                        $js_out.='button_text: \'<span class="theFont">\'+Lang.select_files+\'</span>\','."\n";
                        $js_out.='button_text_style: ".theFont { font-size: 16; font-family: arial; text-decoration: underline; color: #1464F4; }",'."\n";
                        $js_out.='button_text_left_padding: 12,'."\n";
                        $js_out.='button_text_top_padding: 3,'."\n";
                        $js_out.='file_queued_handler : fileQueued,'."\n";
                        $js_out.='file_queue_error_handler : fileQueueError,'."\n";
                        $js_out.='file_dialog_complete_handler : fileDialogComplete,'."\n";
                        $js_out.='upload_start_handler : uploadStart,'."\n";
                        $js_out.='upload_progress_handler : uploadProgress,'."\n";
                        $js_out.='upload_error_handler : uploadError,'."\n";
                        $js_out.='upload_success_handler : uploadSuccess,'."\n";
                        $js_out.='upload_complete_handler : uploadComplete,'."\n";
                        $js_out.='queue_complete_handler : queueComplete'."\n";
                        $js_out.='});'."\n";
                        $js_out.='};'."\n";
                        
                        $fields_img.='<tr>'."\n";
                        $fields_img.='<td class="field-name">Imagenes</td>'."\n";
                        $fields_img.='<td>'."\n";
                        $fields_img.='<div id="img_cont">'."\n";
                        $fields_img.='{if:$images}'."\n";
                        $fields_img.='{loop:$images,item=img,key=j}'."\n";
                        $fields_img.='<div class="img-cont"><img src="{url:image?src=$img.imagen&width=150&folder='.$table.'}" /><a class="del_img" href="{url:image/delete?main_table='.$table.'&image_table='.$config[$table]->image->table.'&main_table_pk='.$PK.'&image_table_pk='.table_primary_key($config[$table]->image->table,$conf['db_name']).'&image_field='.$config[$table]->image->field.'&id=$img.'.table_primary_key($config[$table]->image->table,$conf['db_name']).'&fwd_module='.$table.'&fwd_action=edit&fwd_id=$row.'.$PK.'}">{$system.lang.delete}</a></div>'."\n";
                        $fields_img.='{/loop}'."\n";
                        $fields_img.='{/if}'."\n";
                        $fields_img.='</div>'."\n";
                        $fields_img.='<div>'."\n";
                        $fields_img.='<div class="fieldset flash" id="upload_progress"></div>'."\n";
                        $fields_img.='<div>'."\n";
                            $fields_img.='<div id="upload_button"></div>'."\n";
                            $fields_img.='<input id="cancel_upload" type="button" value="Cancelar todas" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px;" />'."\n";
                        $fields_img.='</div>'."\n";
                        $fields_img.='</div>'."\n";
                        $fields_img.='</td>'."\n";
                        $fields_img.='</tr>'."\n";
                        
                        // Metodo antiguo
                        /*
						$fields_img.='{if:$images}'."\n";
						$fields_img.='<td class="field-name">Imagenes</td>';
						$fields_img.='<td><div id="img_cont">'."\n";
						$fields_img.='{loop:$images,item=img,key=j}'."\n";
						$fields_img.='<div class="img-cont"><img src="{$img.img_src}" /><a href="{url:'.$table.'/del_img?'.table_primary_key($config[$table]->image->table,$dbsetup['db_name']).'={$img.'.table_primary_key($config[$table]->image->table,$dbsetup['db_name']).'}}">[Eliminar]</a></div>'."\n";
						$fields_img.='{/loop}'."\n".'</div><div style="margin-top: 10px">{snippet src="nav/spt_button2.tpl" link="#" label="Agregar imagen" class="button_insert" id="agregar_imagen"}</div></td>'."\n";
						$fields_img.='{else}'."\n";
						$fields_img.='<td class="field-name">Imagenes</td>';
						$fields_img.='<td><div id="img_cont"><input type="file" name="'.$config[$table]->image->field.'[]" /></div><div style="float: left; margin-top: 10px"><a class="buttonst" href="#" id="agregar_imagen"><span class="button_left button_insert"></span><span class="button_middle">Agregar imagen</span><span class="button_right"></span></a></div></td>'."\n";
						$fields_img.='{/if}'."\n";
						$fields_img.='</tr>'."\n";
                        */
					}
				}
				// ---------------------------------------
				// Reemplazamos el JS
				// ---------------------------------------
				if(!empty($js) || !empty($js_out)){
					$_js=$js;
					$js="<script language=\"javascript\">\n";
					$js.='$(document).ready(function(){'."\n";
					$js.=$_js."\n";
					$js.="});\n";
                    if(!empty($js_out))
                        $js.=$js_out."\n";
					$js.="</script>";	
				}
				$_tplae=str_replace("{JS}",$js,$_tplae);
				$_tplae=str_replace("{fields}",$fields.$fields_img,$_tplae);
				$_tplview=str_replace("{fields_img}",$fields_img,$_tplview); // Para las imagenes en view
				//fwrite($tplae, pack("CCC",0xef,0xbb,0xbf));
				fwrite($tplae,$_tplae);
				fclose($tplae);
				fwrite($tplview,$_tplview);
				fclose($tplview);
				$htaccess=str_replace('{PHP_SELF}',
					str_replace('setup.php','index.php',
						str_replace('install/','',$_SERVER['PHP_SELF'])),$htaccess);
				$_htaccess=fopen('../.htaccess','w');
				fwrite($_htaccess,$htaccess);
				fclose($_htaccess);
				
				?><div>Module <b><?php echo $file_name?></b> created.</div><?php
			}
			$_layout=fopen("../content/templates/$tpldir/layout.html","w");
            if(!file_exists("../content/templates/$tpldir/layout.html")){
                $lo=fopen("../content/templates/$tpldir/layout","w");
                fwrite($lo,$layout);
                fclose($lo);
            }
			$layout=str_replace('{{catalogue}}',$catalogue,$layout);
			fwrite($_layout,$layout);
			fclose($_layout);
			?><div>Template <b>layout.html</b> updated.</div><?php
		}

        // -----------------------------------
        // Plugins
        // -----------------------------------
        //error_reporting(E_ALL ^ E_NOTICE);
        if(!empty($_POST['plugin'])){
            if(is_array($_POST['plugin'])){
                foreach($_POST['plugin'] as $i => $plugin){
                    $plugin_name=substr($plugin,0,-4);
                    $installed=0;
                    echo "<br /><span style='background-color: #e6e6e6'>Instalando componente <b>$plugin</b>...</span>";
                    $zf=str_replace('install','plugins',getcwd() ."\\".$plugin);
                    $zip = zip_open($zf);
                    if ($zip) {
                        while ($zip_entry = zip_read($zip)) {
                            $name=zip_entry_name($zip_entry);
                            $fb=explode('/',$name);
                            if(sizeof($fb)<2)
                                continue;
                            // El plugin debe tener al menos una de las cuatos carpetas 
                            // para poder instalarlo: bin, controller, setup y html   
                            if($fb[0]!='bin' && $fb[0]!='controller' && $fb[0]!='setup' && $fb[0]!='html' && $fb[0]!='lib')
                                continue;
                            // Determinamos la ruta destino 
                            switch($fb[0]){
                                case 'bin':
                                    $dest_path='../engine/bin/';
                                    break;
                                case 'lib':
                                    $dest_path='../engine/lib/';
                                    break;
                                case 'controller':
                                    $dest_path='../controller/'.$subdomain.'/'.$plugin_name.'/';
                                    break;
                                case 'setup':
                                    $dest_path='../engine/core/';
                                    break;
                                case 'html':
                                    $dest_path='../content/templates/'.$tpldir.'/html/'.$plugin_name.'/';
                                    break;
                            }
                            // Si no existe el directorio, lo creamos
                            if(!is_dir($dest_path)){
                                mkdir($dest_path);
                            }
                            if(file_exists($dest_path.$fb[1])){
                                echo "<br />>> El archivo <span style='color: green'>".$dest_path.'<b>'.$fb[1]."</b></span> no se pudo instalar porque ya existe.";
                            }
                            // Creamos el archivo con los contenidos del archivo del paquete
                            if (zip_entry_open($zip, $zip_entry, "r")) {
                                $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                                // Setup no crea el archivo, lo edita
                                if($fb[0]=='setup'){
                                    switch($fb[1]){
                                        case 'db.sql': // SQL
                                            $query=($conf['db_engine']=='mysql') ? mysql_query($buf) : mssql_query($buf);
                                            if($query)
                                                echo "<br />>> Sentencia SQL ejecutada correctamente.";
                                            else
                                                echo "<br />>> <span style='color: red'>No se pudo ejecutar la sentencia SQL.</span>";
                                            break;
                                        case 'misc.php': // Miscelaneous
                                            $tmp=fopen($dest_path.$fb[1],'a');
                                            $buf=str_replace('<?php','',$buf);
                                            $buf=str_replace('<?','',$buf);
                                            $buf=str_replace('?>','',$buf);
                                            $buf="\n\n".'// PLUG-IN: '.substr($plugin,0,-4)."\n".$buf;
                                            fwrite($tmp,$buf);
                                            break;
                                        case 'acl.php': // Access control list
                                            $tmp=fopen($dest_path.$fb[1],'a');
                                            $buf=str_replace('<?php','',$buf);
                                            $buf=str_replace('<?','',$buf);
                                            $buf=str_replace('?>','',$buf);
                                            $buf="\n\n".'// PLUG-IN: '.substr($plugin,0,-4)."\n".$buf;
                                            fwrite($tmp,$buf);
                                            break;
                                        case 'map.php': // URL Mapping
                                            $tmp=fopen($dest_path.$fb[1],'a');
                                            $buf=str_replace('<?php','',$buf);
                                            $buf=str_replace('<?','',$buf);
                                            $buf=str_replace('?>','',$buf);
                                            $buf="\n\n".'// PLUG-IN: '.substr($plugin,0,-4)."\n".$buf;
                                            fwrite($tmp,$buf);
                                            break;
                                    }
                                }else{
                                    $tmp=fopen($dest_path.$fb[1],'w');
                                    fwrite($tmp,$buf);
                                    fclose($tmp);   
                                }
                                zip_entry_close($zip_entry);
                                $installed++;
                            }else{
                                // Si no se pudo crear el archivo...
                                echo "<br />>> <span style='color: red'>No se pudo crear el archivo $name</span>";
                            }
                        }
                        zip_close($zip);
                    }else{
                        echo "<br />>> <span style='color: red'>Error al intentar instalar el componente $plugin</span>";
                    }
                    echo ($installed>0) ? "<br />>> <i>Completado!</i>" : "<br />>> <span style='color: red'>No se instaló ningún archivo del paquete!</span>";                   
                }
            }
        }

        // Create the tables
        include("../engine/lib/helpers/sql_parse.php");
        include_once("../engine/conf/config.php");
        mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']) or die('Couldn\'t connecto to MySQL with the specified data');
        mysql_select_db($config['db_name']);
        $chk = mysql_query("SHOW TABLES LIKE 'user_role'");
        if(mysql_num_rows($chk) <= 0){

            $res = execute_sql_file("schema.sql",$config['db_host'],$config['db_user'],$config['db_pass'],$config['db_name']);
            if($res === true){
                echo '<div>The database schema has been created.</div>';
            }else{
                echo '<div style="color: red">The following error was thrown while attempting to create the database schema:<br>
                '.$res.'
                </div>';
            }
        }else{
            echo '<div>The database schema has already been created.</div>';
        }
?>
<br /><br />
<div><b>Installation complete.</b></div>
<div>Your application root: <a href="<?php echo str_replace('install/?op=connect','',$_SERVER['HTTP_REFERER'])?>">
<?php echo str_replace('install/?op=connect','',$_SERVER['HTTP_REFERER'])?>
</a></div>
<?php
	break;
}
?>
</div>
<div style="text-align: center; color: #999999; border-top: solid 1px #cccccc; padding-top: 15px; margin-top: 10px">Falcode &copy; 2.2.0  | </div>

</div>
</body>
</html>