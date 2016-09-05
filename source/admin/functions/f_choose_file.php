<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class choose_file extends uiElement{
    
    public $name = 'choose_file';
    
    public static function clientSide() {
        global $config;
        ?>
//<script>
    function choose_file(result_id){
        
        media_server_root = '<?= $config['media_server'] ?>';
        
        popup_page = media_server_root + 'popup.php?result_id=' + result_id;
        window.open(popup_page,'_blank','width=950,height=820,menubar=0,status=0');
        
    }
    
    function handle_window_close(id,new_value){
        destination = document.getElementById(id);
        destination.value = new_value;
    }
<?php
    }
}