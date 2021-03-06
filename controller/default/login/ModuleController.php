<?php
class ModuleController extends Controller{
    public function __construct(){
        $this->setDefaultAction('login');
    }
    
    /**
    * Login form
    * 
    */
    public function login(){
        if($this->request->isAjax())
            $this->blank();
        $this->load->view($this->request->isAjax() ? "dsp_login_ajax" : "dsp_login",get_defined_vars());
        $this->render();
    }
    
    /**
    * Login check
    * 
    */
    public function check(){
        $this->blank(true);
        // Validation rules
        $this->request->post("username",true)->required()->errorMsg("Enter your username");
        $this->request->post("pass",true)->required()->errorMsg("Enter your password");
        $this->request->validate();

        try{
            if(Auth::attemptLogin($_POST)){
                $db = Db::getInstance();

                $this->updateTimezone();
                if($this->request->isAjax()){
                    $this->response->isJSON();
                    echo json_encode(array(
                        'result' => 'ok',
                        'redirect' => $_SERVER['HTTP_REFERER']
                    ));
                }else{
                    redirect(url('index'));
                }
            }
        }catch(Exception $e){
            $this->system->setError($e->getMessage());
            $this->throwValidationError();
        }
    }

    public function deepLogout(){
        Auth::logout();
        session_destroy();

    }
    
    /**
    * Logout
    * 
    */
    public function logout(){
        $this->blank(true);
        $ln = $_SESSION['login'];
        Auth::logout();
        if($ln['fb'] == true){
            session_destroy();
            redirect($ln['fb_logout']);
        }else{
            redirect(url('login'));
        }
    }
    
    /**
    * Join form save
    * 
    */
    public function join_check(){
        $this->blank(true);
        include("act_join.php");
    }

    /**
     * Password retrieve
     */
    public function recover(){
        $this->blank(true);
        $this->load->helper("email");
        $email=$_REQUEST['email'];
        $response=array();
        $Usuario=new Usuario();
        $Usuario->select(NULL,"WHERE email = '$email'");
        if($Usuario->rows>0){
            $u=$Usuario->next();
            $msg="Your password is: $u[pass]\n\rLoin here: \n\r".url('login/login')."\n\r---\n\r".APP_NAME;
            send_mail("no-reply@yourapp.com",APP_NAME,$email,"Your password",$msg,false);
            Sys::setInfoMsg("You password has been sent to your email");
            redirect(url('login'));
        }else{
            Sys::setErrorMsg("Invalid email address");
            redirect(url('login/forgot'));
        }
    }

    /**
     * Not accesible from URL
     */
    private function updateTimezone(){
        if(isset($_POST['tz_offset'])){
            $tz = timezone_name_from_abbr("",$_POST['tz_offset'],1);
            if($tz != false){
                $U = Usuario::find_by_id(User::get("id_usuario"));
                $U->next();
                $U->timezone = $tz;
                $U->save();
                User::set("timezone",$tz);
            }
        }
    }

} 
