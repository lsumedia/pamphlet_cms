<?php

class radio extends mediaPlayer{
    public $name = 'radio';
    public $title = 'Radio';
    
    public $live = true;
    public $ondemand = false;
    
    public $supported = array('audio/mp3');
    
    public static function build2(){
        require_once('plugins/radio/player.php');
        $primarySource = $video->sources[0];
        $src = $primarySource->src;
        $content->title = radioPlayer::getNowPlaying($content->source, $content->title);
        $content->source = radioPlayer::build($src, $content->poster, $content->source, $content->title);
        
        return $content;
    }
    
    
    public static function build($content,$setup){
        
        $primarySource = $content->sources[0];
        $url= $primarySource->src;
        
        $poster = $content->poster;
        $nowplaying = $content->source;
        
        $title = self::getNowPlaying($nowplaying, $content->title);
        
        ob_start();
        $dir_location = "plugins/radio";     //Change this if using outside of Pamphlet
        
        echo <<<END
  
<div width="100%" height="100%" class="radplayer" style="background-image:url('$poster');">
<audio id="radio1" src="$url" type="audio/mp3" autoplay controls style="display:none;"></audio>
<p class="label" id="nowplaying"></p>
<div id="radiocontrol1" class="radcontrol">
<div class="radcontrol_inner">
<img src="$dir_location/images/play.svg" id="stopstartbtn" onclick="stopstart();" class="control">
<img src="$dir_location/images/volume_up.svg" id="mutebtn" onclick="mutetog();" class="control">
<input type="range" id="volume" value="100">
<p class="label" id="status"></p>
<img src="$dir_location/images/LCR_white.svg" class="ident">
<p class="label" id="title">$title</p>
</div>
</div>
<script>var nowplayinginfo = "$nowplaying"; var title = "$title";</script>
END;
        
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$dir_location/radio.css\" >", PHP_EOL;
        echo "<script src=\"$dir_location/radio.js\"></script>", PHP_EOL;
        
        $content->source =  ob_get_contents();
        ob_end_clean();
        
        
        return $content;
        
    }
    
    public static function getNowPlaying($url,$cname){
        //ShoutCast
        $header = array();
        $header[] = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
        $header[] = 'Cache-Control: max-age=0';
        $header[] = 'Connection: keep-alive';
        $header[] = 'Keep-Alive: 300';
        $header[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
        $header[] = 'Accept-Language: en-us,en;q=0.5';
        $header[] = 'Pragma: ';
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.11 (.NET CLR 3.5.30729)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $response = curl_exec($ch);
        $start = stripos($response, "<body");
        $end = stripos($response, "</body");

        $body = substr($response,$start,$end-$start);
        curl_close($ch);
        if($body != "false"){
            $array = explode(',',$body);
            $last = array_pop($array);
            if(strlen($cname) > 0){
                return $cname . ": " . $last;
            }else{
                return $last;
            }
        }else{
            "Error - response failed or took too long";
        }
        return $cname;
    }   
    
}