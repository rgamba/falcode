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
class ThisUser{
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
    
    public function config($var,$val=NULL,$id_user = NULL){
        return self::configuration($var,$val,$id_user);
    }
    
    public static function getPic(){
        if(!self::islogged())
            return 'avatar.gif';
        $id_admin = User::find(self::get('id_user'))->getTeamAdmin();
        if($id_admin != false){
            if(self::configuration('company_logo',NULL,$id_admin)){
                return self::configuration('company_logo',NULL,$id_admin);
            }else{
                return 'avatar.gif';
            }
        }

        if(self::configuration('company_logo')){
            return self::configuration('company_logo');
        }else{
            return 'avatar.gif';
        }
    }
    
    public static function configuration($var,$val=NULL, $id_user = NULL){
        if(is_null($id_user))
            $id_user = self::get("id_user");
        $User = new UserConfig();
        $User->where("id_user = {0} AND var = '{1}'",$id_user,$var)->execute();
        if($User->rows <= 0){
            // No hay registro
            if(is_null($val)){
                // Lectura
                return NULL;
            }
            if(!empty($val)){
                // Escritura
                if(!is_array($val))
                    $val = array($val);
                foreach($val as $_v){
                    $User->clear();
                    $User->id_user = $id_user;
                    $User->var = $var;
                    $User->val = $_v;
                    $User->save();
                }
                return true;
            }    
        }else{
            // Ya hay registro
            if(is_null($val)){
                // Solo lectura
                if($User->rows > 1){
                    $ret = array();
                    while($User->next())
                        $ret[] = $User->val;
                    return $ret;
                }
                $User->next();
                return $User->val;
            }else{
                // Escritura
                $db = Db::getInstance();
                $db->query("DELETE FROM user_config WHERE id_user = '".$id_user."' AND var = '".$db->escape($var)."'");
                if($val === ""){
                    // Borramos
                    return true;
                }else{
                    // Update
                    if(!is_array($val))
                        $val = array($val);
                    foreach($val as $_v){
                        // Checamos si ya existe combinacion user/var/val
                        $User->where("id_user = {0} AND var = '{1}' AND val = '{2}'",$id_user,$var,$_v)->execute();
                        if($User->rows > 0)
                            continue;
                        $User->clear();
                        $User->id_user = $id_user;
                        $User->var = $var;
                        $User->val = $_v;
                        $User->save();
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
        $UN->select(NULL,"WHERE id_user = '".$id_usuario."'");
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
            $key="id_user";
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
        return @$_SESSION[Auth::SES_LOGIN_INDEX][$key];
    }
    
    public static function getLoginSession(){
        return empty($_SESSION[Auth::SES_LOGIN_INDEX]) ? array() : $_SESSION[Auth::SES_LOGIN_INDEX];
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
