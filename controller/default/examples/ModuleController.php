<?php
class ModuleController extends Controller{
    public function __construct(){

    }

    public function main(){
        $this->load->view("index.php");
    }

    public function simple_view(){
        $data['test'] = "Hello world!";
        $data['name'] = "Richard";

        $this->load->view("simple_view.php",$data);
    }

    public function simple_view_tpl(){
        $data['test'] = "Hello world!";
        $data['name'] = "Richard";

        $this->load->view("simple_view.html",$data);
    }

    public function complex_view(){
        // Simple array
        $colors = array('green','blue','red');

        // 2nd dimensional associative array
        $contacts = array(
            array(
                'name' => 'Richard Gamba',
                'phone' => '56784512'
            ),
            array(
                'name' => 'Chuck Liddel',
                'phone' => '56784512'
            ),
            array(
                'name' => 'Anderson Silva',
                'phone' => '56895623'
            )
        );

        $this->load->view("complex_views.php",get_defined_vars());
    }

    public function complex_view_tpl(){
        // Simple array
        $colors = array('green','blue','red');

        // 2nd dimensional associative array
        $contacts = array(
            array(
                'name' => 'Richard Gamba',
                'phone' => '56784512'
            ),
            array(
                'name' => 'Chuck Liddel',
                'phone' => '56784512'
            ),
            array(
                'name' => 'Anderson Silva',
                'phone' => '56895623'
            )
        );

        $this->load->view("complex_views.html",get_defined_vars());
    }

    public function dynamic_includes(){
        $test = "Hello world!";
        $this->load->view("dynamic_includes.php",get_defined_vars());
    }

    public function dynamic_includes_tpl(){
        $test = "Hello world!";
        $this->load->view("dynamic_includes.html",get_defined_vars());
    }

    public function uri_elements($id,$year=NULL,$month=NULL,$file=NULL){
        /**
         * Other ways to get the URL paths:
         * To get the $year var you could use:
         * Router::$path[0];
         * To get the $month var you could use:
         * Router::$path[1]
         */

        $this->load->view("uri_elements.html",get_defined_vars());
    }

    public function errors(){
        // This authorization system is obviously very insecure, it's only for example purpuse
        if($_GET['token'] != '123'){
            // Once the exception is thrown the execution in this controller is stopped
            // and the ErrorController's access denied function is executed
            $this->throwAccessDenied("Access denied!"); // No code below this line will be executed
            // Which is the same as throwing the exception directly
            //throw new ControllerException("Access denied!",ControllerException::ACCESS_DENIED);
        }

        echo "Welcome to the secure area!";
    }

    public function template_layout(){
        $this->load->template("mobile");
        $this->load->layout("layout.php"); // Load the PHP-based layout (Not the template engine-based)
        $this->load->view("template_layout.php");
    }

    public function models(){
        // Create an instance of the model class
        $User = new User();
        // Make some queries
        $User->where("username = '{0}'",$_REQUEST['user'])->execute();
        // Check if it was found
        if($User->rows <= 0)
            $this->throwCustomError("User not found","The username you requested does not exist");
        // Else, select the user
        $User->next();
        echo "Username: " . $User->username . "<br>Name: " . $User->name;
    }

    public function extensions(){
        // The line below would give an error as SimpleCaptcha is part of cool_captcha
        // extension which has not been loaded yet...
        //$captcha = new SimpleCaptcha();
        // Load the helper. Include the extension just if the helper file is located directly in the
        // extensions root folder. Otherwise use the directory name inside extensions folder
        $this->load->extension("cool_captcha");
        $captcha = new SimpleCaptcha();
        $captcha->resourcesPath = PATH_EXTENSIONS."cool_captcha/resources";
        $captcha->wordsFile = 'words/en.php';
        $captcha->blur = false;
        $captcha->CreateImage();
        // Set the response content type header...
        $this->response->header("Content-type: image/jpeg");
        $this->blank();
    }

    public function helpers(){
        // Load the helper
        $this->load->helper("date");
        // Now you can use the functions inside the helper
        echo friendly_date(time()-200000);
    }

    public function modifiers(){
        $name = "john doe";
        $now_gmt = now_gmt(); // To get the GMT time, Same as date('Y-m-d H:i:s',time_gmt());
        $data = array(
            'name' => "john doe",
            'phone' => "56784552",
            'bday' => "1987-08-21"
        );

        $this->load->view("modifiers.html",get_defined_vars());
    }

    public function advanced_tpl(){
        $age = 25;
        $user_role = "member";
        $username = "admin";

        $this->load->view("advanced_tpl.html",get_defined_vars());
    }

    public function images(){
        $this->load->view("images.html",get_defined_vars());
    }

    public function validation(){

        $this->load->view("validation.html");
    }

    public function validate_form(){
        // Begin validation rules
        $this->request->post("email",true)
            ->required()
            ->email("Enter a valid email address")
            ->errorMsg("Enter your email address");
        $this->request->post("password",true)
            ->required()
            ->minLength(6,"Your password must be at least 6 characters long")
            ->errorMsg("Enter your password");
        $this->request->validate();

        if($this->request->isAjax()){
            $this->response->isJSON();
            echo json_encode(array(
                'result' => 'ok',
                'message' => 'Your data has been received'
            ));
        }else{
            echo "Your data has been received";
        }
    }

    public function javascript(){
        // This is the correct way of including a JS file INSIDE de HEAD of the layout
        $this->load->js("example.js");

        $this->load->view("javascript.html",get_defined_vars());
    }

    public function ajax(){
        $this->load->js("example_ajax.js");
        $this->load->view("ajax.html");
    }

    public function ajax_request(){
        // First we tell falcode that we are going to send a json output format
        $this->response->isJSON();
        // Which is the same as:
        //$this->response->setHeader("Content-Type: application/json");
        //$this->blank(); // This uses a blank layout named _blank.html
        $resp = array(
            array(
                'name' => 'John Doe',
                'phone' => '55748854'
            ),
            array(
                'name' => 'Ricky Gamba',
                'phone' => '45784512'
            ),
            array(
                'name' => 'Peter Pan',
                'phone' => '74857412'
            )
        );

        // Send output
        echo json_encode($resp);
    }

    public function createTable(){
        $db = Db::getInstance();
        $db->modifyField("contacts",array(
            'name' => 'id_contact',
            'type' => 'int',
            'size' => '10',
            'auto_increment' => true
        ));
        $db->addKey("contacts","id_contact","PRIMARY KEY");

        echo $db->getError();
    }
}