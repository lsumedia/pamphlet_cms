<?php

/* 
 * init.php
 * 
 * Include needed function files - run on all pages that require it
 */

require_once('config.php');

$connection = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if($connection->connect_error){
    echo 'Initialisation error: ' . $db->connect_error;
}

$dir = 'functions';

$comp_includes = scandir($dir);

foreach($comp_includes as $comp_ifile){
    if(strpos($comp_ifile, '.php') !== false){
        //echo $dir . '/' . $comp_ifile;
        include($dir . '/' . $comp_ifile);
    }
}