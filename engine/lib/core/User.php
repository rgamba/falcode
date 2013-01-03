<?php
/**~engine/lib/User.php
* 
* User (Singleton)
* ---
* Holds user session information
* 
* @package      FALCODE
* @package      Multi-user System Add-on
* @version      3.0
* @author       FALCODE
*/
class User{    
    // Variables
    private static $role=NULL;
    
    public function __construct(){
        
    }
    
    public function role(){
        return self::getRoleId();
    }
    
    public function pic($id,$w=50,$h=50,$f=true){
        return self::getPic($id,$w,$h,$f);
    }
    
    public function logged(){
        return self::islogged();
    }
    
    public function clear(){
        self::wipeSession();
    }
    
    public function config($var,$val=NULL){
        return self::configuration($var,$val);
    }
    
    public static function getPic($id=NULL,$w=50,$h=50,$f=true){
        if(!empty($id)){
            $Usuario=new Usuario();
            $Usuario->select($id);
            $user=$Usuario->next();
        }else{
            $user=$_SESSION[Auth::SES_LOGIN_INDEX];
        }

        $imagen=$user['imagen'];
        if(empty($imagen)){
            if($user['sexo']=="f")
                $imagen="defprofile.png";   
            else
                $imagen="defprofile.png";
        }
        
        if($w==NULL)
            return $imagen;
            
        return url('image?src='.$imagen.'&folder=usuario&height='.$h.'&width='.$w.'&force='.(!$f ? 0 : 1));   
    }
    
    public static function configuration($var,$val=NULL){
        $Usuario = new UsuarioConfig();
        $Usuario->where("id_usuario = {0} AND var = '{1}'",self::get("id_usuario"),$var)->execute();
        if($Usuario->rows <= 0){
            // No hay registro
            if(is_null($val)){
                return NULL;
            }
            if(!empty($val)){
                if(!is_array($val))
                    $val = array($val);
                foreach($val as $_v){
                    $Usuario->clear();
                    $Usuario->id_usuario = self::get("id_usuario");
                    $Usuario->var = $var;
                    $Usuario->val = $_v;
                    $Usuario->save();
                }
                return true;
            }    
        }else{
            // Ya hay registro
            if(is_null($val)){
                if($Usuario->rows > 1){
                    $ret = array();
                    while($Usuario->next())
                        $ret[] = $Usuario->val;
                    return $ret;
                }
                $Usuario->next();
                return $Usuario->val;
            }else{
                if($val === ""){
                    // Borramos
                    $db = Db::getInstance();
                    $db->query("DELETE FROM usuario_config WHERE id_usuario = '".self::get("id_usuario")."' AND var = '".$db->escape($var)."'");
                    return true;
                }else{
                    if(!is_array($val))
                        $val = array($val);
                    foreach($val as $_v){
                        // Checamos si ya existe combinacion user/var/val
                        $Usuario->where("id_usuario = {0} AND var = '{1}' AND val = '{2}'",self::get("id_usuario"),$var,$_v)->execute();
                        if($Usuario->rows > 0)
                            continue;
                        $Usuario->clear();
                        $Usuario->id_usuario = self::get("id_usuario");
                        $Usuario->var = $var;
                        $Usuario->val = $_v;
                        $Usuario->save();
                    }
                    return true;
                }
            }
        }
    }
    
    /**
    * Para verificar si el usuario esta loggeado
    * 
    */
    public static function islogged(){
        if(isset($_SESSION[Auth::SES_LOGIN_INDEX][Sys::get('config')->user_username_field]))
            return true;
        return false;
    }
    
    /**
    * Para obtener las variables de configuración de un usuario dado
    * 
    * 
    */
    public static function getConfVars($id_usuario=null){
        if($id_usuario == null){
            return false;
        }
        $UN = new UsuarioNotificacion();
        $UN->select(NULL,"WHERE id_usuario = '".$id_usuario."'");
        if($UN->rows>0){
            $UN->next();
            $vars = $UN->recordset();
        }else
            return false;
        return $vars;
    }
    
    /**
    * Borra los datos de la sesion del usuario
    * 
    */
    public static function wipeSession(){
        unset($_SESSION[Auth::SES_LOGIN_INDEX]);
    }
    
    /**
    * Devuelve el id de sesion
    * 
    */
    public function sesId(){
        if(!self::islogged())
            return false;
        return session_id();
    }
    
    /**
    * Magic get de la sesion actual
    * 
    * @param mixed $key
    * @return mixed
    */
    public function __get($key=NULL){
        if($key=="id")
            $key="id_usuario";
        return self::get($key);
    }
    
    /**
    * Get estatico para la sesion actual
    * 
    * @param mixed $key
    * @return mixed
    */
    public static function get($key=NULL){
        if(empty($key))
            return false;
        return $_SESSION[Auth::SES_LOGIN_INDEX][$key];
    }
    
    public static function getLoginSession(){
        return $_SESSION[Auth::SES_LOGIN_INDEX];  
    }
    
    /**
    * Set estatico para la sesion actual
    * 
    * @param mixed $key
    * @return mixed
    */
    public static function set($key=NULL,$val=NULL){
        if(empty($key))
            return false;
        $_SESSION[Auth::SES_LOGIN_INDEX][$key]=$val;
    }
    
    /**
    * Establece el rol del usuario
    * 
    * @param UserRole $role
    */
    public static function setRole(UserRole $role){
        self::$role=$role;
        $_SESSION[Auth::SES_LOGIN_INDEX]['role_id']=self::$role->getRoleId();
    }
    
    /**
    * Devuelve el id del rol actual del usuario
    * 
    */
    public static function getRoleId(){
        if(empty($_SESSION[Auth::SES_LOGIN_INDEX]['role_id']))
            $_SESSION[Auth::SES_LOGIN_INDEX]['role_id']=UserRole::USER_PUBLIC;
        if(self::$role==NULL){
            self::$role=new UserRole($_SESSION[Auth::SES_LOGIN_INDEX]['role_id']);
        }  
        $_SESSION[Auth::SES_LOGIN_INDEX]['role_id']=self::$role->getRoleId(); 
        return self::$role->getRoleId();
    }
    
    /**
    * Numero de intentos de loggeo
    * 
    */
    public static function getAttempts(){
        if(!empty($_SESSION[Auth::SES_LOGIN_INDEX]['timeout']))
            if(time()-$_SESSION[Auth::SES_LOGIN_INDEX]['timeout'] >= 180)
                self::clearAttempts();
        else{
            if($_SESSION[Auth::SES_LOGIN_INDEX]['tries']>=Auth::LOGIN_ATTEMPTS)
                $_SESSION[Auth::SES_LOGIN_INDEX]['timeout']=time();
        }
        return empty($_SESSION[Auth::SES_LOGIN_INDEX]['tries']) ? 0 : $_SESSION['login']['tries'];
    }
    
    /**
    * Agrega intento de loggeo
    * 
    */
    public static function addTry(){
        if(empty($_SESSION['login']['tries']))
            $_SESSION[Auth::SES_LOGIN_INDEX]['tries']=0;
        $_SESSION[Auth::SES_LOGIN_INDEX]['tries']++;
    }
    
    /**
    * Borra los intentos de loggeo
    * 
    */
    public static function clearAttempts(){
        unset($_SESSION[Auth::SES_LOGIN_INDEX]['timeout']);
        unset($_SESSION[Auth::SES_LOGIN_INDEX]['tries']);
    }
}
