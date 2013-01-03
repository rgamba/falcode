<?php
/**
* User join
* 
* @var Usuario
*/
$this->load->helper('string');

// Input validation rules
$this->request->post("nombre",true)->required()->errorMsg("Ingresa tu nombre");
$this->request->post("apellidos",true)->required()->errorMsg("Ingresa tus apellidos");
$this->request->post("pass",true)->required()->minLength(4,"Tu contraseña debe ser mayor a 4 letras")->errorMsg("Ingresa una contraseña");
$this->request->post("email",true)->email()->unique("usuario","email",NULL,"El correo ya está registrado")->errorMsg("Ingresa un email válido");
$this->request->validate();

$_POST['fecha_creacion'] = "now()";
$_POST['activo'] = 1;
$_POST['usuario'] = $_POST['email'];
$_POST['id_usuario_rol'] = 2;

// Check captcha
/*if($_SESSION['captcha'] != $_POST['captcha']){
    $this->system->setError($this->lang->invalid_captcha);
    redirect(url('login'),false,true);
    die();
}*/

$Usuario = new Usuario();

// Save user
$Usuario->clear();
$Usuario->populate($_POST);
$Usuario->save();
$id_usuario = $this->db->lastId();

// Send confirmation email
ob_start();
$Mail = new Mail();
$Mail->subject("Bienvenido a ".APP_NAME);
$Mail->to($this->request->post('email'),"Usuario");
$Mail->from($this->config->mail_from,APP_NAME);
$Mail->renderBody("welcome.html",get_defined_vars());
$Mail->send();
ob_get_clean();

// Log the user
Auth::attemptLogin($_POST);
$this->updateTimezone();

// Response
if($this->request->isAjax()){
    $this->response->isJSON();
    $this->system->setInfo("Hemos creado tu cuenta. ¡Bienvenido a academye!");
    echo json_encode(array(
        'result' => 'ok',
        'redirect' => url('index')
    ));
}else{
    redirect(url('index'));
}