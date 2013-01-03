<?php
/**~engine/lib/Image.php
* 
* Image
* ---
* Image upload and resize
* 
* @package     FALCODE  
* @author      FALCODE
* @copyright   $Copyright$
* @version     $Version$
*/
class Image{
	// Private scope variables
	private $_img=""; // Image holder
	private $_allowed=array('image/gif','image/jpeg','image/png'); // Allowed image extensions
	private $_type=''; // Temporal image type
	private $_res;
	
	// Public scope variables
	public $debug=true; // Error handling
	public $path='./'; // Images folder path (optional)
	public $format='jpg'; // Output image format [ jpg | gif | png ]
	public $max_size="200"; // Max image size in KB
	public $mode="0666"; // File access permission
	public $quality=80; // Image quality
	public $exceptions=false; // Throw exceptions to handle errors?
	
	/**
	 * Constructor
	 **/
	public function __construct($img='',$path=''){
		$this->path=$path;
		if(!empty($img)){
			if(!$this->check_file($img)){
				$this->exception("constructor()","Invalid image format");
			}else{
				$this->_img=$img;
			}
		}
	}
	
	/**
	 * File verify
	 * @params - string(image file)
	 * @return Boolean
	 **/
	private function check_file($img=''){
		if(empty($img)) $img=$this->_img;
		if(!empty($this->path))
			if(substr($this->path,-1)!="/") $this->path.="/";
			
		if(empty($img)){
			$this->exception("[private]check_file()","Unable to retrieve image handle");
			return false;
		}
		if(strpos($img,"/")!==false)
			$file=$img;
		else
	 		$file=$this->path.$img;
			
		if(!file_exists($file))
			return false;
	
		$img_det=getimagesize($file);
		$type=image_type_to_mime_type($img_det[2]);
		// Verify extension
		$valid=false;
		foreach($this->_allowed as $i => $xt){
			if(strtolower($type)==$xt){
				$valid=true;
				$this->_type=$type;
				break;
			}
		}
		return $valid;
	}
	
	/**
	 * Image resize
	 * @params	- img: image handle
	 *			- w: image final width
	 *			- h: image final height
	 *			- force_size: true to force the width
	 *				and height established widthout
	 *				preserving aspect ratio
	 *			- return_handle: if set to false will
	 * 				return output image
	 * @return image handle	or output image	
	 **/
	public function resize($img='',$img_size_width=0,$img_size_height=0,$force_size=false,$output=false){
		if(!$this->check_file($img)){
			$this->exception("resize()","Invalid image format");
			return false;
		}
		if(!empty($img))
			$this->_img=$img;
		if(!empty($img) && strpos($img,"/")===false)
			$img=$this->path.$this->_img;
		$size=getimagesize($img); // width / height
		$aspect_ratio=$size[0]/$size[1];
		
		if(!$force_size){
			// Do not force the sizes
			if ($size[0]<=$img_size_width && $size[1]<=$img_size_height) { // If the original image is smaller
				$h = $size[1]; 
				$w = $size[0];
			}else{
				$w = $img_size_width; 
				$h = abs($w/$aspect_ratio); 
			}
			$x_offset=0;
			$y_offset=0;
		}else{
			$h=$img_size_height; $w=$img_size_width;
			// Force image size (cut)
			if($size[0]>$size[1]){ // Wide image
				$src_w=$size[1];
				$src_h=$size[1];
				// Image offsets
				$x_offset=($size[0]-$size[1])/2;
				$y_offset=0;
			}else{ // Tall image
				$src_w=$size[0];
				$src_h=$size[0];
				// Image offsets
				$x_offset=0;
				$y_offset=($size[1]-$size[0])/2;
			}
		}
		$dest_img=imagecreatetruecolor($w,$h);
		if(!$dest_img){
			$this->exception("resize()","Unable to create temporal image");
			return false;
		}
		
		// Image type
		switch($this->_type){
			case 'image/jpeg':
				$src_img=imagecreatefromjpeg($img);
				break;
			case 'image/gif':
				$src_img=imagecreatefromgif($img);
				break;
			case 'image/png':
				$src_img=imagecreatefrompng($img);
				break;
		}
		if(!$src_img){
			$this->exception("resize()","Unable to create image handler");
			return false;
		}
		
		if(!isset($src_w) && !isset($src_h)){
			$src_w=imagesx($src_img);
			$src_h=imagesy($src_img);
		}
		
		// Copy and resize image
		if(function_exists('imagecopyresampled'))
			$cres=imagecopyresampled($dest_img,$src_img,0,0,$x_offset,$y_offset,$w,$h,$src_w,$src_h);
		else
			$cres=imagecopyresized($dest_img,$src_img,0,0,$x_offset,$y_offset,$w,$h,$src_w,$src_h);
		
		if(!$cres){
			$this->exception("resize()","Unable to resize image");
			return false;
		}
		
		// Create image
		if($output){
			switch(strtolower($this->format)){
				default:
				case 'jpg':
				case 'jpeg':
					imagejpeg($dest_img,NULL,$this->quality);
					break;
				case 'gif':
					imagegif($dest_img);
					break;
				case 'png':
					imagepng($dest_img,NULL,9);
					break;
			}
		}
		$this->_res=$dest_img;
		//imagedestroy($dest_img);
		return $dest_img;
	}
    
