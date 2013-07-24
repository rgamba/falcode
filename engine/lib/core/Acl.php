<?php
/**
* Access control list
* 
* Defines all the users and permissions
* for the system. By default the permission
* tree is created dynamically based on the 
* user database tables.
* 
* @package  FALCODE
*/
class Acl extends AccessControl{
    /**
    * Declaramos roles y permisos
    */
    public function __construct(){
        $Db = Db::getInstance();
        $Load=&Loader::getInstance();
        $Load->helper('array');
        $this->failUrl(url('login'));

        // Agregamos roles
        $Ur = $Db->fetch("SELECT u.*, ur.name as nombre_padre FROM user_role u LEFT JOIN user_role ur on ur.id_user_role = u.id_parent ORDER BY id_parent ASC");
        if($Ur->num_rows > 0){
            foreach($Ur->rows as $ur){
            //while($ur=$Ur->next()){
                if(empty($ur['id_padre'])){
                    $this->addRole(new UserRole($ur['name']));
                }else{
                    $this->addRole(new UserRole($ur['name']),$ur['nombre_padre']);
                }
            }
        }
        
        // Establecemos permisos
        $parents=array();
        $psql=array();
        $roleId=$Db->getVal('user_role','name',$this->getRole(),'id_user_role');
        $this->getDbParents($roleId,$parents);
        
        foreach($parents as $pid)
            $psql[].="ur.id_user_role = '$pid'";
        $psql[]="u.id_user_role IS NULL";
        //$Up=new UsuarioPermiso;
        $Up = $Db->fetch("SELECT u.*,ur.name as nombre_rol FROM user_permission u LEFT JOIN user_role ur USING(id_user_role) WHERE ur.name = '".$this->getRole()."' OR ".implode(' OR ',$psql)." ORDER BY u.id_user_role, u.module,u.action");
        // Obtenemos unicamente los permisos del rol actual
        // o de roles padres para establecer el arbol
        // Los permisos se ordenan por ROL > MODULO > ACCION

        if($Up->num_rows>0){
            foreach($Up->rows as $up){
            //while($Up->next()){
                $mod=explode(',',$up['module']);
                $act=explode(',',$up['action']);
                $sub=($up['subdomain']=='' || $up['subdomain']==NULL) ? '*' : $up['subdomain'];

                $up['mode']=='allow'
                    ? $this->allow(
                        ($up['id_user_role']=='' ? NULL : $up['nombre_rol']),
                        ($up['module']==''         ? NULL : $mod),
                        ($up['action']==''         ? NULL : $act),
                        $sub)
                    : $this->deny(
                        ($up['id_user_role']=='' ? NULL : $up['nombre_rol']),
                        ($up['module']==''         ? NULL : $mod),
                        ($up['action']==''         ? NULL : $act));
            }
        }
        return;
    }
    
    /**
    * Obtiene todos los padres del rol
    * 
    * @param mixed $roleId
    * @param mixed $arr
    */
    public function getDbParents($roleId,array &$arr){
        $db = Db::getInstance();
        $Ur = $db->fetch("SELECT * FROM user_role WHERE id_user_role = $roleId");
        /*$Ur=new UsuarioRol($roleId);
        $Ur->select();  $Ur->next();*/

        $parent=$Ur->row['id_padre'];
        if(empty($parent))
            return false;
        $Urp = $db->fetch("SELECT * FROM user_role WHERE id_user_role = $parent");
        //$Urp=new UsuarioRol($parent);
        //$Urp->select();  $Urp->next();
        $grandpa=$Urp->row['id_parent'];
        if(!empty($grandpa)){
            // Aun tiene padres... 
            $arr[]=$parent;
            $this->getDbParents($parent,$arr);
        }else{
            // Ya no tiene padres
            $arr[]=$parent;
        }

        return array_flatten($arr);
    }
    
    /**
    * Devuelve los permisos heredados al usuario en formato:
    * ['modulo']['accion']=true | false
    * 
    * @param mixed $roleId
    * @param mixed $arr
    */
    public function getInheritedDbPermissions($roleId){
        $db = Db::getInstance();

        $parents=array();
        $this->getDbParents($roleId,$parents);
        if(empty($parents))
            return false;
        $permisos=array();
        // Los permisos se van sobreescribiendo, de manera
        // el el ancestro mas viejo tiene mejor prioridad
        $parents=array_reverse($parents);
        foreach($parents as $i => $pId){
            $Up = $db->fetch("SELECT * FROM user_permission WHERE id_user_role = $pId");
            //$Up=new UsuarioPermiso();
            //$Up->select(NULL,"WHERE id_user_rol = '$pId'");
            if($Up->num_rows>0){
                foreach($Up->rows as $row){
                //while($row=$Up->next()){
                    if(empty($row['module']))
                        continue;
                    if(empty($row['action']))
                        $row['action']='*'; // Wildcard para todos...
                    $permisos[$row['module']][$row['action']]=$row['mode']=='allow' ? true : false;
                }
            }
        }
        return $permisos;
    }
}

