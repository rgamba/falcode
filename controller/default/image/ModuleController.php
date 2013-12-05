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
        //if($this->config->enable_content_cache!=true)
        //    return false;
        $r = $_GET;
        ksort($r);
        $cache_name=sha1("image?".http_build_query($r));
        if(!file_exists(PATH_CACHE.$cache_name))
            return false;

        $creation_time=filectime(PATH_CACHE.$cache_name);
        if(time()-$creation_time >= $this->config->cache_lifetime){
            unlink(PATH_CACHE.$cache_name);
            return false;
        }
        $this->cache=PATH_CACHE.$cache_name;
        return true;
    }
    
    public function delete(){
        $this->blank(true);
        include("act_del.php");
    }

    public function qr(){
        $this->blank(true);
        $this->response->setHeader("Content-Type: image/jpeg");
        include("act_qr.php");
    }
}
