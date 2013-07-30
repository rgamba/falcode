<?php
class ModuleController extends Controller{
    private $Migration;

    public function __construct(){
        $this->Migration = new Migration();
    }

    public function main(){
        $version = $_SERVER['argv'][2];

        if($version == "latest")
            $version = $this->Migration->latestVersion();
        $res = $this->Migration->version($version);
        if(empty($this->Migration->errors))
            echo "Migrated to the version $res succesfully\n";
        else{
            echo "The following errors raised during migration:\n".implode($this->Migration->errors,"\n")."\n";
        }
    }

    public function compare_versions(){
        echo "Current database version:\t" . $this->Migration->currentVersion() . "\n";
        echo "Latest database version:\t" . $this->Migration->latestVersion() . "\n";
    }
}