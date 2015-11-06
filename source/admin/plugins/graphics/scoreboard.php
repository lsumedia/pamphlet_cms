<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class graphics_scoreboard{
    
    static function build($id,$team1,$team1img,$team2,$team2img){
        html::start();
        html::css('plugins/graphics/scoreboard.css');
        html::js('plugins/graphics/scoreboard.js');
        html::title('Scoreboard');
        html::endHead();
        html::div("box", "box");
        
        html::div("logo","team1img");
        echo "<img src=\"$team1img\">",PHP_EOL;
        html::closeDiv();
        
        html::div("name","team1");
        echo $team1;
        html::closeDiv();
        
        html::div("score","team1score");
        echo "00";
        html::closeDiv();
        
        html::div("logo","team2img");
        echo "<img src=\"$team2img\">",PHP_EOL;
        html::closeDiv();
        
        html::div("name","team2");
        echo $team2;
        html::closeDiv();
        
        html::div("score","team2score");
        echo "00";
        html::closeDiv();
        
        html::div("logo","tvlogo");
        html::closeDiv();
        
        html::div("timer","timer");
        echo "00:00";
        html::closeDiv();
        
        html::div("name","half");
        echo "2nd";
        html::closeDiv();
        
        html::closeDiv();
        html::end();
    }
}