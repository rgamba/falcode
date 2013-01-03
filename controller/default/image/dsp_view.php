<?php
/**
* This module uses WideImage extension and requires it to be
* installed in the extensions dir
*/ 
$this->load->extension("wideimage");
$cache=$_REQUEST['cache']=="false" ? false : true;    
if($this->getFromCache()==true && $cache!=false){
    echo $this->cache;
}else{
    if(!empty(Router::$path)){
        $_REQUEST['folder'] = Router::$path[0];
        foreach(Router::$path as $i => $path){
            if($path == "force"){
                $_REQUEST['force'] = 1;
            }elseif($path == "inside" OR $path == "outside"){
                $_REQUEST['position'] = $path;
            }elseif(strpos($path,'x') !== false){
                $size = explode('x',$path);
                if($size[0] != "auto")
                    $_REQUEST['width'] = $size[0];
                if($size[1] != "auto")
                    $_REQUEST['height'] = $size[1];
                if($size[0] != "auto" && $size[1] != "auto")
                    $_REQUEST['force'] = 1;
            }elseif(strpos($path,'.') !== false){
                $_REQUEST['src'] = $path;
            }
        }
    }

    /**
    * Image file within content/_files/images/
    * 
    * @var mixed
    */
    $img=$_REQUEST['src'];
    /**
    * Image width
    * 
    * @var mixed
    */
    $width=!empty($_REQUEST['width']) ? $_REQUEST['width'] : '100%'; 
    /**
    * Image height
    * 
    * @var mixed
    */
    $height=!empty($_REQUEST['height']) ? $_REQUEST['height'] : '100%'; 
    /**
    * Force to the width and height?
    * 
    * @var mixed
    */
    $force=(empty($_REQUEST['force'])) ? false : true; 
    /**
    * Folder to locate the image relative to content/_files/images/
    * 
    * @var mixed
    */
    $subfolder=empty($_REQUEST['folder']) ? '' : $_REQUEST['folder']."/";
    /**
    * Folder to locate the image relative to content/_files/images/
    * 
    * @var mixed
    */
    $upperfolder=empty($_REQUEST['upperfolder']) ? 'images/' : $_REQUEST['upperfolder']."/";  
    /**
    * Position
    * 
    * @var [inside|outside]
    * Inside will try to fit the image in the box (if forced) and the overflow will be cropped out, if not forced will resize to the min of width or height
    * Outside will fit the entire image in the box (if forced) and if the aspect ratio do not match, white space will be added
    * If not forced, will take the largest of width or height and scale to that value
    */
    $position=empty($_REQUEST['position']) ? 'outside' : $_REQUEST['position'];
    try{
        $Image=WideImage::load(PATH_CONTENT_FILES.$upperfolder.$subfolder.$img);
        if($force){
            if($position == "outside"){
                $result = $Image->resize($width,$height,'outside')->crop("center","center",$width,$height);
            }else{
                $white = $Image->allocateColor(255,255,255);
                $result = $Image->resize($width,$height,'inside')->crop("center","center",$width,$height)->resizeCanvas($width,$height,"center","center",$white);
            }    
        }else{
            if(empty($_REQUEST['position'])){
                $result = $Image->resize($width,$height);
            }else{
                $result = $Image->resize($width,$height,$position); 
            }
        }
        ob_start();
        // Formato de la imagen para el output
        $formats=explode(".",$img);
        $format = $formats[count($formats)-1];
        $result->output($format);
        $output=ob_get_clean();
        
        if($this->config->enable_content_cache==true){
            $file_name=md5("image?src=$_REQUEST[src]&width=$_REQUEST[width]&height=$_REQUEST[height]&force=$_REQUEST[force]&folder=$_REQUEST[folder]&zoom=$_REQUEST[zoom]");
            $cache=fopen(PATH_CACHE.$file_name,'w');
            fwrite($cache,$output);
            fclose($cache);
        }
        
        echo $output;
    }catch (Exception $e){
        die($e->getMessage());
    }
}