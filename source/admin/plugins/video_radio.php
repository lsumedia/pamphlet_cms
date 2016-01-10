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
        $nowplaying = $content->source;
        
        
        require_once('plugins/radio/player.php');
       
        //$title = radioPlayer::getNowPlaying($nowplaying, $content->title);
        
        $content->source = radioPlayer::build($url, $poster, $nowplaying, $content->title);
        $content->title = radioPlayer::getNowPlaying($nowplaying, $content->title);
        $content->poster = null;
        
        return $content;
        
    }
    
}