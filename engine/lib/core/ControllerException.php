<?php
class ControllerException extends Exception{
    const MODULE_NOT_FOUND=1;
    const ACTION_NOT_FOUND=2;
    const ACCESS_DENIED=3;
    const UNDER_MAINTENANCE=4;
    const CUSTOM_ERROR=5;
    const UNKNOWN=6;
    const VALIDATION=7;
    
    public function __construct($message,$code=0,Exception $previous=NULL){
        parent::__construct($message,$code,$previous);
    }
    
    public function __toString(){
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}