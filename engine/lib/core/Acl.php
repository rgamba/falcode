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
        /*$Ur=new UsuarioRol();
        $Ur->select(
            NULL,
            "ORDER BY id_padre ASC","LEFT JOIN usuario_rol ur on ur.id_usuario_rol = u.id_padre",
            "u.*, ur.nombre as nombre_padre"
        );*/
        $Ur = $Db->fetch("SELECT u.*, ur.nombre as nombre_padre FROM usuario_rol u LEFT JOIN usuario_rol ur on ur.id_usuario_rol = u.id_padre ORDER BY id_padre ASC");
        if($Ur->num_rows > 0){
            foreach($Ur->rows as $ur){
            //while($ur=$Ur->next()){
                if(empty($ur['id_padre'])){
                    $this->addRole(new UserRole($ur['nombre']));
                }else{
                    $this->addRole(new UserRole($ur['nombre']),$ur['nombre_padre']);
                }
            }
        }
        
        // Establecemos permisos
        $parents=array();
        $psql=array();
        $roleId=$Db->getVal('usuario_rol','nombre',$this->getRole(),'id_usuario_rol');
        $this->getDbParents($roleId,$parents);
        
        foreach($parents as $pid)
            $psql[].="ur.id_usuario_rol = '$pid'";
        $psql[]="u.id_usuario_rol IS NULL";
        //$Up=new UsuarioPermiso;
        $Up = $Db->fetch("SELECT u.*,ur.nombre as nombre_rol FROM usuario_permiso u LEFT JOIN usuario_rol ur USING(id_usuario_rol) WHERE ur.nombre = '".$this->getRole()."' OR ".implode(' OR ',$psql)." ORDER BY u.id_usuario_rol, u.modulo,u.accion");
        // Obtenemos unicamente los permisos del rol actual
        // o de roles padres para establecer el arbol
        // Los permisos se ordenan por ROL > MODULO > ACCION
        /*$Up->select(
            NULL,
            "WHERE ur.nombre = '".$this->getRole()."' OR ".implode(' OR ',$psql).
            " ORDER BY u.id_usuario_rol, u.modulo,u.accion",
            "LEFT JOIN usuario_rol ur USING(id_usuario_rol)",
            "u.*,ur.nombre as nombre_rol"
        );*/
        if($Up->num_rows>0){
            foreach($Up->rows as $up){
            //while($Up->next()){
                $mod=explode(',',$up['modulo']);
                $act=explode(',',$up['accion']);
                $sub=($up['subdominio']=='' || $up['subdominio']==NULL) ? '*' : $up['subdominio'];

                $up['modo']=='allow'
                    ? $this->allow(
                        ($up['id_usuario_rol']=='' ? NULL : $up['nombre_rol']),
                        ($up['modulo']==''         ? NULL : $mod),
                        ($up['accion']==''         ? NULL : $act),
                        $sub)
                    : $this->deny(
                        ($up['id_usuario_rol']=='' ? NULL : $up['nombre_rol']),
                        ($up['modulo']==''         ? NULL : $mod),
                        ($up['accion']==''         ? NULL : $act));
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
        $Ur = $db->fetch("SELECT * FROM usuario_rol WHERE id_usuario_rol = $roleId");
        /*$Ur=new UsuarioRol($roleId);
        $Ur->select();  $Ur->next();*/

        $parent=$Ur->row['id_padre'];
        if(empty($parent))
            return false;
        $Urp = $db->fetch("SELECT * FROM usuario_rol WHERE id_usuario_rol = $parent");
        //$Urp=new UsuarioRol($parent);
        //$Urp->select();  $Urp->next();
        $grandpa=$Urp->row['id_padre'];
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
            $Up = $db->fetch("SELECT * FROM usuario_permiso WHERE id_usuario_rol = $pId");
            //$Up=new UsuarioPermiso();
            //$Up->select(NULL,"WHERE id_usuario_rol = '$pId'");
            if($Up->num_rows>0){
                foreach($Up->rows as $row){
                //while($row=$Up->next()){
                    if(empty($row['modulo']))
                        continue;
                    if(empty($row['accion']))
                        $row['accion']='*'; // Wildcard para todos...
                    $permisos[$row['modulo']][$row['accion']]=$row['modo']=='allow' ? true : false;
                }
            }
        }
        return $permisos;
    }
}

