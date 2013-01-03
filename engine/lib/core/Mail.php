<?php
/**~engine/lib/Mail.php
* 
* Mail Sender
* ---
* 
* @package      FALCODE
* @version      2.0
* @author       FALCODE
* @uses         PHPMailer Package
*/
require_once(PATH_EXTENSIONS.'phpmailer/class.phpmailer.php');
require_once(PATH_EXTENSIONS.'phpmailer/class.smtp.php');

class Mail{
    /**
    * Default SMTP connection credentials
    * Defined on ./engine/conf/config.php
    * 
    * @var mixed
    */
    static $user;
    static $pass;
    static $host;
    static $port=25;
    static $debug=false;
    static $from;
    
    private $params=array();
    private $con=array();
    private $Mailer;
    private $tpl;
    
    public function __construct($params=NULL,$con=NULL){
        $this->Mailer=new PHPMailer();
        $this->Mailer->SMTPAuth=true;
        $this->Mailer->IsSMTP();
        $this->Mailer->CharSet="utf-8";
        if(!is_null($params) && is_array($params)){
            $this->params=array(
                'to' => $this->parseAddr($params['to'],false),
                'from' => $this->parseAddr($params['from']),
                'html' => $params['html']==true,
                'subject' => $params['subject'],
                'body' => $params['body'],
                'alt_body' => $params['alt_body'],
                'reply_to' => $this->parseAddr($params['reply_to'])
            );
            if(!empty($params['cc']))
                $this->params['cc']=$this->parseAddr($params['cc'],false);
            if(!empty($params['bcc']))
                $this->params['bcc']=$this->parseAddr($params['bcc'],false);
            if(!empty($params['tpl']))
                $this->renderBody($params['tpl'],$params['context']);
        }
        if(!is_null($con) && is_array($con)){
            $this->con=array(
                'user' => $con['user'],
                'pass' => $con['pass'],
                'host' => $con['host']
            );    
            if(!empty($con['port']))
                $this->con['port']=$con['port'];
        }else{
            $this->con=array(
                'user' => self::$user,
                'pass' => self::$pass,
                'host' => self::$host,
                'port' => self::$port
            );
        }
        if(!is_null($params) && is_array($params))
            $this->send();
        return $this;
    }
    
    /**
    * Define html content
    * 
    * @param mixed $b
    */
    public function html($b){
        $this->params['html']=$b==true;
        return $this;
    }
    
    /**
    * Add recipient
    * 
    * @param mixed $email >> may be on John <john@hotmail.com> format to avoid $name
    * @param mixed $name
    */
    public function to($email,$name=NULL){
        if(is_null($name)){
            $this->params['to'][]=$this->parseAddr($email);
        }else{
            $this->params['to'][]=array(
                $email,
                $name
            );
        }
        return $this;
    }
    
    /**
    * Add reply-to
    * 
    * @param mixed $email
    * @param mixed $name
    */
    public function replyTo($email,$name=NULL){
        if(is_null($name)){
            $this->params['reply_to']=$this->parseAddr($email);
        }else{
            $this->params['reply_to']=array(
                $email,
                $name
            );
        }
        return $this;
    }
    
    /**
    * Add copy
    * 
    * @param mixed $email
    * @param mixed $name
    */
    public function cc($email,$name=NULL){
        if(is_null($name)){
            $this->params['cc'][]=$this->parseAddr($email);
        }else{
            $this->params['cc'][]=array(
                $email,
                $name
            );
        }
        return $this;
    }
    
    /**
    * Add hidden copy
    * 
    * @param mixed $email
    * @param mixed $name
    */
    public function bcc($email,$name=NULL){
        if(is_null($name)){
            $this->params['bcc'][]=$this->parseAddr($email);
        }else{
            $this->params['bcc'][]=array(
                $email,
                $name
            );
        }
        return $this;
    }
    
    /**
    * Add subject
    * 
    * @param mixed $t
    */
    public function subject($t=NULL){
        if($t!=NULL)
            $this->params['subject']=$t;
        return $this;
    }
    
    /**
    * Add body
    * 
    * @param mixed $b
    */
    public function body($b){
        $this->params['body']=$b;
    }
    
    /**
    * Render html template and establish it as body
    * The template sould be on ./content/templates/common/email/ directory
    * 
    * @param mixed $tpl
    * @param mixed $context
    */
    public function renderBody($tpl,$context=array()){  
        $this->tpl=new TemplateEngine();
        $this->tpl->root=Tpl::get(PATH_COMMON)."email/";
        $this->tpl->controller_root=PATH_CONTROLLER_MODULES.'/';
        $this->tpl->load($this->tpl->root.$tpl);  
        $this->tpl->setContext($context);
        $this->params['body']=$this->tpl->render();
        $this->body=$this->params['body']; 
        $this->params['body']; 
        $this->params['alt_body']=str_replace(array('<br />','<br>'),"\n\r",strip_tags($params['body']));
        $this->params['html']=true;
    }
    
