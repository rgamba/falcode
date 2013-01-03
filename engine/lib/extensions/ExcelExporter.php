<?php
/* +-------------------------------+
 * |    MySQL - Excell Converter   |
 * +-------------------------------+
 * Programador: Ricardo Gamba
 * 
 * La clase debe ser ejecutada una vez iniciada
 * y habiendo proporcionado las variables necesarias:
 * var - sql: consulta sql no ejecutada
 * y no habiendo enviado headers ya que de lo contrario
 * no se podrá ejecutar la descarga.
 */
class ExcelExporter{
    // VARIABLES
    // Sentencia SQL NO EJECUTADA
    var $sql;
    // Nombre del archivo exportado
    var $nombre;
    // Header del archivo
    var $titulo;
    // Si esta true, mostraremos los errores
    // de ejecucion
    var $debug;
    // Errores
    var $error;
    // Alternar color de filas?
    var $alternar;
    // Array de columnas a excluir
    var $excluir;
    // Inicializamos el objeto 
    function __construct(){
        $this->sql="";
        $this->nombre="Libro1";
        $this->debug=false;
        $this->error="";
        $this->titulo="Libro 1";
        $this->alternar=true;
        $this->excluir='';
    }
    function exportar(){
        if(headers_sent()){
            // Error, los headers ya se enviaros
            $this->error="Headers enviados";
        }
        if($this->sql==''){
            // Error, no tenemos sentencia SQL
            $this->error="Sin sentencia SQL";
        }
        // Si tenemos error, salimos
        if($this->error!=""){
            if($this->debug==true){
                echo "Excel Exporter Error: <b>".$this->error."</b>";
            }
            return false;
        }else{
            // Enviamos headers
            header('Content-type: application/vnd.ms-excel; charset=UTF-8');
            header("Content-Disposition: attachment; filename=".$this->nombre.".xls");
            header("Pragma: no-cache");
            header("Expires: 0");
            $query=mysql_query($this->sql);
            if(@mysql_num_rows($query)>0){
                $cols=array();
                $rows=array();
                // Cabeceras de columna
                for($i=0;$i<=(mysql_num_fields($query)-1);$i++){
                    $colName=mysql_field_name($query,$i);
                    if($this->excluir!='' && is_array($this->excluir)){
                        foreach($this->excluir as $j => $val){
                            if($val==$colName){
                                $colName='';
                            }
                        }
                    }
                    if($colName!=''){
                        $cols[]=mysql_field_name($query,$i);
                    }
                }
                $file='<table border=0>
                            <tr>
                                <th colspan='.mysql_num_fields($query).' align=left><h2>'.$this->titulo.'</h2></th>
                            </tr>
                            <tr>
                                <td colspan='.mysql_num_fields($query).' style="color: #666666" align=left><small>
                                Registros: <b>'.mysql_num_rows($query).'</b> | Fecha: <b>'.date("d/m/Y").'</b></small>
                                </td>
                            </tr><br />';
                // Imprimimos cabeceras
                foreach($cols as $i => $colName){
                    $file.='<th style="border-bottom:solid 1px #cccccc; background-color: #e6e6e6">'.$colName.'</th>';
                }
                $file.='</tr>';
                $alt=' ';
                // Imprimimos contenido
                while($rows=mysql_fetch_array($query)){
                    if($this->alternar==true){
                        $alt=$alt==''?';background-color: #f1f1f1':'';
                    }else{
                        $alt='';
                    }
                    $file.='<tr>';
                    for($j=0;$j<=sizeof($cols)-1;$j++){
                        if($cols[$j]=="id_usuario"){
                            $field=Db::_getVal('usuario','id_usuario',$rows[$cols[$j]],'usuario')." (".$rows[$cols[$j]].")";
                        }else if($cols[$j]=="id_movimiento_tipo"){
                            $field=strtoupper(Db::_getVal('movimiento_tipo','id_movimiento_tipo',$rows[$cols[$j]],'nombre'));
                        }else{
                            $field=$rows[''.$cols[$j].''];
                        }
                        if(empty($field))
                            $field='';
                        $file.='<td align=left style="border-right: solid 1px #e6e6e6 '.$alt.'">'.$field.'</td>';
                    }
                    $file.='</tr>';
                }
                $file.='</table>';
                echo $file;
            }else{
                // No tenemos registros en la consulta
                if($this->debug==true){
                    echo 'Excel Exporter: La consulta no arrojo resultados';
                    
                }
                return false;
            }
        }
    }
}    