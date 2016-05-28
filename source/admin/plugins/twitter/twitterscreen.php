<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*In order to use this plugin you must create a twitter_keys.php file containing
 * your Twitter app keys
 * It should contain an array of data formatted like below:
 * $settings = array(
    'oauth_access_token' => "oath_token",
    'oauth_access_token_secret' => "oath_secret",
    'consumer_key' => "consumer_key",
    'consumer_secret' => "consumer_secret"
);
 */

class twitterList{
    
    public static function rawToClean($status){
        $user = $status['user'];
        $author = $user['name'];
        $screen_name = '@' . $user['screen_name'];
        $dpurl = $user['profile_image_url_https'];
        $dp = "<img src=\"$dpurl\" />";
        $text = $status['text']; 
        $timestamp = strtotime($status['created_at']);
        $posted = date('D dS M Y H:i',$timestamp);
        $onclick = "if(confirm('Make this tweet live?')){ replaceCurrentTweet('" . $status['id_str'] . "');}";
        $clean = ['' => $dp, 'Author' => $author, 'Handle' => $screen_name, 'Text' => $text, 'Date' => $posted, 'onclick' => $onclick];
        return $clean;
    }
    
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
            $clean[] = self::rawToClean($status);
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
    
    public static function getOneOembed($tweeturl){
        require_once('TwitterAPIExchange.php');

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
    
    public static function getOneClean($tweetid){
        $raw = self::getOne($tweetid);
        return self::rawToClean($raw);
    }
}