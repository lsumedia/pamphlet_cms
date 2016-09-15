<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class visit_site extends optionsPage{
    
    public $name = 'visit_site';
    public $title = "Visit Site";
    
    public $destination = 'http://grovestreet.me/lsutv';
    
    public function configPage() {
        
        ?>
<script class="dynamic-script">
    window.open('<?= $this->destination ?>','_external','');
    window.history.back();
</script>
<?php
        
    }
    
}

$pluginPages[] = new visit_site();