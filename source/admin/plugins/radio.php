<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class radio extends mediaPlayer{
    public $name = 'radio';
    public $title = 'Radio';
    
    public $live = true;
    public $ondemand = false;
    
    public $supported = array('audio/mp3');
    
    public static function build($content,$setup){
        require_once('plugins/radio/player.php');
        $primarySource = $video->sources[0];
        $src = $primarySource->src;
        $content->title = radioPlayer::getNowPlaying($content->source, $content->title);
        $content->source = radioPlayer::build($src, $content->poster, $content->source, $content->title);
        
        return $content;
    }
}