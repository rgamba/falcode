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
        $this->load->view("index.php",get_defined_vars());
    }
}