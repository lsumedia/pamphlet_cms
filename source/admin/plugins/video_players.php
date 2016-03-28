<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class youtube extends mediaPlayer{
    
    public $name = 'youtube';
    public $title = "YouTube";
    
    public $live = true;
    public $ondemand = true;
    
    public static function build($video,$setup){
        $primarySource = $video->sources[0];
        $id = $primarySource->src;
        
        //$hash = unserialize(file_get_contents("https://gdata.youtube.com/feeds/api/videos/$id?v=2"));

        $video->source = "<iframe frameborder=\"0\" class=\"vidplayer\" width=\"100%\" height=\"100%\" allowfullscreen src=\"https://www.youtube.com/embed/$id\"></iframe>";
        $video->poster = "http://img.youtube.com/vi/$id/maxresdefault.jpg";
        return $video;
    }
    
}

class vimeo extends mediaPlayer{
    public $name = 'vimeo';
    public $title = 'Vimeo';
    
    public $live = false;
    public $ondemand = true;
    
    public static function build($video,$setup){
        $primarySource = $video->sources[0];
        $id = $primarySource->src;

        $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$id.php"));

        $poster_url = $hash[0]['thumbnail_large'];          
        
        $video->title = $hash[0]['title'];
        $video->description = $hash[0]['description'];
        $video->source = "<iframe frameborder=\"0\" class=\"vidplayer\" width=\"100%\" height=\"100%\" allowfullscreen src=\"https://player.vimeo.com/video/$id/\"></iframe>";
        $video->poster = $poster_url;
        return $video;
    }
}

class iframe extends mediaPlayer{
    
    public $name = 'iframe';
    public $title = 'IFrame Embed';
    
    public $live = true;
    public $ondemand = true;
    
    public static function build($video,$setup){
        $primarySource = $video->sources[0];
        $src = $primarySource->src;
        
        $video->source = "<iframe frameborder=\"0\" class=\"vidplayer\" width=\"100%\" height=\"100%\" allowfullscreen src=\"$src\"></iframe>";
        return $video;
    }
}

class videojs_4 extends mediaPlayer{
    public $name = 'videojs-4.9';
    public $title = 'VideoJS 4.9';
    
    public $live = true;
    public $ondemand = true;
    
    public static function build($video,$setup){
        $poster = $video->poster;
        
        require_once('plugins/video/videojs/videojs.php');
        
        ob_start();
        
        videojs::init();
        
        echo "<video id=\"video\" class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$poster\" controls autoplay data-setup='{\"techOrder\": [\"html5\",\"flash\"], \"plugins\" : { \"resolutionSelector\" : { \"default_res\" : \"720\" } } }'>", PHP_EOL;
        foreach($video->sources as $source){
            $src = $source->src;
            $type = $source->type;
            $res = $source->res;
            echo "<source data-res=\"$res\" src=\"$src\" type=\"$type\" >", PHP_EOL;
        }
        echo "Your browser does not support the video tag" . "</video>";
        
        //videojs::run();
        
        $video->source =  ob_get_contents();
        ob_end_clean();
        
        return $video;
    }
    
}

class videojs_5 extends mediaPlayer{
    public $name = 'html5';
    public $title = 'VideoJS 5.3';
    
    public $live = true;
    public $ondemand = true;
    
    public static function build($video,$setup){
        $poster = $video->poster;
        $data = json_decode($video->source,1);
        $css = false;   //Whether a primary CSS file has been included yet
        
        ob_start();
        
        if($data['css']){
            $css = true;
            html::css($data['css']);
        }
        //html::js("plugins/video/videojs/core/video.min.js");
        if($data['version']){
            $version = $data['version'];
            if(!$css){ html::css("//vjs.zencdn.net/$version/video-js.css"); $css = true;}
            html::js("//vjs.zencdn.net/$version/video.js");	//CDN version
        }else{
            //html::css("plugins/video/videojs/core/video-js-custom.css");
            if(!$css){ html::css("//vjs.zencdn.net/5.3.0/video-js.min.css"); $css = true;}
            html::js("//vjs.zencdn.net/5.3.0/video.min.js");	//CDN version
        }
        
        if($data['hls'] == 1){
            html::js('plugins/video/videojs/media-sources/videojs-media-sources.min.js');
            html::js('plugins/video/videojs/hls/videojs.hls.min.js');
        }
        
        foreach($data['plugins'] as $plugin){
            echo $plugin, PHP_EOL;
        }
        
        html::css('plugins/video/videojs/resolution-switcher/videojs-resolution-switcher.css');
        html::js('plugins/video/videojs/resolution-switcher/videojs-resolution-switcher.js');
        
        /* Chromecast (requires app) *//*
        html::css('plugins/video/videojs/chromecast/videojs.chromecast.min.css');
        html::js('plugins/video/videojs/chromecast/videojs.chromecast.min.js');
        */
        
        if($video->live == 1 || $data['autoplay'] == 1){
            $autoplay = 'autoplay';
        }else{
            $autoplay = '';
        }
        
        echo "<video id=\"video\" class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$poster\" controls $autoplay data-setup='{\"techOrder\": [\"html5\",\"flash\"] , \"plugins\": { \"videoJsResolutionSwitcher\" : { \"default\" : \"720\" } }}' $video->code>", PHP_EOL;
        foreach($video->sources as $source){
            $src = $source->src;
            $type = $source->type;
            $res = $source->res;
            $label = $res . 'p';
            echo "<source label=\"$label\" res=\"$res\" src=\"$src\" type=\"$type\" >", PHP_EOL;
        }
        echo "Your browser does not support the video tag";
                
        echo "</video>";
        
        //echo '<script>videojs(\'#video\').videoJsResolutionSwitcher</script>';
        
        $video->source =  ob_get_contents();
        ob_end_clean();
        
        return $video;
    }
    
}

class soundcloud extends mediaPlayer{
    public $name = 'soundcloud';
    public $title = 'SoundCloud';
    
    public $live = false;
    public $ondemand = true;
    
    public static function build($video, $setup){
        $primarySource = $video->sources[0];
        $id = $primarySource->src;
        $video->source = "<iframe frameborder=\"0\" class=\"vidplayer\" width=\"100%\" height=\"100%\" allowfullscreen src=\"https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/$id&hide_related=false&show_comments=true&show_user=true&show_reposts=false&visual=true\"></iframe>";;
        
        $video->alt_url = "https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/$id&hide_related=false&show_comments=true&show_user=true&show_reposts=false&visual=false";
        return $video;
    }
    
}

class spotify extends mediaPlayer{
    public $name = 'spotify';
    public $title = 'Spotify Playlist URI';
    
    public $live = false;
    public $ondemand = true;
    
    public static function build($video, $setup){
        $primarySource = $video->sources[0];
        $id = $primarySource->src;
        $video->source = "<iframe src=\"https://embed.spotify.com/?uri=$id&theme=white\" width=\"100%\" height=\"100%\" frameborder=\"0\" allowtransparency=\"true\" class=\"vidplayer\"></iframe>";

        return $video;
    }
    
}


class custom extends mediaPlayer{
    public $name = 'custom';
    public $title = 'Custom code';
    
    public $live = true;
    public $ondemand = true;
    
    public static function build($video,$setup){
        //$video->source = $video->code;
        return $video;
    }
    
    
}



