<?php
class RequestVar{
    public $value;
    public $error;
    public $index;
    private $rules = array();

    public function __construct($index,$value){
        $this->index = $index;
        $this->value = $value;
    }

    public function errorMsg($err){
        $this->error = $err;
        return $this;
    }

    public function required(){
        $this->rules[] = array(
            'rule' => 'required'
        );
        return $this;
    }

    public function unique($table,$column,$except = NULL,$msg = ""){
        $db = Db::getInstance();
        $this->rules[] = array(
            'rule' => 'unique',
            'sql' => "SELECT * FROM ".$db->escape($table)." WHERE ".$db->escape($column)." = '".$db->escape($this->value)."'".(!empty($except) ? " AND ".$db->escape($column)." != '".$db->escape($except)."'" : ""),
            'msg' => $msg
        );
        return $this;
    }

    public function minLength($c,$msg=""){
        $this->rules[] = array(
            'rule' => 'min_length',
            'val' => $c,
            'msg' => $msg
        );
        return $this;
    }

    public function maxLength($c,$msg = ""){
        $this->rules[] = array(
            'rule' => 'max_length',
            'val' => $c,
            'msg' => $msg
        );
        return $this;
    }

    public function lessThan($c,$msg = ""){
        $this->rules[] = array(
            'rule' => 'less_than',
            'val' => $c,
            'msg' => $msg
        );
        return $this;
    }

    public function greaterThan($c,$msg = ""){
        $this->rules[] = array(
            'rule' => 'greater_than',
            'val' => $c,
            'msg' => $msg
        );
        return $this;
    }

    public function notEmpty(){
        $this->rules[] = array(
            'rule' => 'empty'
        );
        return $this;
    }

    public function numeric($msg = ""){
        $this->rules[] = array(
            'rule' => 'numeric',
            'msg' => $msg
        );
        return $this;
    }

    public function email($msg = ""){
        $this->rules[] = array(
            'rule' => 'email',
            'msg' => $msg
        );
        return $this;
    }

    public function equals($value,$msg = ""){
        $this->rules[] = array(
            'rule' => 'equals',
            'val' => $value,
            'msg' => $msg
        );
        return $this;
    }

    public function regex($regex,$msg = ""){
        $this->rules[] = array(
            'rule' => 'regex',
            'val' => $regex,
            'msg' => $msg
        );
        return $this;
    }

    public function func($fname,$msg = ""){
        $this->rules[] = array(
            'rule' => 'function',
            'val' => $fname,
            'msg' => $msg
        );
        return $this;
    }

    public function val(){
        return $this->value;
    }

    public function validate(){
        $db = Db::getInstance();
        foreach($this->rules as $rule){
            if(!is_array($this->value))
                $this->value = array($this->value);
            foreach($this->value as $v){
                switch($rule['rule']){
                    case 'required':
                        if($v == "" || !isset($v))
                            return false;
                        break;
                    case 'empty':
                        if(empty($this->value))
                            return false;
                        break;
                    case 'unique':
                        $query = $db->query($rule['sql']);
                        if($db->numRows($query) > 0){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'equals':
                        if($v != $rule['val']){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'min_length':
                        if(strlen($v) < $rule['val']){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'max_length':
                        if(strlen($v) > $rule['val']){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'less_than':
                        if($v > $rule['val']){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'greater_than':
                        if($v < $rule['val']){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'numeric':
                        if(!is_numeric($v)){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'email':
                        if(preg_match("/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/",$v) == 0){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'regex':
                        if(preg_match($rule['val'],$v) == 0){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                    case 'function':
                        $ret = call_user_func($rule['val'],$v);
                        if(!$ret){
                            if(!empty($rule['msg']))
                                $this->error = $rule['msg'];
                            return false;
                        }
                        break;
                }
            }
        }
        return true;
    }
}