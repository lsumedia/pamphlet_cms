<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require('player.php');
//$url = $_POST['data'];
$url = $HTTP_RAW_POST_DATA;
echo radioPlayer::getNowPlaying($url, "");