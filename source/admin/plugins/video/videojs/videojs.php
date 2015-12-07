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
        
        echo PHP_EOL;
        /* INCLUDE CORE */
        html::css("plugins/video/videojs/core/video-js-custom-css");
        
        //html::css("//vjs.zencdn.net/4.8.2/video-js.css");
        //html::js("//vjs.zencdn.net/4.8.2/video.js");	//CDN version
        
        html::js("plugins/video/videojs/core/video.min.js");
        
        /* INCLUDE PLUGINS */
        
        /* FLASHLS */
        
        html::js("plugins/video/video-js/flashls/videojs.flashls.js");
        
        /* THE (hopefully) AWESOME FLASH-FREE HLS - WIP */
        
        //html::js('plugins/video/videojs/media-sources/videojs-media-sources.js');
        //html::js('plugins/video/videojs/hls/videojs.hls.js');
        
        /* QUALITY SELECTOR (DOESN'T WORK IN VJS 5.0) */
        
        //html::css("plugins/video/videojs/qualitysel/video-quality-selector.css");
        //html::js("plugins/video/videojs/qualitysel/video-quality-selector.js");
        
        /* ENABLE PLUGINS */
        
        echo "<script>videojs.flashls({swfUrl: \"plugins/video/video-js/flashls/video-js.swf\"}); videojs.options.flash.swf = \"plugins/video/video-js/flashls/video-js.swf\"; /* videojs.options.techOrder = ['html5', 'flash']; */</script>";
        //html::js("plugins/video/video-js/osmf/videojs-osmf.js");
        //echo "<script>videojs.options.osmf.swf=\"plugins/video/video-js/osmf/videojs-osmf.swf\";</script>";
        //echo "<script>videojs.plugin('resolutionSelector',resolutionSelector);</script>";
    }
    static function run(){
        //Place after the player you wish to activate. Will activate the FIRST tag with the "html5vid" class
        echo PHP_EOL;
        echo "<script>"
        . "var vidPlayer = document.getElementsByClassName('html5vid')[0]; "
        . "videojs(vidPlayer, {}, function() {}); "
        . "videojs( '#video', { plugins : { resolutionSelector : {default_res : '720'} } });"
        . "</script>", PHP_EOL;
    }
}