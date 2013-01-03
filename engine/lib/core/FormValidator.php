<?php
class FormValidator{
    private $fields=array();
    private $errors=array();
    
    public function &add($name,$alias){
        $this->fields[]=new FormField($name,$alias);
        return $this->fields[count($this->fields)-1];
    }
    
    public function validate(){
        $errors=array();
        if(!empty($this->fields)){
            foreach($fields as $field){
                if(!$field->validate()){
                    $errors[]=$field->getErrors();
                }
            }
        }
        if(count($errors)<=0)
            return true;
        $this->errors=$errors;
        return false;
    }
    
    public function getErrors(){
        return $this->errors;
    }
}

class FormField{
    private $name;
    private $alias;
    private $rules=array();
    private $errors=array();
    
    public function __construct($name,$alias){
        $this->name=$name;
        $this->alias=$alias;
    }
    
    public function getErrors(){
        return $this->errors;
    }
    
    public function required($error=NULL){
        $this->rules[]=array("required",$error);
        return $this;
    }
    
    public function email($error){
        $this->rules[]=array("email",$error);
        return $this;
    }
    
    public function minLength($length,$error=NULL){
        $this->rules[]=array("min",$error,$length);
        return $this;
    }
    
    public function maxLength($length,$error=NULL){
        $this->rules[]=array("max",$error,$length);
        return $this;
    }
    
    public function numeric($error=NULL){
        $this->rules[]=array("number",$error);
        return $this;
    }
    
    public function minVal($length,$error=NULL){
        $this->rules[]=array("min_val",$error,$length);
        return $this;
    }
    
    public function maxVal($length,$error=NULL){
        $this->rules[]=array("max_val",$error,$length);
        return $this;
    }
    
    public function regex($regex,$error=NULL){
        $this->rules[]=array("regex",$error,$regex);
        return $this;
    }
    
    public function validate($post){
        $value=$post[$this->name];
        $errors=array();
        if(!empty($this->rules)){
            foreach($this->rules as $rule){
                switch($rule[0]){
                    case 'required':
                        if(trim($value)==""){
                            $errors[]=empty($rule[1]) ? "The field {$this->alias} is required" :$rule[1];
                        }
                        break;
                    case 'email':
                        $mail_pat='/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
                        if(!preg_match($mail_pat,$value)){
                            $errors[]=empty($rule[1]) ? "The field {$this->alias} must be a valid email address" :$rule[1];
                        }
                        break;
                    case 'min_length':
                        if(strlen(trim($value))<=$rule[2]){
                            $errors[]=empty($rule[1]) ? "The field {$this->alias} must be at least $rule[2] characters long" :$rule[1];
                        }
                        break;
                    case 'max_length':
                        if(strlen(trim($value))>$rule[2]){
                            $errors[]=empty($rule[1]) ? "The field {$this->alias} must be at the most $rule[2] characters long" :$rule[1];
                        }
                        break;
                    case 'min_val':
                        if(intval($value)<=$rule[2]){
                            $errors[]=empty($rule[1]) ? "The field {$this->alias} must be equal or bigger than $rule[2]" :$rule[1];
                        }
                        break;
                    case 'max_val':
                        if(intval($value)>$rule[2]){
                            $errors[]=empty($rule[1]) ? "The field {$this->alias} must smaller than $rule[2]" :$rule[1];
                        }
                        break;
                    case 'regex':
                        if(!preg_match($rule[2],$value)){
                            $errors[]=empty($rule[1]) ? "The field {$this->alias} has a wrong format" :$rule[1];
                        }
                        break;
                }
            }
        }
        if(count($errors)>0){
            $this->errors=$errors;
            return false;
        }
        return true;
    }
}