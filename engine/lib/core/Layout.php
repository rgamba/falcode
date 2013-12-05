<?php
/**~engine/lib/Layout.php
 *
 * Layout (Singleton)
 * ---
 *
 * @package      FALCODE
 * @version      3.0
 * @author       FALCODE
 * @uses         Template.php
 * @uses         TemplateEngine.php
 */
class Layout{
    // Variables definition
    private $_template='';
    private $content='';
    private $title='';
    public $output=''; // Output
    private static $st_instance; // Unica instancia del singleton

    /**
     * Constructor
     */
    private function __construct(){
        $this->title=Tpl::get('PAGE_TITLE');
    }

    /**
     * Unico metodo para crear LA instancia a esta clase
     * es un SINGLETON!
     *
     */
    public static function create(){
        if(!self::$st_instance){
            self::$st_instance=new Layout();
        }
        return self::$st_instance;
    }

    /**
     * Regresa el output del template
     */
    public function output($print=false){
        $this->fileCheck();
        $this->output=$this->content();
        if($print)
            return $this->output;
        return;
    }

    /**
     * Check include files
     */
    private function fileCheck(){
        $TPL_TEMPLATE=Tpl::get('MAIN_TEMPLATE');
        $BLANK=false;
        $INCLUDE=NULL;

        try{
            if(DSP_FILE==CTRL_ERR_FILE)
                throw new ControllerException("Module not found",ControllerException::MODULE_NOT_FOUND);
            // Access control list
            if(Sys::get('config')->login_required===true){
                if(!Sys::get('acl')->guard(true))
                    throw new ControllerException("Access denied",ControllerException::ACCESS_DENIED);
            }
            // Incluimos el archivo despachador
            include_once(DSP_FILE); // Dispatcher
            // Obtenemos el archivo de include si es que hay
            if(!empty($INCLUDE) && empty(Controller::$include))
                Controller::$include=$INCLUDE;
            // Control por la clase ModuleController
            if(class_exists('ModuleController')){
                $methods=get_class_methods('ModuleController');
                if(!in_array('__construct',$methods))
                    die("ModuleController must have a constructor method");
                foreach($methods as $k => $v)
                    if($v == "__construct")
                        unset($methods[$k]);

                Sys::set('module_controller',new ModuleController());
                $runMethod=NULL;
                if(in_array((DSP_CONTROL.'Action'),$methods)){
                    // public function methodAction() format
                    $runMethod=DSP_CONTROL.'Action';
                }elseif(in_array(DSP_CONTROL,$methods)){
                    // public function method() format, NOTE: method MUST be public
                    $runMethod=DSP_CONTROL;
                }
                elseif(DSP_CONTROL==""){
                    // Buscamos metodo default
                    $defaultAction=!empty(Sys::get('module_controller')->defaultAction) ? Sys::get('module_controller')->defaultAction : 'main';

                    if(in_array($defaultAction,$methods))
                        $runMethod=$defaultAction;
                    else
                        throw new ControllerException("Action not found",ControllerException::ACTION_NOT_FOUND);
                }else{
                    throw new ControllerException("Action not found",ControllerException::ACTION_NOT_FOUND);
                }
                if(Sys::get('module_controller')->breadcrumb()!=NULL){
                    Tpl::set('BREADCRUMB',Sys::get('module_controller')->breadcrumb());
                }
                // Obtenemos el output
                if($runMethod){
                    ob_start();
                    call_user_func_array(array(
                        Sys::get('module_controller'),
                        $runMethod
                    ),array_merge(
                        array(Sys::get('module_controller')->request->id),
                        Router::$path
                    ));
                    //Sys::get('module_controller')->{$runMethod}();
                    if(Sys::get('module_controller')->load->getDefaultView() && !Sys::get('module_controller')->rendered)
                        Sys::get('module_controller')->render();
                    $_cont=ob_get_clean();
                }
            }else{
                die("Layout::checkFile() - Class ModuleController not found on ModuleController.php file (".DSP_FILE.")");
            }


            // Requerimos template blank?
            if(!isset(Router::$Control['blank']))
                Router::$Control['blank'] = false;
            if(!isset(Router::$Control['ajax']))
                Router::$Control['ajax'] = false;
            if(Router::$Control['blank']==true || Router::$Control['ajax']==true)
                $BLANK=true;

            // Si se requirio otro template
            if(Tpl::get('ACTIVE')!=Tpl::get('DEF')){
                Tpl::set(PATH,HTTP_CONTENT_TEMPLATES.Tpl::get('ACTIVE').'/');
                Core::autoIncludeFiles(); // Reload de auto includes
            }
            // Definimos constante de ruta global del template
            // activo
            define('TPL_DEF_PATH',Tpl::get('PATH'));

        }catch(ControllerException $e){

            define('TPL_DEF_PATH',Tpl::get('PATH'));
            Tpl::set('ERROR',true);
            if(file_exists(PATH_CONTROLLER_MODULES.DSP_MODULE.'/ErrorController.php') && $e->getCode()!=ControllerException::MODULE_NOT_FOUND){
                require_once(PATH_CONTROLLER_MODULES.DSP_MODULE.'/ErrorController.php');
            }elseif(file_exists(PATH_CONTROLLER_MODULES.'ErrorController.php')){
                require_once(PATH_CONTROLLER_MODULES.'ErrorController.php');
            }else{
                die("File ErrorController not found");
            }
            if(!class_exists('ErrorController'))
                die("The class ErrorController was not found on ".PATH_CONTROLLER_MODULES.DSP_MODULE."ErrorController.php");
            $methods=get_class_methods('ErrorController');
            $method_to_call="";
            switch($e->getCode()){
                default:
                case ControllerException::MODULE_NOT_FOUND:
                    $method_to_call="ModuleNotFound";
                    break;
                case ControllerException::ACTION_NOT_FOUND:
                    $method_to_call="ActionNotFound";
                    break;
                case ControllerException::ACCESS_DENIED:
                    $method_to_call="AccessDenied";
                    break;
                case ControllerException::UNDER_MAINTENANCE:
                    $method_to_call="UnderMaintenance";
                    break;
                case ControllerException::CUSTOM_ERROR:
                    $method_to_call="CustomError";
                    break;
                case ControllerException::UNKNOWN:
                    $method_to_call="UnknownError";
                    break;
                case ControllerException::VALIDATION:
                    $method_to_call="Validation";
                    break;
            }
            if(in_array($method_to_call,$methods)){
                Sys::set('module_controller',new ErrorController());
                ob_start();
                if($method_to_call=="ModuleNotFound")
                    $param=DSP_MODULE;
                elseif($method_to_call=="ActionNotFound")
                    $param=DSP_CONTROL;
                Sys::get('module_controller')->{$method_to_call}($e->getMessage(),@$param);
                if(Sys::get('module_controller')->load->getDefaultView() && !Sys::get('module_controller')->rendered)
                    Sys::get('module_controller')->render();
                $_cont=ob_get_clean();
            }else
                die("ErrorController does not implement $method_to_call method");
        }
        define('TPL_HTML',TPL_DEF_PATH.'html/');
        if(empty($_cont)) // Si cont no ha sido llenado por ModuleController...
            $_cont=get_include(Controller::$include);

        // Die msg
        if(Sys::get('DIE_MSG')!=""){
            $this->content=Sys::get('DIE_MSG');
        }else{
            $this->content=$_cont;
        }

        // Blank template
        $TPL_TEMPLATE=Tpl::get('MAIN_TEMPLATE');
        $this->_template=$TPL_TEMPLATE;

        // Page title
        if(Sys::get('config')->tpl_append_title){
            if($this->title!=Tpl::get('PAGE_TITLE'))
                $this->title=Tpl::get('PAGE_TITLE')." - ".APP_NAME;
        }else{
            $this->title=Tpl::get('PAGE_TITLE');
        }
        define('BLANK_OUTPUT',(Tpl::get('MAIN_TEMPLATE')==Tpl::get('BLANK') ? true : false));
        define('SYS_CHARSET',Sys::get('CHARSET'));
        define('SYS_CONTENT_TYPE',Sys::get('CONTENT_TYPE'));
    }

    /**
     * Customize and unify templates
     * for the main page
     */
    private function content(){
        $this->template=new TemplateEngine(TPL_DEF_PATH.$this->_template);
        $this->template->root=Tpl::htmlPath();
        $this->template->controller_root=PATH_CONTROLLER_MODULES.'/';
        // Main content
        $this->template->Container=$this->content;
        $this->template->Title=$this->title;
        // Page loading time
        Sys::set('page_load_time',microtime(true)-Sys::get('page_load_init'));
        // Get content
        $content=$this->template->render();
        return ltrim($content);
    }
}