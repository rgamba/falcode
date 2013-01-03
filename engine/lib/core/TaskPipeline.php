<?php
class TaskPipeline{
    private $db;
    public $last_cmd;

    public function __construct($task=NULL){
        $this->db = Db::getInstance();
        if(!is_null($task))
            $this->create($task);
    }

    public function create($task){
        $this->db->query("INSERT INTO task_pipeline(command,executed) VALUES('".$this->db->escape($task)."',0)");
    }

    public function executeNext(){
        $this->db = Db::getInstance();
        $task = $this->db->fetch("SELECT * FROM task_pipeline WHERE executed = 0 ORDER BY id_task_pipeline ASC LIMIT 1");
        if($task->num_rows <= 0){
            return false;
        }
        $response = "";
        ob_start();
        passthru($task->row['command']);
        $response = ob_get_clean();
        $this->last_cmd = array(
            'cmd' => $task->row['command'],
            'response' => $response
        );
        $this->db->update("task_pipeline",array(
            'executed' => '1',
            'output' => $response
        ),"WHERE id_task_pipeline = {$task->row['id_task_pipeline']}");
        return true;
    }
}