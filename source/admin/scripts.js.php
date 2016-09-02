<?php

if(isset($_GET['debug']) || $config['debug'] == true){
    ini_set("display_errors", 1);
    ini_set("track_errors", 1);
    ini_set("html_errors", 1);
    error_reporting(E_ALL);
}

header('Content-Type: text/javascript');

require_once 'init.php';


$pages = new standardOptionsPages();
$pages->configure();

uiElement::loadUiElementsJs();
