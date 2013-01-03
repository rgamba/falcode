<?php
/**
 * AuthToken.php
 * ---
 *
 * Generates Apache Mod-Auth-Token URIs
 * @url http://code.google.com/p/mod-auth-token/
 * @author Ricardo Gamba <rgamba@gmail.com>
 */
class AuthToken{
    public $secret = "";
    public $path = "";
    public $file = "";
    public $prefix = "";

    /**
     * @param $secret Secret key as established in AuthTokenSecret
     * @param $path Same as AuthTokenPrefix
     * @param $file File name without leading "/"
     * @param string [$prefix] If set, it will preped this to the URI
     */
    public function __construct($secret,$path,$file,$prefix=""){
        $this->secret = $secret;
        $this->path= $path;
        $this->file = $file;
        $this->prefix = $prefix;
    }

    public function getUri($file=""){
        if(!empty($file))
            $this->file = $file;
        $hexTime = dechex(time());
        $token = md5($this->secret . "/".$this->file . $hexTime);
        if(substr($this->path,-1) != "/")
            $this->path .= "/";
        return $this->prefix . $this->path . $token . "/" . $hexTime . "/" . $this->file;
    }
}