    /*
    * Otra función para resizeo de imágenes
    * Respeta imágenes sin fondo
    */  
    public function resize_then_crop( $filein='', $fileout=false, $imagethumbsize_w=0, $imagethumbsize_h=0, $force_size=false, $red="255", $green="255", $blue="255",$small_crop=false)
    {        
        //Ruta de la imagen
        if(!empty($filein))
            $this->_img=$filein;
        if(!empty($filein) && strpos($filein,"/")===false)
            $filein=$this->path.$this->_img; 
        
        if(empty($imagethumbsize_w) && empty($imagethumbsize_h)){
            list($imagethumbsize_w, $imagethumbsize_h) = getimagesize($filein);
        }elseif(empty($imagethumbsize_w)){ 
            list($original_width, $original_height) = getimagesize($filein);
            $imagethumbsize_w = ($original_width * $imagethumbsize_h)/$original_height;      
        }elseif(empty($imagethumbsize_h)){
            list($original_width, $original_height) = getimagesize($filein);
            $imagethumbsize_h = ($original_height * $imagethumbsize_w)/$original_width;      
        } 
        
        //Si el width es muy grande, ajustar (nuevo)
        if($imagethumbsize_w > 980){
            $imagethumbsize_h = 980*$imagethumbsize_h/$imagethumbsize_w; 
            $imagethumbsize_w = 980;
        }

       if(preg_match("/.jpg/i", "$filein"))
       {
           $format = 'image/jpeg';
       }
       if (preg_match("/.gif/i", "$filein"))
       {
           $format = 'image/gif';
       }
       if(preg_match("/.png/i", "$filein"))
       {
           $format = 'image/png';
       }
      
           switch($format)
           {
               case 'image/jpeg': 
               $image = imagecreatefromjpeg($filein);
               break;
               case 'image/gif';
               $image = imagecreatefromgif($filein);
               break;
               case 'image/png':
               $image = imagecreatefrompng($filein);
               break;
           } 

    $width = $imagethumbsize_w ;
    $height = $imagethumbsize_h ;
    list($width_orig, $height_orig) = getimagesize($filein);

    if ($width_orig < $height_orig) {
      $height = ($imagethumbsize_w / $width_orig) * $height_orig;
    } else {
        $width = ($imagethumbsize_h / $height_orig) * $width_orig;
    }

    if ($width < $imagethumbsize_w)
    //if the width is smaller than supplied thumbnail size
    {
    $width = $imagethumbsize_w;
    $height = ($imagethumbsize_w/ $width_orig) * $height_orig;;
    }

    if ($height < $imagethumbsize_h)
    //if the height is smaller than supplied thumbnail size
    {
    $height = $imagethumbsize_h;
    $width = ($imagethumbsize_h / $height_orig) * $width_orig;
    
    }
    
    //Ajustamos tamaños para que quepa completo en un cuadro de dimensiones específicas
    if(!empty($small_crop)){
        $small_dims = explode("x",$small_crop);
        $small_width = $small_dims[0];
        $small_height = $small_dims[1];
        $imagethumbsize_w = $small_width;
        $imagethumbsize_h = $small_height;
        
        
        if($width_orig > $height_orig){
            $width = $small_width;
            $height = floor($width*$height_orig/$width_orig);
            //Checar si el height es del tamaño adecuado ahora
            if($height>$imagethumbsize_h){  
                $width = floor($width*$imagethumbsize_h/$height); 
                $height = $imagethumbsize_h;
            }
        }else{
            $height = $small_height; //?? sino pongo +1 me da una tira negra a la derecha! 
            $width = floor($height*$width_orig/$height_orig);
            //Checar si el width es del tamaño adecuado ahora
        }        
    }
    

    //Creacion de la imagen proporcionada
    $thumb = imagecreatetruecolor($width+1 , $height); 
    $bgcolor = imagecolorallocate($thumb, $red, $green, $blue); 
    ImageFilledRectangle($thumb, 0, 0, $width, $height, $bgcolor);
    imagealphablending($thumb, true);
    

    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
    
   
    //Creacion de la imagen ya recortada
    $thumb2 = imagecreatetruecolor($imagethumbsize_w , $imagethumbsize_h);
    $bgcolor2 = imagecolorallocate($thumb2, $red, $green, $blue); 
    ImageFilledRectangle($thumb2, 0, 0, $imagethumbsize_w , $imagethumbsize_h , $bgcolor2);
    imagealphablending($thumb2, true);
    

    $w1 =($width/2) - ($imagethumbsize_w/2);
    $h1 = ($height/2) - ($imagethumbsize_h/2);
                                                                                                                                   
    imagecopyresampled($thumb2, $thumb, 0,0, $w1, $h1,$imagethumbsize_w , $imagethumbsize_h ,$imagethumbsize_w, $imagethumbsize_h);
    //ImageFill($thumb2, 1, 1, ImageColorAllocate($thumb2, $red, $green, $blue));

    //if ($fileout !="")imagejpeg($thumb2, $fileout); //write to file
    imagejpeg($thumb2,NULL,$this->quality); //output to browser
    
    $this->_res=$thumb2;
    //imagedestroy($dest_img);
    return $thumb2;
    
    }
    
    
	
