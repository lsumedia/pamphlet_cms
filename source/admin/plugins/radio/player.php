<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function css($url){
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$url\">",PHP_EOL;
    return;
}
function js($url){
    echo "<script src=\"$url\"></script>",PHP_EOL;
    return;
}

class radioPlayer{
    
    public static function build($url,$poster,$nowplaying, $title, $ajaxInfo){
        ob_start();
        $dir_location = "plugins/radio";     //Change this if using outside of Pamphlet
        js('https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js');
        css('https://fonts.googleapis.com/icon?family=Material+Icons');
        css($dir_location."/radio.css");
        echo <<<END
<div width="100%" height="100%" class="radplayer" id='player_outer_wrapper' style="background-image:url('$poster');" >
<audio id="radio1" src="$url" type="audio/mp3" autoplay controls style="display:none;"></audio>
<p class="label" id="nowplaying"></p>
<div id="radiocontrol1" class="radcontrol">
<div class="radcontrol_inner">
    <div id="radcontrol_left">
        <i id="stopstartbtn" onclick="stopstart();" class="material-icons control">play_arrow</i>
        <i id="mutebtn" onclick="mutetog();" class="material-icons control">volume_up</i>
        <input type="range" id="volume" value="100">
        <span class="label" id="status"></span>
    </div>
    <div id="radcontrol_right">
        <span class="label" id="title">$title</span>
        <img src="$dir_location/images/LCR_white.svg" class="ident">
    </div>
</div>
</div>
<script>var json_url='$ajaxInfo'; var title = "$title";</script>
END;
        echo PHP_EOL;

        js($dir_location."/radio.js");
        
        $code =  ob_get_contents();
        ob_end_clean();
        return $code;
        
    }
    
    /**
     * 
     * @param json_string $info - JSON nowplaying info
     * @param string $cname - Channel title
     * @return string
     * 
     * Provide a valid JSON string matching the following model:
     * {"nowplaying":{"type":"[icecast/shoutcast]","url":"nowplayingurl"}}
     * Function will fetch the info from the provided URL and concatenate it with the
     * channel title as such
     * Channel: Artist - Song
     * If $cname is left blank, only the song info will be returned
     */
    public static function getNowPlaying($info){
        
        $source = json_decode($info);
        $infoarray = $source->nowplaying;
        $type = $infoarray->type;
        $url = $infoarray->url;
        
        $info = [];
        
        switch($type){
            case 'icecast':
                $string = file_get_contents($url);

                $info['raw'] = json_decode($string);
                $stats = $info['raw']->icestats;
                if(is_array($stats->source)){
                    $sourceinfo = $stats->source[0];
                }else{
                    $sourceinfo = $stats->source;
                }
                
                $info['title'] = $sourceinfo->title;
                $info['description'] = $sourceinfo->server_description;
                $info['bitrate'] = $sourceinfo->audio_info;
                $info['genre'] = $sourceinfo->genre;

                break;
            case 'shoutcast':
                $raw = self::shoutCastData($url);
                $info['raw'] = $raw;
                $info['title'] = end($info['raw']);
                break;
        }
        
        return $info;

    }   
    
    static function shoutCastData($url){
        //ShoutCast
        $header = array();
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
        }else{
            "Error - response failed or took too long";
        }
        return $array;
    }
}


