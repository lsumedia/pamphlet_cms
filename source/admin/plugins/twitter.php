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
        
        twitterscroller::build($_GET['search']);
        
        
        
    }
    
    function configPage(){
        global $connection;
        require('plugins/twitter/twitterscreen.php');
        
        ce::begin('style="width:60vw;"');
        
        $term = $_GET['term'];
        //echo "<form>";
        echo "<div class=\"form\">";
        echo "<div class=\"fieldRow\">";
        echo "<p>Search term</p>";
        echo "<input id=\"term_input\" value=\"$term\"></input>";
        echo "</div>";
        echo "<div class=\"fieldRow\">";
        echo "<button onclick=\"cm_loadPage('plugin_twitter&term=' + document.getElementById('term_input').value);\" >Search</button>";
        echo "</div>";
        echo "</div>";

        //echo "</form>";
        if(isset($_GET['term'])){
            $term = $_GET['term'];
            $data = twitterList::getCleanList($term);
            $list = new ajaxList($data,'data');
            $list->title('Matching tweets');
            $list->display();
        }else{
            echo "Please enter a search term";
        }
        
        ce::end();
        
    }
    function updatePage(){
        global $connection;
        
    }
    
}

$pluginPages[] = new twitter();