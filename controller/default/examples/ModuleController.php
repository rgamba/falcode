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
}