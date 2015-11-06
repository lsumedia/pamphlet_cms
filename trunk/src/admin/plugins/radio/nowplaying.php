<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require('player.php');
$url = filter_input(INPUT_GET,'url');
echo radioPlayer::getNowPlaying($url, "");