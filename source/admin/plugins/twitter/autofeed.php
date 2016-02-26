<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$term = filter_input(INPUT_GET,'term');

require_once('TwitterAPIExchange.php');

include('twitter_keys.php');

$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = "?q=$term-filter:retweets&result_type=recent";
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
$twitter->setGetfield($getfield)      ->buildOauth($url, $requestMethod);
if(isset($_GET['debug'])){
    $tweets = json_decode($twitter->performRequest());
    var_dump($tweets);
}else{
    echo ($twitter->performRequest());
}