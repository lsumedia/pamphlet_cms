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
        
        $json = actualLink() . "/public.php?action=plugin_vod&id=$content->id";
        
        require_once('plugins/radio/player.php');
       
        //$title = radioPlayer::getNowPlaying($nowplaying, $content->title);
        
        $content->source = radioPlayer::build($url, $poster, $nowplaying, $content->title, $json);
        $info = radioPlayer::getNowPlaying($nowplaying_url);
        
        $content->server_info = $info['raw'];
        $content->nowplaying = $info['title'];
        $content->plaintitle = $content->title;
        $content->title = $content->title . ': ' . $info['title'];
        //$content->poster = null;
        
        return $content;
        
    }
    
}