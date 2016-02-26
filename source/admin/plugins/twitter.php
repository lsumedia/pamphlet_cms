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
        
        $currentString = file_get_contents('plugins/twitter/current.json');
        $current = json_decode($currentString,1);
        
        $clean = [];
        $user = $current['user'];
        $author = $user['name'];
        $screen_name = '@' . $user['screen_name'];
        $dpurl = $user['profile_image_url_https'];
        $dp = "<img src=\"$dpurl\" />";
        $text = $current['text'];
        $posted = $current['created_at'];
        $new = ['' => $dp, 'Author' => $author, 'Handle' => $screen_name, 'Text' => $text, 'Date' => $posted];
        $clean[] = $new;
        
        $currentIL = new ajaxList($clean, 'currenttweet');
        $currentIL->title('Current live tweet');
        $currentIL->display();
        
        $term = $_GET['term'];
        //echo "<form>";
        echo "<div class=\"form\">";
        echo "<div class=\"fieldRow\">";
        echo "<p>Search term</p>";
        echo "<input id=\"term_input\" placeholder=\"Search term\" value=\"$term\"></input>";
        echo "</div>";
        echo "<div class=\"fieldRow\">";
        echo "<button onclick=\"cm_loadPage('plugin_twitter&term=' + document.getElementById('term_input').value);\" >Search</button>";
        echo "</div>";
        echo "</div>";
        
        
        
        $current = file_get_contents('plugins/twitter/current.json');

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