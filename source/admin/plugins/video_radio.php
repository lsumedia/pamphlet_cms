<?php

class lastfm{
    static function searchSong($title){
        global $config;
        $key = $config['lastfm_apikey'];
        $json = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=track.search&track=$title&api_key=$key&format=json");
        return json_decode($json,true);
    }
}


class visual_radio extends mediaPlayer{
    
    public $name = 'radio_visual';
    
    public $title = 'SHOUTcast/Icecast';
    public $live = true;
    public $vod = true;
    
    static function build($content,$setup){
        global $config;
        
        $primarySource = $content->sources[0];
        $url= $primarySource->src;

        $poster = $content->poster;
        $nowplaying_url = $content->source;
        
        if(!isset($content->pullsongs)){
            $content->pullsongs = true;
        }
        
        require_once('plugins/radio/player.php');

        //$title = radioPlayer::getNowPlaying($nowplaying, $content->title);
        $content->plaintitle = $content->title;
        $info = radioPlayer::getNowPlaying($nowplaying_url);
        if(strlen($info['title']) > 0){
            $content->server_info = $info['raw'];
            $content->server_nowplaying = $info['title'];
            if($content->pullsongs != false){
                $content->nowplaying = $info['title'];
                //$content->title = $content->title . ': ' . $info['title'];
                $content->liveSongInfo = true;
                if(strlen($config['lastfm_apikey']) > 0 && $config['enable_lastfm'] == true){
                    $content->songinfo = lastfm::searchSong($content->nowplaying);
                    if($track = $content->songinfo['results']['trackmatches']['track'][0]){
                        $content->nowplaying = $track['artist'] . ' - ' . $track['name'];
                    }
                }
            }else{
                $content->nowplaying = "";
                $content->liveSongInfo = false;
            }
        }
        //$content->poster = null;
        
        ob_start();
        
        html::css("plugins/radio/video-js-custom.css");
        //html::js("plugins/video/videojs/core/video.min.js");
        
        html::js("//vjs.zencdn.net/5.3.0/video.js");	//CDN version
        html::jquery();
       
        
        html::css('plugins/video/videojs/resolution-switcher/videojs-resolution-switcher.css');
        html::js('plugins/video/videojs/resolution-switcher/videojs-resolution-switcher.js');
       
        $audioonly = true;
        foreach($content->sources as $source){
            if(stripos($source->type,'audio') === false){
                $audioonly = false;
            }
        }
        
        $content->audioonly = $audioonly;
        
        $cbcode = ($audioonly == true) ? ',"inactivityTimeout": 0' : '';
        $autoplay = ($content->live || $_GET['autoplay'])? 'autoplay' : '';
        
        echo "<video id=\"video\" class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$poster\" controls $autoplay data-setup='{\"techOrder\": [\"html5\",\"flash\"] , \"plugins\": { \"videoJsResolutionSwitcher\" : { \"default\" : \"720\" } } $cbcode}' $video->code>", PHP_EOL;
        foreach($content->sources as $source){
            $src = $source->src;
            $type = $source->type;
            $res = $source->res;
            if($audioonly){
                $label = $res;
            }else{
                $label = $res . 'p';
            }
            echo "<source label=\"$label\" res=\"$res\" src=\"$src\" type=\"$type\" >", PHP_EOL;
        }
        echo "Your browser does not support the video tag", PHP_EOL;
                
        echo "</video>", PHP_EOL;
        
        //echo '<script>videojs(\'#video\').videoJsResolutionSwitcher</script>';

        if($content->channelID){
            $json = actualLink() . "/public.php?action=plugin_videomanager&id=$content->channelID";
        }else{
            $json = actualLink() . "/public.php?action=plugin_vod&id=$content->id";
        }

        echo "<script>videojs('#video');</script>";
        echo  "<script> var json_url = '$json'; </script>", PHP_EOL;
        html::js('plugins/radio/vjs-update.js');

        $content->source =  ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
}
