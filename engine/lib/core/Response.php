<?php
class Response{
    private $headers = array();
    private $output = "";
    private static $instance = NULL;
    
    private function __construct(){
        
    }
    
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = new Response();
        return self::$instance;
    } 
    
    /**
    * Set output header
    * If the header was previously sent, it will be replaced
    * 
    * @param mixed $header
    */
    public function setHeader($header){
        list($k,$v) = explode(':',$header,2);
        foreach($this->headers as $_k => $_v){
            if(trim(strtolower($_k)) == trim(strtolower($k))){
                $k = $_k;
                break;
            }
        }
        $this->headers[trim($k)] = trim($v);
    }
    
    /**
    * Determine if the response is JSON format
    * 
    */
    public function isJSON(){
        Sys::get('module_controller')->blank(true);
        $this->setHeader("Content-Type: application/json");
    }

    public function isBlank(){
        Sys::get('module_controller')->blank(true);
    }
    
    /**
    * Get all headers (note that they're not sent yet!)
    * 
    */
    public function getHeaders(){
        return $this->headers;
    }
    
    /**
    * Append output
    * 
    * @param mixed $output
    */
    public function setOutput($output){
        $this->output .= $output;
    }
    
    /**
    * Get the output buffer
    * 
    */
    public function getOutput(){
        return $this->output;
    }
    
    /**
    * Send output headers (do not use manually unless needed)
    * 
    */
    public function sendHeaders(){
        if(headers_sent())
            die("Response::send() - Headers already sent!");
        if(!empty($this->headers)){
            foreach($this->headers as $h => $v)
                header($h.": ".$v);
        }
    }
    
    /**
    * Send headers and send output
    * 
    */
    public function sendOutput(){
        $this->sendHeaders();
        echo $this->getOutput();
    }
}
