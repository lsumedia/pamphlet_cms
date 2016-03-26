<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class kcFinder extends uiElement{
    public $name = 'KCFinder';
    public static function clientSide(){
        
    }
}

class media extends optionsPage{
    public $name = "plugin_media";
    //public $title = "Media";
    
    function configPage() {
        $ce = new centralElement("ce-medium");
        echo <<<END
<script type="text/javascript">
var finder = new CKFinder();
finder.basePath = '/cms/ckfinder/';
finder.create({width:'100%' , height:'100%'});
</script>
END;
        $ce->end();
    }
}

$plugin_media = new media();

$pluginPages[] = $plugin_media;