<?php
/**~engine/lib/Uploader.php
* 
* File Uploader Handler
* ---
* 
* @package     FALCODE  
* @author      FALCODE
* @copyright   $Copyright$
* @version     $Version$
*/
class Uploader{
    /**
    * Arreglo de extensiones y tipo de archivos permitidos
    * 
    * @var mixed
    */
    private $allowed=array();
    
    /**
    * Archivo que se subio
    * 
    * @var mixed
    */
    private $file=NULL;
    
    /**
    * Destino donde se va a guardar el archivo
    * 
    * @var mixed
    */
    public $location=NULL;
    
    /**
    * Tamano maximo del archivo en KB
    * 
    * @var mixed
    */
    public $maxFileSize=2000;
    
    /**
    * Nombre del nuevo archivo
    * 
    * @var mixed
    */
    public $newName=NULL;
    
    /**
    * Establecer a true para reemplazar el archivo
    * en caso de existir, de lo contrario se agregara un
    * sufijo (copia n)
    * 
    * @var mixed
    */
    public $replace=false;
    
    /**
    * Obtendra el valor del nuevo archivo renombrado
    * 
    * @var mixed
    */
    public $newFile=NULL;
    
    /**
    * Error codes
    * 
    * @var mixed
    */
    public $err_codes = array(
        'too_big' => 1,
        'invalid_extension' => 2,
        'invalid_file' => 3,
        'invalid_creation_dir' => 4
    );
    
    /**
    * Constructora
    * 
    * @param mixed $arr
    * @return Uploader
    */
    public function __construct(array $arr=array()){
        if(!empty($arr))
            $this->allowed=$arr;
    }
    
    /**
    * Agrega un tipo de archivo a la lista de permitidos
    * 
    * @param mixed $type
    */
    public function allow($type=NULL){
        $common_mime=array(
            'word'      => 'doc',
            'excel'     => 'xls',
            'wordx'     => 'docx',
            'excelx'    => 'xlsx'
        );
        foreach($common as $alias => $_type){
            if($type==$alias){
                $type=$_type;
                break;
            }
        }
        if(!is_null($type))
            $this->allowed[]=$type;
    }
    
    /**
    * Verifica que el archivo sea valido
    * 
    */
    private function checkFile(){
        if(!is_array($this->file)){
            $this->exception("Archivo invalido",$this->err_codes['invalid_file']);
            return false;   
        }
        if(isset($this->file["error"]) && $this->file["error"] != 0){
            $this->exception($this->file["error"],$this->err_codes['invalid_file']);
            return false; 
        }
        if(!isset($this->file["tmp_name"]) || !@is_uploaded_file($this->file["tmp_name"])){
            $this->exception("No se encontro el archivo temporal",$this->err_codes['invalid_file']);
            return false; 
        }
        if(empty($this->file["name"])){
            $this->exception("El archivo no tiene nombre",$this->err_codes['invalid_file']);
            return false; 
        }
        $allowed=false;
        $fdet = pathinfo($this->file['name']);
        if(!in_array(strtolower($fdet['extension']),$this->allowed)){
            $this->exception("El tipo de archivo '".strtoupper($fdet['extension'])."' no esta permitido",$this->err_codes['invalid_extension']);
            return false;
        }
        if($file['size']>($this->maxFileSize*128)){
            $this->exception("El archivo no puede ser superior a ".$this->maxFileSize." KB",$this->err_codes['too_big']);
            return false;
        }
        return true;
        
    }
    
    /**
    * Funcion principal para subir el archivo
    * 
    * @param mixed $file
    * @param mixed $newName
    * @param mixed $location
    */
    public function upload(array $file,$newName=NULL,$location=NULL){
        if(!is_null($newName))
            $this->newName=$newName;
        if(!is_null($location))
            $this->location=$location;
        $this->file=$file;
        if(empty($this->newName))
            $this->newName=$this->file['name'];
        // Secure file name
        $this->newName = preg_replace('/[^.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-]|\.+$/i', "", basename($this->newName));
        if(!$this->checkFile())
            return false;
        if(!is_dir($this->location))
            mkdir($this->location);
        if(!is_dir($this->location)){
            $this->exception("La ruta de creacion (".$this->location.") no existe",$this->err_codes['invalid_creation_dir']);
            return false;
        }
        
        if(file_exists($this->location."/".$this->newName) && !$this->replace){
            $i=1;
            $name=pathinfo($this->newName);
            $ext=$name['extension'];
            $name=$name['filename'];
            while(true){
                $tmp_name=$name."-$i.$ext";
                if(!file_exists($this->location."/".$tmp_name)){
                    $this->newName=$tmp_name;
                    break;
                }
                $i++;
            }
        }
        $ext=pathinfo($this->file['name']);
        $ext=$ext['extension'];
        $move=move_uploaded_file($this->file['tmp_name'],$this->location."/".$this->newName.".$ext");
        $this->newFile=$this->newName.".$ext";
        return $move;
    }
    
    /**
    * Arroja una excepcion
    * 
    * @param mixed $msg
    */
    private function exception($msg,$code){
        throw new Exception($msg,$code);
    }
}