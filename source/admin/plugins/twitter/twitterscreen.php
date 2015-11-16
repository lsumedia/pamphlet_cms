<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class twitterscreen{

    public static function build($feed,$image,$title){
    
        html::start();
        
        echo "<div id=\"background\"></div>";

        html::css('plugins/twitter/twitterscreen.css');
        html::endHead();
        
        echo "<iframe id=\"screen\" src=\"public.php?action=plugin_twitter&search=$feed\"></iframe>";

        html::div('banner','banner');
        echo "<span>$title</span>";
        html::closeDiv();
        

        echo "<img class=\"biglogo\" src=\"$image\">";

        html::end();
    }

}

class twitterscroller{
    public static function build($term){
        html::start();

        html::css('plugins/twitter/twitter.css');
        html::js('plugins/twitter/twitter.js');
        html::endHead();

        html::div('wrapper','wrapper');
            html::div('profilebar','profilebar');
        

                html::div('profilepic','profilepic');
                html::closeDiv();
                
                html::div('namebox','namebox');
                echo "<p id=\"name\"></p><p id=\"handle\"></p>";
                html::closeDiv();
                
            html::closeDiv();
            html::div('result','result');
            echo "No tweets found yet!";
            html::closeDiv();
            
        html::closeDiv();
        
        html::div('bgimage','bgimage');
        echo "<img style=\"transform:translate(0,-30%);\" src=\"/live/images/hustings.jpg\">";
        html::closeDiv();
          
          echo "<script>", PHP_EOL;
          echo "var term='$term';"
                  . "loadAllTweets();"
                  . "var timer1 = setInterval(loadNewTweet,10000);", PHP_EOL;
          echo "</script>", PHP_EOL;
          
          html::end();
    }
}