	/**
	 * Upload image
	 * @params	- file: posted uploaded file global variable
	 *			- dest_path: destination path
	 *			- new_name: new name (WITHOUT extension) if not especified will use original name
	 *			- others: same as resize
	 * @return Boolean
	 **/
	public function upload($file='',$new_name='',$img_size=0,$force_size=false,$return_name=true,$index=NULL){
		if(is_array($file['name']) && empty($index))
			$index=0;
		if(!is_null($index)){
			$_f=$file;
			$file=array();
			$file['type']=$_f['type'][$index];
			$file['size']=$_f['size'][$index];
			$file['tmp_name']=$_f['tmp_name'][$index];
			$file['name']=$_f['name'][$index];
		}
		if(empty($file)){
			$this->exception("upload()","No uploaded file set");
			return false;
		}
        //Formato de la imagen
        $format = $this->format;
        switch($file['type'])
        {
            case 'image/jpg':
           case 'image/jpeg': 
           $format = "jpg";
           break;
           case 'image/gif';
           $format = "gif";
           break;
           case 'image/png':
           $format = "png";
           break;
        }
		
		if(!empty($this->path))
			if(substr($this->path,-1)!="/") $this->path.="/";
		
		$valid=false;
		foreach($this->_allowed as $i => $typ){
			if($file['type']==$typ)
				$valid=true;
		}
		//print_r($file);
		if(!$valid){
			$this->exception("upload()","Invalid image format");
			return false;
		}
		
		if($file['size']>($this->max_size*1000)){
			$this->exception("upload()","Max image size (".$this->max_size."KB) excedeed");
			return false;
		}
		
		$ext=explode(".",$file['name']);
		$ext=$ext[1];
		$dst_img=$this->path.$new_name.".".$format;
		
		move_uploaded_file($file['tmp_name'],$dst_img);
		chmod ($dst_img, octdec($this->mode));
		
		// Resize
		//$img=$this->resize($dst_img,$img_size,$force_size);
		$img=$this->resize_then_crop($dst_img,NULL); 
        
		// Save
		switch(strtolower($format)){
			default:
			case 'jpg':
			case 'jpeg':
				imagejpeg($img,$dst_img,$this->quality);
				break;
			case 'gif':
				imagegif($img,$dst_img);
				break;
			case 'png':
				imagepng($img,$dst_img);
				break;
		}
		imagedestroy($img);
		if($return_name)
			return $new_name.".".$format;
		return $dst_img;
	}
	
	/**
	 * Error and exception handling
	 **/
	private function exception($process='',$msg=''){
		if($this->exceptions){
			throw new Exception("$process : $msg");
		}else{
			if($this->debug)
				echo "$process : $msg";
		}
	}
	
	/**
	 * Clean cache
	 **/
	public function clean(){
		if(!empty($this->_res))
			@imagedestroy($this->_res);
	}
}