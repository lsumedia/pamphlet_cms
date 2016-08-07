<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class logout_script extends optionsPage{
    
    public $name = 'auth_logout';
    
    public function configPage(){
        global $auth;
        ?>
<div class="centralelement">
    Logging you out...
    <script>
    window.location.href = '<?= $auth->logout_url() ?>';    
    </script>
</div>

        
<?php       
        
    }
}

$pluginPages[] = new logout_script();