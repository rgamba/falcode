<?php
/**
 * FfMpeg.php
 * --
 *
 * Toolkit for ffmpeg library
 * IMPORTANT:
 * - This class can only be run under linux servers
 * - ffmpeg needs to be installed in the server
 * - qt-faststart needs to be installed in order to make the moov atom correction
 *
 * USAGE:
 * $video = new FfMpeg("/home/user/www/video_input.flv");
 * echo "Video bitrate is: " . $video->getBitRate();
 * try{
 *  // This will convert to H264 encodig with a 800K bitrate, same size as original
 *  $video->convert("/home/user/www/video_output.mp4","-b 800K -vcodec libx264 -vpre slow -vpre baseline","/home/user/www/thumbs/video_output.jpg");
 *  echo "Video has been converted";
 * }catch(Exception $e){
 *  die("Error: ".$e->getMessage());
 * }
 *
 * For more info on conversion parameters visit http://ffmpeg.org/
 * @author Ricardo Gamba <rgamba@gmail.com>
 */
class FfMpeg{
    public $exe_dir ="";
    private $video;
    private $info = NULL;
    public $qt_faststart_dir = "/usr/local/bin/";
    // Width, Height, Bitrate
    private $resolutions = array(
        array(1920,1080,3000),
        array(1280,720,1250),
        array(854,480,650),
        array(640,360,350),
        array(426,240,150)
    );

    /**
     * @param $video Absolute video file location including the video file
     * @param string $exe_dir In case ffmpeg can't be run in every directory provide the absolute path
     */
    public function __construct($video,$exe_dir = ""){
        $this->video = $video;
        $this->exe_dir = $exe_dir;
        $this->getInfo();
    }

    private function getInfo(){
        if(!is_null($this->info))
            return false;
        ob_start();
        passthru($this->exe_dir."ffmpeg -i \"{$this->video}\" 2>&1");
        $this->info = nl2br(ob_get_clean());
        if(strpos($this->info,"No such file or directory") !== false){
            throw new Exception("File not found");
        }
        if(strpos($this->info,"Invalid data found when processing input") !== false){
            throw new Exception("Invalid file");
        }
    }

    private function textUntil($position,$char){
        $i = $position;
        $ret = "";
        if(empty($position))
            return;
        while(true){
            $a_char = substr($this->info,$i,1);
            if($a_char === false || $i > strlen($this->info) || $a_char == $char)
                break;
            $i++;
            $ret .= $a_char;
        }
        return $ret;
    }

    /**
     * Get the video duration
     * @return string
     */
    public function getDuration(){
        $this->getInfo();
        return $this->textUntil(strpos($this->info,"Duration:")+9,",");
    }

    /**
     * Get the bitrate
     * @return string
     */
    public function getBitRate(){
        $this->getInfo();
        return $this->textUntil(strpos($this->info,"bitrate:")+8,"<");
    }

    /**
     * Get the video codec
     * @return mixed
     */
    public function getVideoCodec(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Video:")+6,"<");
        $video = explode(",",$video);
        return $video[0];
    }

    /**
     * Get the video dimensions
     * @return string
     */
    public function getDimensions(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Video:")+6,"<");
        $video = explode(",",$video);
        return trim($video[2]);
    }

    /**
     * Get the audio bitrate
     * @return string
     */
    public function getVideoBitRate(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Video:")+6,"<");
        $video = explode(",",$video);
        return trim($video[3]);
    }

    /**
     * Get the video frames per second
     * @return string
     */
    public function getFps(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Video:")+6,"<");
        $video = explode(",",$video);
        return trim($video[4]);
    }

    /**
     * Get the audio codec
     * @return string
     */
    public function getAudioCodec(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Audio:")+6,"<");
        $video = explode(",",$video);
        return trim($video[0]);
    }

    /**
     * Get the audio Hertz
     * @return string
     */
    public function getAudioHz(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Audio:")+6,"<");
        $video = explode(",",$video);
        return trim($video[1]);
    }

    /**
     * Stereo | Mono
     * @return string
     */
    public function getAudioChannel(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Audio:")+6,"<");
        $video = explode(",",$video);
        return trim($video[2]);
    }

    /**
     * Get the bitrate
     * @return string
     */
    public function getAudioBitRate(){
        $this->getInfo();
        $video = $this->textUntil(strpos($this->info,"Audio:")+6,"<");
        $video = explode(",",$video);
        return trim($video[4]);
    }

    /**
     * Convert the video file
     * @param $dest absolute path to the destination converted file
     * @param $params app flags. I.E: "-b 1000K"
     * @param null $pic If this is set it will create a thumbnail in this absolute path
     * @return string
     * @throws Exception
     */
    public function convert($dest,$params,$pic=NULL){
        $dest_tmp = explode(".",$dest);
        $dest_tmp = $dest_tmp[0]."_tmp.".$dest_tmp[1];
        $p = "";
        if(is_array($params)){
            $params= array_merge(array('i' => $this->video),$params);
            foreach($params as $k => $v)
                $p .= " -$k \"$v\"";
        }else{
            $p = $params;
        }

        ob_start();
        exec($this->exe_dir."ffmpeg $p \"$dest_tmp\" 2>&1");
        $response = ob_get_clean();
        if(strpos($response,"Unknown encoder") !== false)
            throw new Exception("Unknown encoder");
        if(strpos($response,"Unable to parse") !== false)
            throw new Exception("Unable to parse option");
        if(strpos($response,"Invalid value") !== false)
            throw new Exception("Invalid option value");
        // Moov atom using qt-faststart
        // MUST have qt-faststart installed in the server
        ob_start();
        passthru($this->qt_faststart_dir."qt-faststart \"{$dest_tmp}\" \"{$dest}\" 2>&1");
        // Remove tmp file
        passthru("rm -f \"{$dest_tmp}\" 2>&1");
        // Create thumbnail picture
        if(!empty($pic)){
            $mid = $this->hoursToSeconds($this->getDuration()) / 2;
            passthru($this->exe_dir."ffmpeg -i \"{$dest}\" -vframes 1 -ss $mid \"$pic\"");
        }
        $response .= ob_get_clean();
        return $response;
    }

    /**
     * Utility function
     * @param $hour
     * @return int
     */
    public function hoursToSeconds($hour){
        $parse = explode(":",$hour);
        return (int) $parse[0] * 3600 + (int) $parse[1] * 60 + (int) $parse[2];
    }
}