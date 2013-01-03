<?php
/**~engine/lib/UserRoleRegistry.php
* 
* UserRoleRegistry (Singleton)
* ---
* User role registry control system
* 
* @package      FALCODE
* @package      Multi-user System Add-on
* @version      3.0
* @author       FALCODE
*/
class UserRoleRegistry{
    /**
    * Aqui se guarda todo el registro de roles
    * 
    * @var mixed
    */
    public $roles=array();
    private static $instance=NULL;
    
    private function __construct(){
        // Agregamos rol publico por defecto
        $this->add(new UserRole(UserRole::USER_PUBLIC));
        $this->add(new UserRole(UserRole::USER_SUPER_ADMIN));
    }
    
    /**
    * Llama a la instancia del singleton
    * 
    */
    public static function getInstance(){
        if(self::$instance==NULL)
            self::$instance=new UserRoleRegistry();
        return self::$instance;
    }
    
    /**
    * Agrega un rol al registro de roles
    * 
    * @param UserRole $role
    * @param mixed $parents
    */
    public function add(UserRole $role, $parents=NULL){
        $roleId=$role->getRoleId();
        if($this->roleExists($roleId))
            return true;
            
        // Recorremos parents
        $parentRoles=array();
        if(!is_null($parents) && !is_array($parents))
            $parents=array($parents);
        if(!empty($parents)){
            foreach($parents as $i => $parent){
                $parentId=NULL;
                if($parent instanceof UserRole){
                    $parentId=$parent->getRoleId();
                }else{
                    if($this->roleExists($parent))
                        $parentId=$parent;
                    else
                        throw new Exception("El rol padre <b>$parent</b> no existe en el registro.");
                }
                $parentRoles[]=$parentId;
                $this->roles[$parentId]['children'][]=$roleId;
            }
        }
        
        // Agregamos rol al registro
        $this->roles[$roleId]=array(
            'instance'  => $role,
            'children'  => array(),
            'parent'    => $parentRoles
        );
    }
    
    /**
    * Devuelve un arreglo con todos los id de roles
    * 
    */
    public function getAllRolesId(){
        $roles=array();
        foreach($this->roles as $id => $role)
            $roles[]=$id;
        return $roles;
    }
    
    /**
    * Vierifica si el rol existe en el registro
    * 
    * @param mixed $roleId
    * @return mixed
    */
    public function roleExists($roleId){
        return isset($this->roles[$roleId]);
    }
    
    /**
    * Resetea los roles
    * 
    */
    public function clearRoles(){
        $this->roles=array();
    }
}