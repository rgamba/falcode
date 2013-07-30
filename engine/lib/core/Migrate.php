<?php
abstract class Migrate{
    abstract protected function up();
    abstract protected function down();
    protected $db = NULL;

    public function __construct(){
        $this->db = Db::getInstance();
    }
}