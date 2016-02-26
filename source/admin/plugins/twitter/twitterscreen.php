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
        echo "<img style=\"transform:translate(0,0);\" src=\"//falkegg.co.uk/images/bouleyblur.min.jpg\">";
        html::closeDiv();
          
          echo "<script>", PHP_EOL;
          echo "var term='$term';"
                  . "loadAllTweets();"
                  . "var timer1 = setInterval(loadNewTweet,10000);", PHP_EOL;
          echo "</script>", PHP_EOL;
          
          html::end();
    }
}

class twitterList{
    
    public static function getList($term){
        require_once('TwitterAPIExchange.php');

        require_once('twitter_keys.php');

        $url = 'https://api.twitter.com/1.1/search/tweets.json';
        $getfield = "?q=$term-filter:retweets&result_type=recent&count=100";
        $requestMethod = 'GET';

        $twitter = new TwitterAPIExchange($settings);
        $twitter->setGetfield($getfield)      
                ->buildOauth($url, $requestMethod);
        $datastring = $twitter->performRequest();
        $data = json_decode($datastring,1);
        $statuses = $data['statuses'];
        return $statuses;
    }
    
    public static function getCleanList($term){
        $statuses = self::getList($term);
        $clean = [];
        foreach($statuses as $status){
            $user = $status['user'];
            $author = $user['name'];
            $screen_name = '@' . $user['screen_name'];
            $dpurl = $user['profile_image_url_https'];
            $dp = "<img src=\"$dpurl\" />";
            $text = $status['text'];
            $posted = $status['created_at'];
            $new = ['' => $dp, 'Author' => $author, 'Handle' => $screen_name, 'Text' => $text, 'Date' => $posted];
            $clean[] = $new;
        }
        return $clean;
    }
    
    public static function getOne($tweetid){
        require_once('TwitterAPIExchange.php');

        /* This bit is secret so DON'T put it on GitHub! */
        require_once('twitter_keys.php');

        $url = 'https://api.twitter.com/1.1/statuses/show.json';
        $getfield = "?id=$tweetid";
        $requestMethod = 'GET';

        $twitter = new TwitterAPIExchange($settings);
        $twitter->setGetfield($getfield)      
                ->buildOauth($url, $requestMethod);
        $datastring = $twitter->performRequest();
        $status = json_decode($datastring,1);
        return $status;
    }   
}