<?php

/**
 * VideoJS class
 * Must be in the path ./plugins/video/videojs/videojs.php relative to the PHP file which includes it =
 */
class videojs{
    /**
     * Initialise videoJS javascript files
     * 
     */
    static function init(){
        html::css("plugins/video/videojs/core/video-js-custom-css");
        html::js("plugins/video/videojs/core/video.min.js");
        html::js("plugins/video/video-js/flashls/videojs.flashls.js");
        echo "<script>videojs.flashls({swfUrl: \"plugins/video/video-js/flashls/video-js.swf\"}); /* videojs.options.flash.swf = \"plugins/video/video-js/flashls/video-js.swf\"; *//* videojs.options.techOrder = ['flash', 'html5']; */</script>";
        //html::js("plugins/video/video-js/osmf/videojs-osmf.js");
        //echo "<script>videojs.options.osmf.swf=\"plugins/video/video-js/osmf/videojs-osmf.swf\";</script>";
    }
    static function run(){
        //Place after the player you wish to activate. Will activate any tags with the "html5vid" class
        echo "<script>"
        . "var vidPlayer = document.getElementsByClassName('html5vid')[0];"
        . "videojs(vidPlayer, {}, function() {}); "
                /*
        . "if(document.getElementsByClassName('html5vid').length > 0){ "
            . "function play(){ vidPlayer.play(); } "
            . "function pause(){ vidPlayer.pause(); } "
            . "function playing(){ return vidPlayer.playing; } "
            . "function stopstart(){ "
                . "if(playing() == true){ pause(); }"
                . "else{play();}"
        . "}}"*/
                . "</script>", PHP_EOL;
    }
}