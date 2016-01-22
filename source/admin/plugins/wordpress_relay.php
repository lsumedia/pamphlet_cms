<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Wordpress Relay Classes
//Temporary solution

class wordpress_relay extends optionsPage{
    
    public $name = 'wordpress_relay';

    public function displayPage(){
        echo file_get_contents('https://media.lsu.co.uk/?json_route=/posts&filter[cat]=24');
    }
}

$pluginPages[] = new wordpress_relay();