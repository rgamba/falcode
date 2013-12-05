<?php
class ModuleController extends Controller{
    public function __construct(){
        // Pass
    }

    public function main(){
        print("System default\n");
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
        //echo "Executed: $exe";
    }

    /**
     * Send email queue
     */
    public function mail(){
        set_time_limit(0);
        $db = Db::getInstance();
        $sent = 0;
        while($sent < 10){
            $mailer = $db->fetch("SELECT * FROM mailer WHERE sent = 0 ORDER BY id_mailer ASC LIMIT 1");
            if($mailer->num_rows <= 0)
                break;
            $db->query("UPDATE mailer SET sent = 1, date_sent = now() WHERE id_mailer = " . $mailer->row['id_mailer']);
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
        //print("Sent: $sent");
    }

    /**
     * Delete temporal files to free up space
     */
    public function cleanup(){
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
        //print("Deleted: $deleted");
    }
}
