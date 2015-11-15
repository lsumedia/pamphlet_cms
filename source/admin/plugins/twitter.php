<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class twitter extends optionsPage{
    public $name = "plugin_twitter";
    public $title = "Twitter";
    
    function setup(){
        
    }
    
    function displayPage(){
        
        global $connection; //Import connection object
        
        require_once('plugins/twitter/twitterscreen.php');
        
        if(isset($_GET['search'])){
            $term = filter_input(INPUT_SEARCH,'search');
            twitterscroller::build($term);
        }else{
            twitterscreen::build("falkegg","/live/images/falkeggmedia.png","#falkegghustings");
        }
        
    }
    
    function configPage(){
        global $connection;
        $ce = new centralElement("ce-medium");
        
        $ce->end();
        
    }
    function updatePage(){
        global $connection;
        
    }
    
}

$pluginPages[] = new twitter();