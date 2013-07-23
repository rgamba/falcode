<?php
class Session{
    private $db;

    public static function start(){
        //session_name(Sys::get("config")->session_name);
        ini_set('session.hash_function',1); // SHA1 algorithm
        ini_set('session.gc_maxlifetime', Sys::get("config")->session_expire);
        ini_set('session.cookie_domain',Sys::get("config")->session_domain);
        session_set_cookie_params(0, '/', Sys::get("config")->session_domain);

        session_start();

        if(Sys::get("config")->session_save_on_db == true){
            $Session = new Session();
            session_set_save_handler(
                array($Session, 'open'),
                array($Session, 'close'),
                array($Session, 'read'),
                array($Session, 'write'),
                array($Session, 'destroy'),
                array($Session, 'gc')
            );
            register_shutdown_function('session_write_close');

        }
        session_start();
    }

    public function __construct(){
        $this->db = Db::getInstance();
    }

    public function open($save_path,$session_name){
        return true;
    }

    public function close(){
        // Pass
    }

    public function read($id){
        $query = $this->db->fetch("SELECT data FROM session WHERE session_id = '".$this->db->escape($id)."' AND ip = '".$this->db->escape($_SERVER['REMOTE_ADDR'])."'");
        if($query->num_rows > 0){
            return $query->row['data'];
        }
        return "";
    }

    public function write($id,$data){
        $this->db->query("REPLACE INTO session (session_id,ip,user_agent,last_activity,data) VALUES(
        '".$this->db->escape($id)."',
        '".$this->db->escape($_SERVER['REMOTE_ADDR'])."',
        '".$this->db->escape($_SERVER['HTTP_USER_AGENT'])."',
        '".date('U', time() - date('Z', time()))."',
        '".$this->db->escape($data)."'
        )");
        return true;
    }

    public function destroy($id){
        $this->db->query("DELETE FROM session WHERE session_id = '".$this->db->escape($id)."'");
        return true;
    }

    public function gc($max){
        $max = date('U', time() - date('Z', $max)); // Max in GMT as stored in db
        $old = date('U', time() - date('Z', time())) - $max;
        $this->db->query("DELETE FROM session WHERE last_activity < '$old'");
        return true;
    }
}
