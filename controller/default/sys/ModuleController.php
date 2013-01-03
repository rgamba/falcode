<?php
class ModuleController extends Controller{
    public function __construct(){
        // Pass
    }

    public function main(){
        $this->throwAccessDenied();
    }

    /**
     * Execute command pipeline
     */
    public function task(){
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $this->blank(true);
        if($this->request->token != $this->config->cron_token)
            $this->throwAccessDenied("Invalid token");
        $Tp = new TaskPipeline();
        $exe = 0;
        while($exe < 5){
            if($Tp->executeNext() != true){
                break;
            }
            $exe ++;
        }
        echo "Executed: $exe";
    }

    /**
     * Send email queue
     */
    public function mail(){
        set_time_limit(0);
        $this->blank(true);
        $db = Db::getInstance();
        if($this->request->token != $this->config->cron_token)
            $this->throwAccessDenied("Invalid token");
        $sent = 0;
        while($sent < 10){
            $mailer = $db->fetch("SELECT * FROM mailer WHERE enviado = 0 ORDER BY id_mailer ASC LIMIT 1");
            if($mailer->num_rows <= 0)
                break;
            $db->query("UPDATE mailer SET enviado = 1, fecha_enviado = now() WHERE id_mailer = " . $mailer->row['id_mailer']);
            $Mail = new Mail(array(
                'to' => $mailer->row['to'],
                'from' => $mailer->row['from'],
                'subject' => $mailer->row['title'],
                'body' => $mailer->row['body'],
                'html' => $mailer->row['html'] == 1 ? true : false,
                'alt_body' => $mailer->row['alt_body'],
                'reply_to' => $mailer->row['from']
            ));
            $sent++;
            usleep(10);
        }
        echo "Sent: $sent";
    }

    /**
     * Delete temporal files to free up space
     */
    public function cleanup(){
        $this->blank(true);
        if($this->request->token != $this->config->cron_token)
            $this->throwAccessDenied("Invalid token");
        $files = scandir(PATH_CONTENT.'tmp');
        $time = $this->config->tmp_files_lifetime * 60 * 60;
        $deleted = 0;
        foreach($files as $file){
            if($file == "." || $file == "..")
                continue;

            if(time() - filemtime(PATH_CONTENT.'tmp/'.$file) > $time){
                unlink(PATH_CONTENT.'tmp/'.$file);
                $deleted++;
            }
        }
        echo "Deleted: $deleted";
    }
    
    public function exchange_rates(){
        $this->blank(true);
        if($this->request->token != $this->config->cron_token)
            $this->throwAccessDenied("Invalid token");
        $Http = new HttpClient("http://openexchangerates.org/");
        $Http->get("/api/latest.json?app_id=". $this->config->ox_app_id);
        $response = json_decode($Http->getBody(),true);    
        $M = new Moneda();
        if(!empty($response['rates'])){
            foreach($response['rates'] as $key => $rate){
                $M->clear();
                $M->where("currency_code = '{0}'",$key)->execute();
                if($M->rows <= 0){
                    $M->clear();
                    $M->populate(array(
                        'currency_code' => $key,
                        'rate' => $rate,
                        'fecha' => 'now()'        
                    ))->save();   
                }else{
                    $this->db->update("moneda",array(
                        'rate' => $rate,
                        'fecha' => 'now()'
                    ),"WHERE currency_code = '".$this->db->escape($key)."'");   
                }
            }   
        }
    }

    public function captcha(){
        $this->load->extension('cool_captcha');
        $captcha = new SimpleCaptcha();
        $captcha->resourcesPath = PATH_EXTENSIONS."cool_captcha/resources";
        $captcha->wordsFile = 'words/es.php';
        $captcha->blur = false;
        //$captcha->minWordLength = 3;
        $captcha->CreateImage();
        die();
    }
}
