<?php

/* 
 * public.php
 * Sends JSON responses for requests for public pages
 * 
 */

if(isset($_GET['debug'])){	//Error reporting - disable for production
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
}else{
    ini_set('display_errors',0);
    ini_set('display_startup_errors',0);
    error_reporting(0);
}

require 'config.php';
require 'connect.php';
require 'functions/data_wrangler.php';
require 'functions/elements.php';

//Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

//Current internal pages

$pages = new standardOptionsPages();
$pages->configure();

if(isset($_GET['action'])){
    $action = filter_input(INPUT_GET,'action');
    
    if($page = $pages->matchObject($action)){
        try{
            $page->displayPage();
        }catch(Exception $e){
            "Error - could not perform that action: $e";
        }
    }else{
        echo "Error - Page $action does not exist";
    }
}else{
    echo "Error - Empty page request";
}