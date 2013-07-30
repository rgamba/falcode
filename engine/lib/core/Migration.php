<?php
class Migration{
    private $migrations = array();
    private $lve;
    public $errors = array();

    public function __construct(){
        $files = scandir(PATH_ENGINE_MIGRATIONS);
        foreach($files as $file){
            if($file == "." || $file == "..")
                continue;
            if(!preg_match('/M_([0-9]+)_([a-zA-Z0-9_]+)\.php/',$file,$matches))
                continue;
            $this->migrations[intval($matches[1])] = array(
                'version' => intval($matches[1]),
                'desc' => $matches[2],
                'file' => $file,
                'date' => filectime(PATH_ENGINE_MIGRATIONS.$file)
            );
        }

        $this->currentVersion();
    }

    public function latest(){
        return $this->version($this->latestVersion());
    }

    public function version($version){
        if($this->lve['version'] == $version)
            return true;
        $migrations = $this->migrations;
        if($this->lve['version'] < $version){
            $up = true;
        }else{
            arsort($migrations);
        }

        $last_ver = 0;


        foreach($migrations as $ver => $m){
            if($up){
                if($ver > $this->lve['version'] && $ver <= $version){
                    $this->migrate($m['file'],"up");
                }
            }else{
                if($ver <= $this->lve['version'] && $ver > $version){
                    $this->migrate($m['file'],"down");
                }
            }

            $last_ver = $ver;
        }

        file_put_contents(PATH_ENGINE_MIGRATIONS.'last_version',json_encode(array(
            'version' => $version,
            'date' => time()
        )));

        return $version;
    }

    public function latestVersion(){
        end($this->migrations);
        return key($this->migrations);
    }

    public function currentVersion(){
        $this->lve = json_decode(file_get_contents(PATH_ENGINE_MIGRATIONS.'last_version'),true);
        if(!$this->lve){
            $this->lve = array(
                'version' => 0,
                'date' => null
            );
        }
        return (int)$this->lve['version'];
    }

    private function migrate($file,$mode = "up"){
        //echo "Executing $file > $mode\n";
        require_once(PATH_ENGINE_MIGRATIONS.$file);
        $class = current(explode('.',$file));
        $M = new $class();
        try{
            if($mode == "up"){
                $M->up();
            }else{
                $M->down();
            }
        }catch(Exception $e){
            $this->errors[] = "Migration: <$file> ($mode) Error: " . $e->getMessage();
        }

    }
}