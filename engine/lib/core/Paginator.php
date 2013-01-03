<?php
/**~engine/lib/Paginator.php
* 
* Paginator
* ---
* 
* @package     FALCODE
* @phpversion  >= 5
* @uses        TemplateEngine.php
*/
class Paginator{
    /**
     * Consulta sql inicial (sin limites)
     * @var string
     */
    public $sql='';
    
    /**
     * Consulta sql final creada automaticamente (con limites incluidos)
     * @var string
     */
    public $navSql='';
    
    /**
     * Nombre de la variable GET para el numero de pagina
     */
    public $navPgVar='pg';
    
    /**
     * Numero de paginas antes/despues de la pagina actual
     * @var integer
     */
    public $navOffset=1;
    
    /**
     * Pagina actual
     */
    public $navActPg=0;
    
    /**
     * Numero total de paginas
     */
    public $navTotalPg=0;
    
    /**
     * Numero de registros por p�gina
     */
    public $navRowsPerPg=10;
    
    /**
     * Numero de pagina previa (si no hay 0)
     */
    public $navPrev=0;
    
    /**
     * Numero de pagina siguiente (si no hay 0)
     */
    public $navNext=0;
    
    /**
     * L�mite inferior de registros mostrados
     */
    public $infLimit;
    
    /**
     * Limite superior de registros mostrados
     */
    public $supLimit;
    
    /**
     * Rango donde empezar� la consulta (exclusive)
     */
    public $fromRange;
    
    /**
     * Directorio donde se encuentra el template de
     * paginacion
     * @var string
     * @example mi/directorio
     */
    public $tplDir='';
    
    /**
     * Nombre del archivo template con extension
     */
    public $tplFile='paginator.html';
    
    /**
     * Nombre de la clase de template
     */
    public $tplEngine='TemplateEngine';
    
    /**
     * Keyword de la seccion donde ir�n los links de paginado
     */
    public $tplSection='Pag';
    
    /**
     * Keyword de la condici�n para validar si es la pagina actual
     */
    public $tplCurCond='CurPg';
    
    /**
     * Keyword para validar si hay link de previo
     */
    public $tplPrevCond='Prev';
    
    /**
     * Keyword para validar si hay link de siguiente
     */
    public $tplNextCond='Next';
    
    /**
     * Keyword para link de siguiente pagina
     */
    public $tplNext='NextPg';
    
    /**
     * Keyword para link de ultima pagina
     */
    public $tplLast='LastPg';
    
    /**
     * Mostrando DESDE el registro...
     */
    public $tplFromReg='DesdeReg';
    
    /**
     * Mostrando HASTA el registro...
     */
    public $tplToReg='HastaReg';
    
    /**
     * Key para el TOTAL de registros...
     */
    public $tplTotalReg='TotalReg';
    
    /**
     * Keyword para mostrar el numero de pagina actual
     */
    public $tplPg;
    
    /**
     * Key para el link de pagina previa
     */
    public $tplPrev='PrevPg';
    
    /**
     * Key para el link de primera pagina
     */
    public $tplFirst='FirstPg';
    
    /**
     * Key para el link de pagina (iterado)
     */
    public $tplItem='PgNum';
    
    /**
     * Url de la pagina
     */
    public $tplItemUrl='PgUrl';
    
    /**
     * Html resultado del template compilado
     */
    public $tplHtml='';
    
    /**
     * Numero total de registros devueltos por la consulta
     */
    public $numRows=0;
    
    /**
     * Array con las ligas de navegacion
     */
    public $nav=array();
    
    /**
     * Resultado del query
     */
    private $result=NULL;
    
    /**
     * Error
     */
    public $showErrors=false; 
    
    /**
    * Habilitar para uso con ajax
    */
    public $ajax=false;
    public $module=false;
    public $control=false;
    public $target=NULL;
    public $callback=NULL;
     
    /**
     * Paginator::__construct()
     * 
     * @param mixed $sql
     * @return
     */
    public function __construct($sql=NULL,$navPgVar=NULL){
        if(!is_null($sql))
            $this->sql=$sql;
        if(!is_null($navPgVar))
            $this->navPgVar=$navPgVar;
        $this->tplDir=Tpl::htmlPath().'nav';
        $this->navActPg=empty($_REQUEST[$this->navPgVar]) ? 1 : $_REQUEST[$this->navPgVar];
        $this->sql=preg_replace("/LIMIT [0-9]+, [0-9]+/",'',$this->sql); // Remove limits
    }
    
    /**
     * Funcion principal para iniciar todos los procedimientos
     * 
     * @param integer $navActPg    [opcional] Numero de pagina actual
     * @param mixed $navRowsPerPg [opcional] Numero de registros por pagina
     * @return
     */
    public function paginate($compile=true,$navActPg=NULL,$navRowsPerPg=NULL){
        $db = Db::getInstance();
        if(!is_null($navActPg))
            $this->navActPg=$navActPg;
        if(empty($this->navActPg) || $this->navActPg<1)
            $this->navActPg=1;
        if(!is_null($navRowsPerPg))
            $this->navRowsPerPg=$navRowsPerPg;
        if(empty($this->sql))
            $this->error("paginate()","Empty sql query");
        $this->result=$db->query($this->sql);
        if(!$this->result)
            $this->error("paginate()","Empty result");
        $this->numRows=$db->numRows(); // Number of rows
        $this->navTotalPg=ceil($this->numRows/$this->navRowsPerPg); // Number of pages
        if($this->navActPg>$this->navTotalPg){
            $this->navActPg=$this->navTotalPg;
        }
        $this->infLimit=($this->navRowsPerPg*($this->navActPg-1))+1;
        $this->fromRange=$this->infLimit-1;
        if((($this->infLimit-1)+$this->navRowsPerPg)>$this->numRows){
            // Last page
            $this->supLimit=$this->numRows;
        }else{
            $this->supLimit=$this->infLimit+$this->navRowsPerPg-1;
        }
        $this->navPrev=($this->navActPg==1) ? 0 : $this->navActPg-1;
        $this->navNext=($this->navActPg==$this->navTotalPg) ? 0 : $this->navActPg+1;
        $this->createNav();
        if($compile)
            $this->compileNavTpl();
    }
    
