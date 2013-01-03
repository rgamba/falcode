<?php
/**~controller/default/{table}/ModuleController.php
* 
* ModuleController
* ---
* 
* @package      FALCODE
* @version      3.0
* @author       $Autor$
* @modified     $Fecha$
* 
*/
class ModuleController extends Controller{
    public function __construct(){
        // Default action
        $this->setDefaultAction('listAction');
    }
    
    /**
    * Listing
    * 
    */
    public function listAction(){
        $this->title('Listado de {table}');
        include('dsp_list.php');
    }
    
    /**
    * Add / Edit
    * 
    */
    public function add(){
        $this->edit();
    }
    public function edit(){
        include('dsp_ae.php');
    }
    
    /**
    * View
    * 
    */
    public function view(){
        $this->title('Ver {table}');
        include('dsp_view.php');
    }
    
    /**
    * Save
    * 
    */
    public function save(){
        $this->blank();
        include('act_save.php');
    }
    
    /**
    * Delete
    * 
    */
    public function del(){
        $this->blank();
        include('act_del.php');
    }
    
    /**
    * [Delete image]
    * 
    */
    public function del_img(){
        $this->blank();
        include('act_del_img.php');
    }
    
}

