<?php
class ModuleController extends Controller{
    public $cache="";
    
    public function __construct(){
        $this->setDefaultAction('view');
    }

    public function captcha(){
        $this->load->extension('cool_captcha');
        $captcha = new SimpleCaptcha();
        $captcha->resourcesPath = PATH_EXTENSIONS."cool_captcha/resources";
        $captcha->wordsFile = 'words/en.php';
        $captcha->blur = false;
        $captcha->minWordLength = 3;
        $captcha->CreateImage();
        die();
    }
    
    public function view(){
        $this->blank(true);
        $this->response->setHeader("Content-Type: image/jpeg");
        include("dsp_view.php");
    }
    
    private function getFromCache(){
        if($this->config->enable_content_cache!=true || !file_exists(PATH_CACHE.md5("image?src=$_REQUEST[src]&width=$_REQUEST[width]&height=$_REQUEST[height]&force=$_REQUEST[force]&folder=$_REQUEST[folder]&zoom=$_REQUEST[zoom]")))
            return false; 
        $creation_time=filectime(PATH_CACHE.md5("image?src=$_REQUEST[src]&width=$_REQUEST[width]&height=$_REQUEST[height]&force=$_REQUEST[force]&folder=$_REQUEST[folder]&zoom=$_REQUEST[zoom]"));
        if(time()-$creation_time >= $this->config->cache_lifetime){
            unlink(PATH_CACHE.md5("image?src=$_REQUEST[src]&width=$_REQUEST[width]&height=$_REQUEST[height]&force=$_REQUEST[force]&folder=$_REQUEST[folder]&zoom=$_REQUEST[zoom]"));
            return false;
        }
        $this->cache=file_get_contents(PATH_CACHE.md5("image?src=$_REQUEST[src]&width=$_REQUEST[width]&height=$_REQUEST[height]&force=$_REQUEST[force]&folder=$_REQUEST[folder]&zoom=$_REQUEST[zoom]"));
        return true;
    }
    
    public function delete(){
        $this->blank(true);
        include("act_del.php");
    }
}
