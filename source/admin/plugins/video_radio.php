<?php

class radio extends mediaPlayer{
    public $name = 'radio';
    public $title = 'Radio';
    
    public $live = true;
    public $ondemand = false;
    
    public $supported = array('audio/mp3', 'audio/ogg', 'audio/wave', 'audio/wav');
    
    public static function build($content,$setup){
        
        $primarySource = $content->sources[0];
        $url= $primarySource->src;
        
        $poster = $content->poster;
        $nowplaying_url = $content->source;
        
        if($content->channelID){
            $json = actualLink() . "/public.php?action=plugin_videomanager&id=$content->channelID";
        }else{
            $json = actualLink() . "/public.php?action=plugin_vod&id=$content->id";
        }
        
        require_once('plugins/radio/player.php');
       
        //$title = radioPlayer::getNowPlaying($nowplaying, $content->title);
        $content->plaintitle = $content->title;
        $content->source = radioPlayer::build($url, $poster, $nowplaying, $content->title, $json);
        $info = radioPlayer::getNowPlaying($nowplaying_url);
        $content->server_info = $info['raw'];
        $content->server_nowplaying = $info['title'];
        
        if($content->pullsongs != false){
            $content->nowplaying = $info['title']; 
            $content->title = $content->title . ': ' . $info['title'];
        }
        //$content->poster = null;
        
        return $content;
        
    }
    
}

class visual_radio extends mediaPlayer{
    
    public $name = 'radio_visual';
    
    public $title = 'LCR Video Player';
    public $live = true;
    public $vod = true;
    
    static function build($content,$setup){
        $primarySource = $content->sources[0];
        $url= $primarySource->src;

        $poster = $content->poster;
        $nowplaying_url = $content->source;
        
        require_once('plugins/radio/player.php');

        //$title = radioPlayer::getNowPlaying($nowplaying, $content->title);
        $content->plaintitle = $content->title;
        $info = radioPlayer::getNowPlaying($nowplaying_url);
        if(strlen($info['title']) > 0){
            $content->server_info = $info['raw'];
            $content->server_nowplaying = $info['title'];
            if($content->pullsongs != false){
                $content->nowplaying = $info['title'];
                $content->title = $content->title . ': ' . $info['title'];
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
        
        $cbcode = ($audioonly == true) ? ',"inactivityTimeout": 0' : '';
        $autoplay = ($content->live)? 'autoplay' : '';
        
        echo "<video id=\"video\" class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$poster\" controls $autoplay data-setup='{\"techOrder\": [\"html5\",\"flash\"] , \"plugins\": { \"videoJsResolutionSwitcher\" : { \"default\" : \"720\" } } $cbcode}' $video->code>", PHP_EOL;
        foreach($content->sources as $source){
            $src = $source->src;
            $type = $source->type;
            $res = $source->res;
            $label = $res . 'p';
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

        echo  "<script> var json_url = '$json'; </script>", PHP_EOL;
        html::js('plugins/radio/vjs-update.js');

        $content->source =  ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
}