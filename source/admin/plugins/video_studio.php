<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class video_studio extends optionsPage{
    
    public $name = "video_studio";
    public $title = "Broadcast Studio";
    
    public function configPage(){
        
        $channel_id = (isset($_GET['channel_id']))? $_GET['channel_id'] : 1;
        $channel_json_url = actualLink() . '/public.php?action=plugin_videomanager&id=' . $channel_id;
        $channel_iframe_url = actualLink() . '/public.php?action=plugin_videomanager&iframe=' . $channel_id;
        
        ?>
<div class="bigpanel">
    <div id="titlebar"><h2>Broadcast Studio</h2></div>
    <div class="row">
        <div class="sixteen-nine" id="chan_preview">
            <iframe class="content" src="<?= $channel_iframe_url ?>" ></iframe>  
        </div>
    </div>
</div>


<style>
    body{
        background-color:black;
    }
    .bigpanel{
        width:100%;
        height:100%;
        color:white;
    }
    iframe{
        border:none;
        width:100%;
        height:100%;
    }
    #chan_preview{
        width:800px;
        height:450px;
    }
    
</style>
<?php


    }
    
}

$pluginPages[] = new video_studio();