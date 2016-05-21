<?php

header('Content-Type: text/javascript');

require 'functions/data_wrangler.php';
require 'functions/elements.php';


$pages = new standardOptionsPages();
$pages->configure();

uiElement::loadUiElementsJs();
