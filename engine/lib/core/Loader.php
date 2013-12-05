<?php
class Loader{
    private $parent;
    private $loaded=array();
    private static $instance=null;

    public static function getInstance(){
        if(self::$instance==null){
            self::$instance=new Loader();
        }
        return self::$instance;
    }

    private function __construct(){
        $this->loaded=array(
            'models' => array(),
            'helpers' => array(),
            'extensions' => array(),
            'templates' => array()
        );
    }

    public function __get($module){
        return isset($this->loaded['models'][$module])
            ? $this->loaded['models'][$module]
            : !isset($this->loaded['views'][$module])
                ? null
                : $this->loaded['views'][$module];
    }

    public function isLoaded($type,$name){
        return isset($this->loaded[$type][$name]);
    }

    public function helper($file){
        $ext=explode('.',$file);
        if($ext[count($ext)-1]!="php"){
            $file.='.php';
        }
        if(!file_exists(PATH_ENGINE_LIB.'helpers/'.$file))
            die("Loader::helper(): File not found '".PATH_ENGINE_LIB.'helpers/'.$file."'");
        require_once(PATH_ENGINE_LIB.'helpers/'.$file);
        $this->loaded['helpers'][$file]=true;
    }

    public function view($file=null,$vars=array(),$alias="template"){

        $ext=explode('.',$file);
        if(empty($ext[1]) && !empty($ext[0]))
            $file.=".".Sys::get('config')->tpl_default_extension;
        if(is_string($vars)){
            if(file_exists(PATH_CONTROLLER_MODULES.$vars)){
                include_once(PATH_CONTROLLER_MODULES.$vars);
                $vars = empty($data) ? array() : $data;
            }
        }

        $root=Tpl::moduleHtmlPath();
        $tpl=new TemplateEngine();
        $tpl->root=Tpl::htmlPath();
        $tpl->controller_root=PATH_CONTROLLER_MODULES.'/';
        if(empty($file)){
            if(DSP_CONTROL==""){
                if(Controller::$default_action!=""){
                    $file=Controller::$default_action.".html";
                }else{
                    $file=Controller::ACT_DEFAULT.".html";
                }
            }else{
                $file=DSP_CONTROL.".html";
            }
        }

        $tpl->load($root.$file);
        if($vars)
            $tpl->setContext($vars);
        $this->loaded['views'][$alias]=&$tpl;
        return $this->loaded['views'][$alias];

    }

    public function getDefaultView(){
        @reset($this->loaded['views']);
        return @current($this->loaded['views']);
    }

    public function extension($file){
        if(is_dir(PATH_EXTENSIONS.$file)){
            if(!file_exists(PATH_EXTENSIONS.$file.'/_autoload.php')){
                die("Loader::extension(): The extension $file does not contain a _autoload.php file in it's root directory");
            }
            require_once(PATH_EXTENSIONS.$file.'/_autoload.php');
        }elseif(file_exists(PATH_EXTENSIONS.$file)){
            require_once(PATH_EXTENSIONS.$file);
        }else{
            die("Loader::extension(): Extension $file not found");
        }
        $this->loaded['extensions'][$file]=true;
    }

    public function js($file){
        $ext=explode('.',$file);
        if($ext[count($ext)-1]!="js"){
            $file.='.js';
        }
        Sys::$JS_Files[]=HTTP_CONTENT_TEMPLATES.Tpl::get('ACTIVE')."/js/$file";
    }

    public function css($file){
        $ext=explode('.',$file);
        if($ext[count($ext)-1]!="css"){
            $file.='.css';
        }
        Sys::$CSS_Files[]=HTTP_CONTENT_TEMPLATES.Tpl::get('ACTIVE')."/css/$file";
    }

    public function model($name){
        $name=str_replace(' ','',ucwords(str_replace('_',' ',$name)));
        $this->loaded['models'][$name]=new $name();
        return $this->loaded['models'][$name];
    }

    /**
     * Change the default layout file
     * Located in content/templates/<active template>/
     *
     * @param mixed $l
     */
    public function layout($file){
        Tpl::set('MAIN_TEMPLATE',$file);
    }

    public function template($tpl){
        $load=false;
        if(Tpl::get('ACTIVE')!=$tpl)
            $load=true;
        Tpl::set('ACTIVE',$tpl);
        if($load){
            Tpl::set('PATH',PATH_CONTENT_TEMPLATES.Tpl::get('ACTIVE').'/');
            Core::autoIncludeFiles(); // Reload de auto includes
        }
    }
}