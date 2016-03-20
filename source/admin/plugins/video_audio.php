<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Audio player tech based on SoundCloud javascript-based player
 *
 * @author Cameron
 */
class video_audio extends mediaPlayer{
    //put your code here
    public $name = 'wavesurfer';
    public $title = 'Wavesurfer Audio';
    
    public $live = false;
    public $ondemand = true;
    
    public $supported = array('audio/mp3', 'audio/ogg', 'audio/wave', 'audio/wav');
    
    public static function build($content, $options = ''){
        
        $primarySource = $content->sources[0];
        $url = $primarySource->src;

        $poster = $content->poster;
        
        require_once('plugins/radio/player.php');

        //$title = radioPlayer::getNowPlaying($nowplaying, $content->title);
        $content->plaintitle = $content->title;
        //$content->poster = null;
        
        ob_start();
 
        html::jquery();
        html::js("//cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/1.0.52/wavesurfer.min.js");	//WaveSurfer plugin
        
        echo "<div id=\"waveform\"></div>";
        echo "<audio id=\"video\" class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$poster\" controls>", PHP_EOL;
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
        echo "Your browser does not support the audio tag", PHP_EOL;
                
        echo "</audio>", PHP_EOL;
        
        ?>
<script>
<button onclick="wavesurfer.playPause()">
    Play/pause
</button>
var wavesurfer = WaveSurfer.create({
   container: '#waveform',
   waveColor: 'black',
   progressColor: '#2486c7'
});

wavesurfer.load('<?php echo $url; ?>');
</script>
<?php
        
        //echo '<script>videojs(\'#video\').videoJsResolutionSwitcher</script>';

        if($content->channelID){
            $json = actualLink() . "/public.php?action=plugin_videomanager&id=$content->channelID";
        }else{
            $json = actualLink() . "/public.php?action=plugin_vod&id=$content->id";
        }

        echo  "<script> var json_url = '$json'; </script>", PHP_EOL;

        $content->source =  ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
}