    /**
     * Funcion para crear el array de navegacion conteniendo
     * los indices de las paginas de navegacion
     * 
     * @return
     */
    private function createNav(){
        if(empty($this->numRows))
            $this->error("createNav()","Empty result");
        for($i=$this->navActPg; $i<=($this->navActPg+$this->navOffset);$i++){
            if($i>$this->navTotalPg)
                break;
            $this->nav[$i]=$i;
        }
        if($this->navActPg<$this->navOffset){
            $lim=1;
        }else{
            $lim=($this->navActPg-$this->navOffset);
        }
        for($i=$this->navActPg; $i>=$lim; $i--){
            if($i<1)
                break;
            $this->nav[$i]=$i;
        }
        sort($this->nav);
        // Create definitive sql query string (including limits)
        $this->navSql=$this->sql." LIMIT {$this->fromRange}, {$this->navRowsPerPg}";
    }
    
    /**
     * Funcion para compilar y crear el template especificado
     * 
     * @param string $tplDir Ruta del template
     * @param string $tplFile Nombre del archivo
     * @param string $tplEngine Nombre de la clase
     * @return string HTML del template
     */
    public function compileNavTpl($tplDir=NULL,$tplFile=NULL,$tplEngine=NULL){
        if(!is_null($tplDir))
            $this->tplDir=$tplDir;
        if(!is_null($tplFile))
            $this->tplFile=$tplFile;
        if(!is_null($tplEngine))
            $this->tplEngine=$tplEngine;

        $get=$this->parseGet('request',array($this->navPgVar));
        //$post=$get['post'];
        //$get=$get['get'];
        
        // Usar la siguiente declaraci�n en caso de no contar con la funci�n url()
        $module=empty($this->module) ? DSP_MODULE : $this->module;
        $control=empty($this->control) ? Router::$Control['control'] : $this->control;
        $url=$module.(empty($control) ? '' : '/'.$control)."?".$get.(!empty($get) ? '&' : '').$this->navPgVar."=";
        $class_name=$this->tplEngine;
        $tpl=new $class_name();
        $tpl->load($this->tplDir."/".$this->tplFile);
        $tpl->rel=($this->ajax==true) ? 'ajax' : '';
        $tpl->tar=($this->ajax==true) ? $this->target : '';
        $tpl->callback=($this->ajax==true) ? $this->callback : '';

        $paginator=array();
        foreach($this->nav as $i => $num){
            $paginator[]=array(
                'current' => ($num==$this->navActPg),
                $this->tplItem => $num,
                $this->tplItemUrl => url($url.$num),
                'post' => $post
            );
        }
        $tpl->assign('paginator',$paginator);
        $tpl->assign($this->tplNextCond,($this->navActPg!=$this->navTotalPg));
        $tpl->assign($this->tplNext,url($url.$this->navNext));
        $tpl->assign($this->tplLast,url($url.$this->navTotalPg));

        $tpl->assign($this->tplPrevCond,($this->navActPg!=1));
        $tpl->assign($this->tplFirst,url($url.'1'));
        $tpl->assign($this->tplPrev,url($url.$this->navPrev));

        //$tpl->set($this->tplPg,$this->navActPg);
        $tpl->assign($this->tplFromReg,$this->infLimit);
        $tpl->assign($this->tplToReg,$this->supLimit);
        $tpl->assign($this->tplTotalReg,$this->numRows);
        $tpl->post=$post;
        
        $this->tplHtml=$tpl->render(); 
        return $this->tplHtml;
    }
    
    /**
     * Construye el get en formato K/V http query
     * 
     * @return string
     */
    function parseGet($method='get',$except=array()){
        if(sizeof($except)>0){
            $request=$get;
            foreach($except as $i => $val){
                unset($get[$val]);
                unset($request[$val]);
            }
        }
        $get=$_GET;
        if(!empty($_POST['search']))
            $get['search']=$_POST['search'];
        if($method=='get'){
            $ret=http_build_query($get);
            return (!empty($ret)) ? $ret : '';
        }elseif($method=='request'){
            foreach($_POST as $k => $v){
                $get[$k]=$v;
            }
            $ret=@http_build_query($get);
            return $ret;
            //return (!empty($ret) || !empty($_POST)) ? array('get' => $ret, 'post' => http_build_query($_POST)) : '';
        }
    }
    
    public function limitQuery($include_kw = true){
        return ($include_kw ? "Limit " : '').$this->fromRange.", ".$this->navRowsPerPg;
    }
    
    /**
     * Error handler
     * 
     * @param mixed $source
     * @param mixed $msg
     * @return
     */
    private function error($source,$msg){
        if($this->showErrors)
            die("<b>Paginator::$source</b>: $msg");
    }
}