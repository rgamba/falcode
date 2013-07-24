<?php
/**~engine/lib/Auth.php
*
* Auth (Singleton - No instanciable)
* ---
* Registro y validacion del usuario en el sistema
*
* @package      FALCODE
* @package      Multi-user System Add-on
* @version      2.0
* @author       FALCODE
*/
class Auth{
    const SES_LOGIN_INDEX='login'; // Indice en $_SESSION donde se guarda el registro del login
    const SES_REGISTER_TABLE_FIELDS=1; // Indica si se registrarán todos los campos de la tabla de usuario
    const COOKIE_NAME='login';
    
    
    /**
    * Intenta crear el login del usuario
    * 
    * @param mixed $post $_POST
    */ 
    public static function attemptLogin($post=NULL){
        if(ThisUser::islogged())
            return true;
        if(is_null($post))
            $post=$_POST;
            
        // Verificamos login por cookies
        if(Sys::get('config')->login_set_cookie==true && empty($post) && self::hasCookie()){
            if(self::hasCookie()){
                $U=new User();
                $U->select(NULL,"WHERE SHA1(CONCAT(".Sys::get('config')->user_username_field.",".Sys::get('config')->user_password_field.")) = '".Sys::$Db->escape($_COOKIE[self::COOKIE_NAME])."'");
                if($U->rows>0){
                    $cookie=$U->next();
                    $post[Sys::get('config')->user_username_field]=$cookie[Sys::get('config')->user_username_field];
                    $post[Sys::get('config')->user_password_field]=$cookie[Sys::get('config')->user_password_field];   
                }
            }
        }

        if(ThisUser::islogged())
            return true;
        
        if(empty($post))
            return false;

        if(ThisUser::getAttempts()>=Sys::get('config')->login_attempts){
            self::throwException(Sys::get('config')->login_max_tries);
            return false;
        }
        
        if(Sys::get('config')->login_encode_pass==true){
            $post[Sys::get('config')->user_password_field]=sha1($post[Sys::get('config')->user_password_field]);     
        }

        $query=Sys::get('config')->user_login_query;
        $query=str_replace(array('{0}','{1}'),array($post[Sys::get('config')->user_username_field],$post[Sys::get('config')->user_password_field]),$query);

        $pass=@Sys::get('db')->query($query);

        if(@Sys::$Db->numRows()<=0){
            ThisUser::addTry();
            self::throwException(Sys::get('config')->login_error_msg);
            return false;
        }

        if(self::SES_REGISTER_TABLE_FIELDS==1)
            self::registerVars($pass);
        ThisUser::clearAttempts();
        if(!empty($post['set_cookie']) && Sys::get('config')->login_set_cookie==true){
            self::setCookie($post[Sys::get('config')->user_username_field],$post[Sys::get('config')->user_password_field]);
        }else{
        }
        return true;
    }
    
    private static function setCookie($user,$pass){
        setcookie(self::COOKIE_NAME,sha1($user.$pass),time()+60*60*24*Sys::get('config')->login_cookie_expire_days,'/');
    }
    
    private static function deleteCookie(){
        if(self::hasCookie()){
            setcookie(self::COOKIE_NAME,"disposed",time()-1000,'/');
        }
    }
    
    public static function hasCookie(){
        return !empty($_COOKIE[self::COOKIE_NAME]);
    }
    
    /**
    * Registra las variables de la tabla de usuario a la sesión actual
    * 
    * @param mixed $qry Handler del query
    */
    private static function registerVars($qry){
        $arr=@Sys::$Db->getRow($qry);
        foreach($arr as $i => $val)
            ThisUser::set($i,$val);
        ThisUser::setRole(new UserRole($arr[Sys::get('config')->user_lvl_field]));
        return true;
    }
    
    /**
    * Finaliza la sesion del usuario actual
    * 
    */
    public static function logout(){
        ThisUser::wipeSession();
        self::deleteCookie();
        return true;
    }
    
    /**
    * Envia error para ser atrapado mediante un try catch
    * 
    * @param mixed $err
    */
    private static function throwException($err){ 
        //$name_cookie = str_replace(" ", "_",APP_NAME."_login");
        //setCookie($name_cookie);
        throw new Exception($err);
    }

    /**
     * Login with facebook
     * @return bool
     */
    public function initFb(){
        Sys::get('loader')->extension('facebook');
        Sys::set('fb', new Facebook(array(
            'appId'  => Sys::get('config')->fb_app_id,
            'secret' => Sys::get('config')->fb_secret,
        )));
        
        // Get User ID
        $user = Sys::get('fb')->getUser();

        // We may or may not have this data based on whether the user is logged in.
        //
        // If we have a $user id here, it means we know the user is logged into
        // Facebook, but we don't know if the access token is valid. An access
        // token is invalid if the user logged out of Facebook.

        if ($user) {
          try {
            // Proceed knowing you have a logged in user who's authenticated.
            $user_info = Sys::get('fb')->api('/me');
            // Vemos si ya existe el usuario
            $Usuario = new User();
            $Usuario->where(Sys::get('config')->user_username_field." = '".Sys::get('db')->escape($user_info['email'])."'")->execute();
            if($Usuario->rows > 0){
                // La direccion de email ya esta registrada
                $u = $Usuario->next();
                if(!empty($u['fb_id'])){
                    // Ya se ha loggeado con fb antes... iniciamos sesion
                    Auth::attemptLogin($u);
                }else{
                    // El mail ya existe en la base pero el usuario no esta vinculado con fb...
                    if(empty($u['activo']))
                        $Usuario->activo = 1;
                    $Usuario->save();
                    Auth::attemptLogin($u);
                }
            }else{
                // El usuario no esta registrado en la base pero ya nos dio acceso por fb
                // Registramos el nuevo usuario
                $Usuario->clear();
                $Usuario->populate(array(
                    'id_user_role' => 4,
                    'active' => 1,
                    'activation' => 'fb'.uniqid(),
                    'date_creation' => 'now()',
                    'name' => $user_info['first_name'],
                    'last_name' => $user_info['last_name'],
                    'username' => $user_info['email'],
                    'pass' => $user_info['id'],
                    'email' => $user_info['email'],
                    'fb_id' => $user_info['id']
                ))->save();
                $id_usuario = Sys::get('db')->lastId();
                Sys::get("loader")->helper("uri");
        
                Auth::attemptLogin(array(
                    'username' => $user_info['email'],
                    'pass' => $user_info['id']
                ));
            }
            $_SESSION['login']['fb'] = true;
            $_SESSION['login']['fb_logout'] = Sys::get('fb')->getLogoutUrl(array(
              'next' => url('login')
            ));
            Sys::set('fb_user',$user_info);
            return true;
          } catch (FacebookApiException $e) {
            return false;
          }catch(Exception $e){
              session_destroy();
          }
        }
    }

}
