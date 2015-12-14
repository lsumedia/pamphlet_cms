<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$term = filter_input(INPUT_GET,'term');

require_once('TwitterAPIExchange.php');

/* This bit is secret so DON'T put it on GitHub! */
$settings = array(
    'oauth_access_token' => "807535267-P4cDp5WvNjHqAYmhDo3iK4uXyXFKliNWjhF15DbF",
    'oauth_access_token_secret' => "EkhCINaKnakrZY0O9E87eq3nycVLvme6TOTsuNeVIiGNj",
    'consumer_key' => "C7u15mc3C96uoCaiEc1EZGiLs",
    'consumer_secret' => "zTNXQzjrVwLNY0bkIogNEG0PiARVpgaNmITM6WDjZSoWFxM1Hf"
);

$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = "?q=$term-filter:retweets&result_type=recent";
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
$twitter->setGetfield($getfield)
        ->buildOauth($url, $requestMethod);
if(isset($_GET['debug'])){
    $tweets = json_decode($twitter->performRequest());
    var_dump($tweets);
}else{
    echo ($twitter->performRequest());
}