    /**
    * Add attachment
    * 
    * @param mixed $file Complete file path, relative to site root directory
    */
    public function attach($file){
        if(file_exists($file))
            $this->Mailer->AddAttachment($file);
    }
    
    /**
    * Add from
    * 
    * @param mixed $email
    * @param mixed $name
    */
    public function from($email,$name=NULL){
        if(is_null($name)){
            $this->params['from']=$this->parseAddr($email);
        }else{
            $this->params['from']=array(
                $email,
                $name
            );
        }
        return $this;    
    }
    
    private function enqueue(){
        $Mailer = new Mailer();
        $to=array();
        foreach($this->params['to'] as $dest)
            $to[]=$dest[1]."<".$dest[0].">";
        $to=implode(",",$to);
        $Mailer->populate(array(
            'to' => $to,
            'from' => ($this->params['from'][1]."<".$this->params['from'][0].">"),
            'title' => ($this->params['subject']),
            'body' => ($this->params['body']),
            'alt_body' => ($this->params['alt_body']),
            'html' => $this->params['html'] ? 1 : 0,
            'fecha_creado' => "now()",
            'enviado' => 0
        ));
        $Mailer->save();
    }
    
    /**
    * Send email
    * 
    */
    public function send($save_on_queue=false){
        if($save_on_queue){
            $this->enqueue();
            return true;
        }
        // Credentials
        $this->Mailer->Host=$this->con['host'];
        $this->Mailer->Username=$this->con['user'];
        $this->Mailer->Password=$this->con['pass'];
        $this->Mailer->Port=$this->con['port'];
        // Message
        foreach($this->params['to'] as $i => $dest){
            $this->Mailer->AddAddress($dest[0],$dest[1]);
        }
        foreach($this->params['cc'] as $i => $dest){
            $this->Mailer->AddCC($dest[0],$dest[1]);
        }
        foreach($this->params['bcc'] as $i => $dest){
            $this->Mailer->AddBCC($dest[0],$dest[1]);
        }
        $this->Mailer->From=$this->params['from'][0];
        $this->Mailer->FromName=$this->params['from'][1];
        $this->Mailer->AddReplyTo($this->params['reply_to'][0],$this->params['reply_to'][1]);
        $this->Mailer->Subject=$this->params['subject'];
        if($this->params['html']==true)
            $this->Mailer->MsgHTML($this->params['body']);
        else
            $this->Mailer->Body=$this->params['body'];
        $this->Mailer->AltBody=$this->params['alt_body'];
        try{
            ob_start();
            $this->Mailer->Send();

            $Logger = new Logger();
            $Logger->populate(array(
                'evento' => 'SENDMAIL',
                'mensaje' => (json_encode($this->params)),
                'fecha' => 'now()'
            ));
            $Logger->save();
            ob_get_clean();
        }catch(phpmailerException $e){
            ob_start();
            $this->throwError($e);
            
            $Logger = new Logger();
            $Logger->populate(array(
                'evento' => 'SENDMAIL_ERROR',
                'mensaje' => $e->getMessage(),
                'fecha' => 'now()'
            ));
            $Logger->save();
            ob_get_clean();
        }catch(Exception $e){
            ob_start();
            $this->throwError($e);
            
            $Logger = new Logger();
            $Logger->populate(array(
                'evento' => 'SENDMAIL_ERROR',
                'mensaje' => $e->getMessage(),
                'fecha' => 'now()'
            ));
            $Logger->save();
            ob_get_clean();
        }
    }
    
    private function parseAddr($addr,$optimize=true){
        $addr=explode(',',$addr);
        $ret=array();
        foreach($addr as $i => $a){
            $a=trim($a);
            if(strpos($a,"<")===false){
                $ret[]=array($a);
            }else{
                $matches=array();
                preg_match('/(.*)<(.+?)>/',$a,$matches);
                $ret[]=array($matches[2],$matches[1]);    
            }
        }
        if(count($ret)<=1 && $optimize){
            return $ret[0];
        }else{
            return $ret;
        }
    }
    
    private function throwError($e){
        if(self::$debug){
            throw new Exception($e);
        }else{
            die("Mail cls error: $e");
        }
    }
}