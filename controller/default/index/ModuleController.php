<?php
/**
* Module Controller
* 
* Sets the actions for the current module
* @package App
*/
class ModuleController extends Controller{
    public function __construct(){

    }
    
    public function main(){
        $this->title(APP_NAME);

        $this->load->view("index.html",get_defined_vars());
    }

}