<?php
/**~engine/lib/UserRole.php
* ---
* UserRole 
* ---
* User Role control
* 
* @package      FALCODE
* @package      Multi-user System Add-on
* @version      3.0
* @author       FALCODE
*/
class UserRole{
    /**
    * ID unico de rol
    * 
    * @var mixed
    */
    protected $roleId=NULL;
    const USER_PUBLIC="public";
    const USER_SUPER_ADMIN="super_admin";
    
    /**
    * Constructora
    * 
    * @param mixed $roleId
    * @return UserRole
    */
    public function __construct($roleId){
        $this->roleId=$roleId;
    }
    
    /**
    * Devuelve el Id de rol del objecto actual
    * 
    */
    public function getRoleId(){
        return $this->roleId;
    }
}