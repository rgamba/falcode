<?php
/**~engine/lib/AccessControl.php
* 
* AccessControl (Singleton)
* ---
* Controla los accesos a los modulos y acciones
* del sistema dependiento el tipo de usuario
* 
* @package      FALCODE
* @package      Multi-user System Add-on
* @version      2.0
* @author       FALCODE
*/
class AccessControl{
    /**
    * Constantes
    */
    const ALLOW="allow";
    const DENY="deny";
    const ACCESS_ALL="all";
    const AUTO_REDIRECT_ON_DENY=true;
    const DIE_ON_DENY=true;
    const MSG_ON_DENY="Lo sentimos, usted no tiene los permisos necesarios para acceder a esta zona.";
    const STRICT_MODE=false;
    
    private static $instance=NULL;
    public static $redirectUrl=NULL;
    private $accConfig=array();
    private $roleRegistry=NULL;
    private $rules=array();
    private $rulesOrder=array();
    private $rulesIndex=0;
    
    private function __construct(){
        return true;
    }
    
    /**
    * Llama a la instancia del singleton
    * 
    */
    public static function getInstance(){
        if(self::$instance==NULL)
            self::$instance=new AccessControl();
        return self::$instance;
    }
    
    /**
    * Concede permisos a un rol de usuario
    * NOTA: para conceder el permiso a todos los roles, establecer rol=NULL,
    * para dar el permiso a todos los modulos, establecer modules=NULL.
    * Esta misma funcionalidad aplica para la funcion deny()
    * 
    * @param mixed $user_lvl Del tipo constante 
    * @param mixed $route
    */
    final public function allow($role=NULL,$modules=NULL,$actions=NULL,$subdomain="*"){
        try{
            $this->addRule($role,self::ALLOW,$modules,$actions,$subdomain);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }
    
    /**
    * Impide el acceso a un usuario a un modulo y acciones
    * NOTA: Los accesos se conceden y sobreescriben conforme se van otorgando...
    * Por conceniencia primero se restringe el acceso a un modulo(s) de manera general
    * y luego se concede el acceso a ciertos modulos en especifico...
    * 
    * @param mixed $user_lvl
    * @param mixed $route
    */
    final public function deny($role=NULL,$modules=NULL,$actions=NULL,$subdomain="*"){
        try{
            $this->addRule($role,self::DENY,$modules,$actions,$subdomain);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }
    
    /**
    * Establece una regla de acceso
    * 
    * @param UserRole $role
    * @param mixed $action
    * @param mixed $modules
    * @param mixed $actions
    * ---
    * Formato del arreglo rules:
    * $rules=array(
    *   'subdomain' => array(
    *       $roleId => array(
    *           'allow' => array(
    *               $moduleId => array(
    *                       'actions' => array('list','view')
    *               )
    *           ),
    *           'deny' => array(
    *               $moduleId => array(
    *                       'actions' => array('delete','edit','add')
    *               )
    *           )
    *       )
    *   )
    * );
    */
    private function addRule($role=NULL,$action=self::ALLOW,$modules=NULL,$actions=null,$subdomain='*'){
        if(is_string($role))
            $role=new UserRole($role);
        // En caso de no especificar, se aplicaría a todos los roles de usuario
        if($role==NULL){
            $_role=$this->getRoleRegistry()->getAllRolesId();
            foreach($_role as $r)
                $role[]=new UserRole($r);
        }

        if(!is_array($role))
            $role=array($role);

        if($action!=self::ALLOW && $action!=self::DENY)
            throw new Exception("Tipo de acceso invalido");
        
        // Recorremos por cada uno de los roles
        foreach($role as $_role){
            $roleId=($_role instanceof UserRole) ? $_role->getRoleId() : $_role;
           
            if(!$this->getRoleRegistry()->roleExists($roleId))
                throw new Exception("El rol <b>$roleId</b> no existe y no se le pueden otorgar privilegios");
            
            if($modules!=NULL){    
                // Verificamos que el modulo o modulos existan
                if(!is_array($modules))
                    $modules=array($modules);
                
                if(self::STRICT_MODE){
                    foreach($modules as $i => $modName){
                        if(!is_dir(PATH_CONTROLLER_MODULES.$modName))
                            throw new Exception("El modulo <b>$modName</b> al que se quiere otorgar provilegios, no existe.");
                    }   
                }
                
                // Agregamos la regla
                foreach($modules as $modName){
                    if(!isset($this->rules[$subdomain][$roleId][$action][$modName])){
                        // No existe aun regla para este modulo
                        $this->rules[$subdomain][$roleId][$action][$modName]=array(
                            'actions'   => is_array($actions) ? $actions : (is_null($actions) ? array() : array($actions))
                        );
                        if(!empty($actions)){
                            foreach($this->rules[$subdomain][$roleId][$action][$modName]['actions'] as $act)
                                $this->setRuleIndex($roleId.'.'.$action.'.'.$modName.'.'.$act);
                        }else{
                            $this->setRuleIndex($roleId.'.'.$action.'.'.$modName);
                        }
                        
                    }else{
                        // Ya tenemos regla para el modulo... agregamos accion
                        if($actions!=NULL){
                            if(is_array($actions)){
                                foreach($actions as $act){
                                    $this->rules[$subdomain][$roleId][$action][$modName]['actions'][]=$act;
                                    $this->setRuleIndex($roleId.".".$action.".".$modName.".".$act);
                                }
                            }else{
                                $this->rules[$subdomain][$roleId][$action][$modName]['actions'][]=$actions;
                                $this->setRuleIndex($roleId.".".$action.".".$modName.".".$actions);
                            }
                        }else{
                            $this->rules[$subdomain][$roleId][$action][$modName]['actions']=array();
                            $this->setRuleIndex($roleId.".".$action.".".$modName);
                        }
                    }
                }
            }else{
                // Tenemos que agregar la regla a todos los modulos
                // el comodín '*' significa 'todos'... al igual que para acciones
                if(is_array($actions))
                    foreach($actions as $act){
                        $this->rules[$subdomain][$roleId][$action]['*']['actions'][]=$act;
                        $this->setRuleIndex($roleId.".".$action.".*.".$act);
                    }
                else{
                    if($actions==NULL){
                        $this->rules[$subdomain][$roleId][$action]['*']['actions']=array(); // Todas las acciones
                        $this->setRuleIndex($roleId.".".$action.".*");
                    }else{
                        $this->rules[$subdomain][$roleId][$action]['*']['actions'][]=$actions;
                        $this->setRuleIndex($roleId.".".$action.".*.".$actions);
                    }
                }
            }
        }
    }
    
    private function setRuleIndex($key){
        $this->rulesOrder[$key]=$this->rulesIndex;
        $this->rulesIndex++;
    }
    
    /**
    * Determina si el rol determinado tiene acceso al modulo (y accion)
    * Devuelve true si y solo si el rol tiene concedido acceso al modulo
    * y no se le ha denegado ningun acceso a la combinacion modulo/accion
    * especificada.
    * 
    * @param mixed $rol
    * @param mixed $module
    * @param mixed $action
    */
    final public function isAllowed($rol,$module,$action=NULL,$subdomain="*"){
        if(is_string($rol))
            $rol=new UserRole($rol);
        if(!($rol instanceof UserRole))
            throw new Exception("El objeto rol debe ser de tipo UserRole");
        $roleId=$rol->getRoleId();
        if(!$this->getRoleRegistry()->roleExists($roleId))
            throw new Exception("El rol <b>$roleId</b> no esta registrado en UserRoleRegistry");
            
        // Accesos heredados
        $inherited=$this->getInheritedPermissions($roleId);
        $inheritedBy=$inherited['inherited_by'];
        $inherited=$inherited['inherited'];

        if(empty($this->rules[$subdomain][$roleId][self::ALLOW]))
            $this->rules[$subdomain][$roleId][self::ALLOW]=array();
        if(empty($this->rules[$subdomain][$roleId][self::DENY]))
            $this->rules[$subdomain][$roleId][self::DENY]=array();
        
        // Evaluamos acceso
        $allow=array_merge($this->rules[$subdomain][$roleId][self::ALLOW],$inherited[self::ALLOW]);
        $deny=array_merge($this->rules[$subdomain][$roleId][self::DENY],$inherited[self::DENY]);

        $isAllowed=true;
        $tmpIndex=-1;
        $tmpIndex2=-1;
        $maxAllowRuleIndex=-1;
        $maxDenyRuleIndex=-1;
        $empty=true;
        
        // Checamos permisos concedidos
        if(in_array($module,array_keys($allow)) || in_array('*',array_keys($allow))){
            $empty=false;
            // Tiene acceso
            foreach($allow as $modName => $actions){
                if($modName!=$module && $modName!='*')
                    continue;
                $actions=$actions['actions'];
                if(empty($actions) || in_array($action,$actions)){
                    $_roleId=(isset($inheritedBy[self::ALLOW.".".$modName.(empty($actions) ? '' : ".".$act)]))
                        ? $_roleId=$inheritedBy[self::ALLOW.".".$modName.(empty($actions) ? '' : ".".$act)]
                        : $roleId;

                    $tmpIndex=empty($actions) 
                        ? $this->rulesOrder[$_roleId.".allow.".$modName] 
                        : $this->rulesOrder[$_roleId.".allow.".$modName.".".$action];

                    if($tmpIndex>$maxAllowRuleIndex){
                        $maxAllowRuleIndex=$tmpIndex;
                    }
                }
            }
        }
        
        // Checamos permisos denegados
        if(in_array($module,array_keys($deny)) || in_array('*',array_keys($deny))){
            $empty=false;
            // Tiene acceso denegado
            foreach($deny as $modName => $actions){
                if($modName!=$module && $modName!='*')
                    continue;
                $actions=$actions['actions'];
                if(empty($actions) || in_array($action,$actions)){
                    $tmpIndex2=empty($actions) 
                        ? $this->rulesOrder[$roleId.".deny.".$modName] 
                        : $this->rulesOrder[$roleId.".deny.".$modName.".".$action];
                    // Verificamos que la regla de bloqueo haya sido
                    // establecida DESPUES que la regla de autorizacion
                    if($tmpIndex2>$maxDenyRuleIndex){
                        $maxDenyRuleIndex=$tmpIndex2;
                    }
                }
            }
        }
        
        if($maxAllowRuleIndex<$maxDenyRuleIndex || $empty){
            $isAllowed=false;
        }
        return $isAllowed;
    }
    
    /**
    * Funcion para obtener el arbol de padres
    * 
    * @param mixed $roleId
    * @param array $arr Arreglo donde se van a guardar los resultados obtenidos
    * de la funcion recursiva
    */
    final public function getAllParents($roleId,&$arr){
        $parent=$this->getRoleRegistry()->roles[$roleId]['parent'];
        if(empty($parent))
            return false;
        foreach($parent as $pId){
            if(!empty($this->getRoleRegistry()->roles[$pId]['parent'])){
                // Aun tiene padres... 
                $arr[]=$pId;
                $this->getAllParents($pId,$arr);
            }else{
                // Ya no tiene padres
                $arr[]=$pId;
            }
        }
        return array_flatten($arr);
    }
    
    /**
    * Verifica que el usuario tenga acceso a la ruta actual
    * 
    */
    final public function guard($return=false){
        // Damos permiso total al super admin
        if($this->getRoleRegistry()->roleExists(UserRole::USER_SUPER_ADMIN))
            $this->allow(UserRole::USER_SUPER_ADMIN);
           
        // Para control en subdominios... o tiene acceso particular al subdominio o tiene acceso general
        // cualquiera de las dos condiciones da acceso
        $sub=SUBDOMAIN=="" ? "*" : SUBDOMAIN;
        $allow=$this->isAllowed(User::getRoleId(),DSP_MODULE,DSP_CONTROL,$sub) || $this->isAllowed(User::getRoleId(),DSP_MODULE,DSP_CONTROL,'*');
        if($return)
            return $allow;
        if(!$allow){
            if(self::AUTO_REDIRECT_ON_DENY){
                if(User::getRoleId()==UserRole::USER_PUBLIC && DSP_MODULE!='content' && DSP_MODULE!='login')
                    $_SESSION['_fwd_']=DSP_MODULE.'/'.DSP_CONTROL;
                $this->redirectFail();
            }else
                if(self::DIE_ON_DENY)
                    die(self::MSG_ON_DENY);
        }
        return $deny;      
    }
    
    /**
    * Check if the current user has access to given location
    * 
    * @param mixed $module
    * @param mixed $control
    * @param mixed $sub
    */
    final public function isAllowedOnLocation($module,$control,$sub=""){
        return $this->isAllowed(User::getRoleId(),$module,$control,$sub) || $this->isAllowed(User::getRoleId(),$module,$control,'*');
    }
    
    /**
    * Divide la ruta...
    * 
    * @param mixed $route
    */
    private function translateRoute($route){
        $r=explode('/',$route);
        $_r=object();
        $_r->module=$r[0];
        $_r->action=$r[1];
        return $_r;
    }
    
    /**
    * Redirige en caso de error
    * 
    */
    private function redirectFail(){
        if(empty(self::$redirectUrl))
            self::$redirectUrl=url("usuario/login");

        redirect(self::$redirectUrl);
        exit;
    }
    
    /**
    * Establece la url a donde redirigir al usuario
    * en caso de error
    * 
    * @param mixed $url
    */
    final protected function failUrl($url){
        if(!empty($url))
            self::$redirectUrl=$url;
    }
    
    /**
    * Da de alta un Rol al sistema
    * 
    * @param UserRole $role
    * @param mixed $parents
    */
    final public function addRole(UserRole $role,$parents=NULL){
        try{
            $this->getRoleRegistry()->add($role,$parents);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }
    
    /**
    * Devuelve el objecto
    * 
    */
    final public function getRoleRegistry(){
        if($this->roleRegistry==NULL)
            $this->roleRegistry=UserRoleRegistry::getInstance();
        return $this->roleRegistry;
    }
    
    /**
    * Establece el rol
    * 
    * @param mixed $role
    */
    final public function setRole($role){
        return User::setRole($role);
    }
    
    /**
    * Devuelve el rol
    * 
    * @param mixed $role
    */
    final public function getRole(){
        return User::getRoleId();
    }
    
    /**
    * Obtiene los permisos heredados
    * 
    * @param mixed $roleId
    */
    final public function getInheritedPermissions($roleId=NULL,$subdomain="*"){
        if(is_null($roleId))
            return false;

        // Accesos heredados
        $inherited=array('allow'=>array(),'deny'=>array());
        $parents=array();
        $inheritedBy=array();
        // Obtenemos arbol de padres
        $this->getAllParents($roleId,$parents);
        // Analizamos cada padre
        foreach($parents as $pId){
            // Agregamos ALLOWs heredados
            if(!empty($this->rules[$subdomain][$pId][self::ALLOW])){
                foreach($this->rules[$subdomain][$pId][self::ALLOW] as $moduleName => $allow){
                    foreach($allow as $k => $module){
                        if(!empty($inherited[self::ALLOW][$moduleName]))
                            $inherited[self::ALLOW][$moduleName]=array('actions'=>array());
                            
                        if(is_array($module) && !empty($module)){
                            foreach($module as $h => $act){
                                $inherited[self::ALLOW][$moduleName]['actions'][]=$act;
                                $inheritedBy[self::ALLOW.".".$moduleName.".".$act]=$pId;
                            }
                        }else{
                            if(empty($module)){
                                $inherited[self::ALLOW][$moduleName]['actions']=array();
                                $inheritedBy[self::ALLOW.".".$moduleName]=$pId;
                            }else{
                                $inherited[self::ALLOW][$moduleName]['actions'][]=$module;
                                $inheritedBy[self::ALLOW.".".$moduleName.".".$module]=$pId;
                            }
                        }
                    }
                }
            }
            // Agregamos DENYs heredados
            if(!empty($this->rules[$subdomain][$pId][self::DENY])){
                foreach($this->rules[$subdomain][$pId][self::DENY] as $moduleName => $deny){
                    foreach($deny as $k => $module){
                        if(!empty($inherited[self::DENY][$moduleName]))
                            $inherited[self::DENY][$moduleName]=array('actions'=>array());

                        if(is_array($module) && !empty($module)){
                            foreach($module as $h => $act){
                                $inherited[self::DENY][$moduleName]['actions'][]=$act;
                                $inheritedBy[self::ALLOW.".".$moduleName.".".$act]=$pId;
                            }
                        }else{
                            if(empty($module)){
                                $inherited[self::DENY][$moduleName]['actions']=array();
                                $inheritedBy[self::ALLOW.".".$moduleName.".".$act]=$pId;
                            }else{
                                $inherited[self::DENY][$moduleName]['actions'][]=$module;
                                $inheritedBy[self::ALLOW.".".$moduleName.".".$module]=$pId;
                            }
                        }
                    }
                }
            }
        }
        return array(
            'inherited' => $inherited,
            'inherited_by' => $inheritedBy
        );
    }
}