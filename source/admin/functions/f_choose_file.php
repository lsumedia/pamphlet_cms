<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class choose_file extends uiElement{
    
    public $name = 'choose_file';
    
    public static function clientSide() {
        ?>
//<script>
    function choose_file(result_id){
        
        media_server_root = '<?= $config['media_server'] ?>';
        
        
        
    }
    
    function handle_window_close(id,new_value){
        destination = document.getElementById(result_id);
        destination.value = new_value;
    }
<?php
    }
}