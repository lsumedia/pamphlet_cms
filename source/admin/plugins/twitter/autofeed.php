<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$term = filter_input(INPUT_GET,'term');

require_once('TwitterAPIExchange.php');


$settings = array(
    'oauth_access_token' => "",
    'oauth_access_token_secret' => "",
    'consumer_key' => "",
    'consumer_secret' => ""
);

$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = "?q=$term&result_type=recent";
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
$twitter->setGetfield($getfield)
        ->buildOauth($url, $requestMethod);
echo ($twitter->performRequest());
