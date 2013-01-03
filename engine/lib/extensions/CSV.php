<?php
/**
* CSV
* ---
* 
* @package  FALCODE
* @author Ricardo Gamba <rgamba@gmail.com>
*/
class CSV{
    private $file=NULL;
    private $parsed=array();
    private $file_h=NULL;
    public $headers=array();
    private $count=0;
    public $row=array();
    /**
    * En caso que esta variable sea true, el arreglo $row
    * sera asociativo con base en el primer registro o linea encontrada
    * en el archivo, que sera tomada como linea de headers
    * 
    * @var boolean
    */
    public $enable_headers=true;
    /**
    * Encoding del archivo CSV que se va a procesar
    * 
    * @var "ISO-8859-1" o "UTF-8"
    */
    public $enctype="ISO-8859-1";
    
    /**
    * Constructora
    * 
    * @param mixed $file Archivo cvs que se va a procesar
    * @return bool
    */
    public function __construct($file=NULL){
        /*if(!file_exists($file)){
            $this->Exception("El archivo CSV <$file> no existe o no puede ser abierto");
            return false;
        }*/
        $this->file=$file;
        return true;
    } 
    
    /**
    * Abre el archivo CSV
    * 
    */
    public function open(){
        if(empty($this->file)){
            $this->Exception("Seleccione un archivo CSV valido");
            return false;
        }
        $this->count=0;
        $this->file_h=fopen($this->file,"r");
        if($this->file_h===false){
            $this->Exception("Error al abrir el archivo ".$this->file);
            return false;
        }
        
        if($this->enable_headers)
            $this->getHeaders();
    }
    
    /**
    * Procesa todo el archivo y devuelve un arreglo
    * asociativo con todos los contenidos
    * 
    */
    public function parse(){
        $this->open();
        $this->parsed=array();
        while($this->next()){
            $this->parsed[]=$this->row;   
        }
        return $this->parsed;
    }
    
    /**
    * Procesa un registro y avanza para procesar el archivo
    * linea por linea
    * 
    */
    public function next(){
        if($this->file_h==NULL)
            $this->open();
        $tmp_row=fgetcsv($this->file_h);
        if($this->enable_headers && !empty($this->headers) && $tmp_row!==false){
            $t_row=$tmp_row;
            $tmp_row=array();
            foreach($t_row as $i => $r){
                $tmp_row[$this->headers[$i]]=($this->enctype=="UTF-8" ? utf8_decode($r) : $r);
            }
        }
        $this->row=$tmp_row;
        
        $this->count++;
        return $this->row;  
    }
    
    /**
    * Convierte un arreglo asociativo en formato CSV
    * 
    * @param mixed $array
    */
    public function convert($array,$filename,$ignore_headers=false,$output=true){
        if(!is_array($array))
            return false;

        if($output){    
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
        }
        $fp = $output ? fopen('php://output', 'w') : fopen('php://temp','r+');
        if(!$ignore_headers)
            fputcsv($fp,array_keys($array[0]));
        foreach($array as $row){
            fputcsv($fp,$row);
        } 
        if(!$output){
            rewind($fp);
            while(!feof($fp))
                $csv.=fgets($fp);
        }
        fclose($fp);        
        if(!$output)
            return $csv;         
    }
    
    /**
    * Obtiene los headers en caso de que $enable_headers sea true
    * 
    */
    private function getHeaders(){
        if(!empty($this->headers))
            return true;
        $head=fgetcsv($this->file_h);    
        foreach($head as $i => $key){
            $this->headers[]=$key;
        }  
        return $this->headers; 
    }
    
    /**
    * Excepciones
    * 
    * @param mixed $msg
    */
    public function Exception($msg){
        throw new Exception($msg);
    }
}
