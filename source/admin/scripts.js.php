<?php

header('Content-Type: text/javascript');

require 'functions/data_wrangler.php';
require 'functions/elements.php';

$pages = new standardOptionsPages();    //Initialize plugin finder object
$pages->configure();                    //Load plugin data

uiElement::loadUiElementsJs();          //Print scripts
