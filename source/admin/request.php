<?php
$session_expiration = time() + 3600 * 24 * 2; // +2 days
session_set_cookie_params($session_expiration);
session_start();


if(0){	//Error reporting - disable for production
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
}else{
    ini_set('display_errors',0);
    ini_set('display_startup_errors',0);
    error_reporting(0);
}

require 'init.php';

//Current internal pages
$action = filter_input(INPUT_GET,'action');

$pages = new standardOptionsPages();
$pages->configure();

$auth = new authenticator();

//Logged in!
if($page = $pages->matchObject($action)){
    if(isset($_GET['update'])){
        $page->updatePage();
    }else{
        $page->configPage();
    }
}else{
    echo "Invalid action request";
}


?>