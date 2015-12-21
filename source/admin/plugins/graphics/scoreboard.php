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
        
        html::div("name","tvlogo");
        echo "<img src=\"plugins/graphics/images/LSUTV_White.png\">";
        html::closeDiv();
        
        html::div("timer","timer");
        echo "00:00";
        html::closeDiv();
        
        html::div("name","quarter");
        html::closeDiv();
        /*
        html::div("name","half");
        echo "2nd";
        html::closeDiv();
        */
        html::closeDiv();
        
        echo "<script>", PHP_EOL;
        echo "var id = $id;", PHP_EOL;
        echo "setInterval(updateScores,100);", PHP_EOL;
        echo "setInterval(updateTimer,100);", PHP_EOL;
        echo "</script>", PHP_EOL;
        html::end();
    }
    
    static function build_controller($id,$team1,$team2){
        
        if(get_username() == false){
            echo "You must be logged in to access this page";
            die();
        }
        
        html::start();
        html::css("plugins/graphics/controller.css");
        html::lockZoom();
        html::title("Scoreboard Controller");

        html::endHead();

        html::div("wrapper","wrapper");
        html::div("title","title");
        echo "Scoreboard Controller";
        html::closeDiv();
        
        html::div("controller", "controller");
        html::div("scorecontrol","team1score");
        self::controlbuttons(1,$team1);
        html::closeDiv();
        html::div("scorecontrol","team2score");
        self::controlbuttons(2,$team2);
        html::closeDiv();
        
        html::closeDiv();
        html::div("timercontroller","timer");
        echo "<div class=\"row\"><h1>Timer</h1></div>";
        echo "<input type=\"text\" id=\"timerdisplay\" readonly value=\"Loading...\">";
        echo "<div class=\"row\"><button class=\"bigbutton\" onclick=\"toggleTimer();\" id=\"timerStopStart\">Start</button><button class=\"bigbutton\" id=\"timerReset\">Reset</button></div>";
        //echo "<div class=\"row\"><input type=\"time\" id=\"timermanual\"><button>Set</button></div>";
        echo "<div class=\"row\"><span>Quarter</span><input type=\"number\" min=\"1\" max=\"4\" id=\"quarter\" value=\"1\"><button id=\"setqtrbtn\">Set</button></div>";
        html::closeDiv();
        
        echo "<script>", PHP_EOL;
        echo "var id = $id;", PHP_EOL;
        echo "</script>", PHP_EOL;
        
        html::js("plugins/graphics/controller.js");
        html::end();
    }
    public static function controlbuttons($teamid,$teamname){
        html::div("buttonrow","scoreupdate_$teamid");
        echo "<h2>$teamname</h2>";
        echo "<button class=\"\" id=\"score_minus_$teamid\" onclick=\"scoreupdate($teamid,-1);\">&#8210;</button>";
        echo "<input type=\"text\" class=\"scorefield\" id=\"currentscore_$teamid\"></input>";
        echo "<button class=\"\" id=\"score_plus_$teamid\" onclick=\"scoreupdate($teamid,1);\">+</button>";
        html::closeDiv();
        
        html::div("buttonrow","scorechange_$teamid");
        
        html::closeDiv();
        
        html::div("buttonrow","scorereset_$teamid");
        html::closeDiv();
    }
}
