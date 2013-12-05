<?php

class ErrorController extends Controller{
    public function __construct(){
        // Set default layout for all errors

    }

    public function ModuleNotFound($message,$module_requested){
        $this->title(Lang::get('page_not_found'));
        $this->load->view("generic");
        $this->template->header=Lang::get('module_not_found');
        $this->template->message=Lang::get('module_not_found_msg',$module_requested);
    }

    public function ActionNotFound($message,$action_requested){
        $this->title(Lang::get('page_not_found'));
        $this->load->view("generic");
        $this->template->header=Lang::get('section_not_found');
        $this->template->message=Lang::get('section_not_found_msg');
    }

    public function AccessDenied($message){
        $this->title(Lang::get('access_denied'));
        $this->load->view("generic");
        $this->template->header=Lang::get('access_denied');
        $this->template->message=Lang::get('access_denied_msg');
        $this->template->show_login=true;
        if($this->isAjaxRequest()){
            die(json_encode(array(
                'result' => 'error',
                'sys_error' => 'access_denied',
                'msg' => $this->lang->access_denied_msg
            )));
        }else{
            // Set session fwd location
            if(!ThisUser::islogged()){
                $_SESSION['_login_fwd_'] = array(
                    'module' => DSP_MODULE,
                    'control' => DSP_CONTROL,
                    'get' => $_GET,
                    'post' => $_POST
                );
            }
        }
    }

    public function Validation($arr){


        $fields = unserialize($arr);
        $err = array(
            'result' => 'error',
            'sys_error' => 'validation',
            'fields' => $fields,
            'context' => @$_REQUEST['context'],
            'desc' => $this->system->getError()
        );

        Sys::clearErrorMsg();
        if($this->isAjaxRequest()){
            die(json_encode($err));
        }else{
            $this->system->setFlash('json',$err);

            if(Sys::get('redirect_on_error')){

                redirect(Sys::get('redirect_on_error'),false,true);
            }else{

                redirect($_SERVER['HTTP_REFERER'],false,true);
            }
            die();
        }
    }

    public function UnderMaintenance($message){
        $this->title(Lang::get('under_maintenance'));
        $this->load->view("generic");
        $this->template->header=Lang::get('under_maintenance');
        $this->template->message=Lang::get('under_maintenance_msg').": $message.";
    }

    public function CustomError($header,$message){
        $this->title(Lang::get('unknown_error'));
        $this->load->view("generic");
        $this->template->header=$header;
        $this->template->message=$message;
    }

    public function UnknownError($message){
        $this->title(Lang::get('unknown_error'));
        $this->load->view("generic");
        $this->template->header=Lang::get('unknown_error');
        $this->template->message=Lang::get('unknown_error').": $message";
    }
}
