/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var start = new Date();
var running = 1;    //Whether timer is running
var elapsed = 0; //How long the timer has been running for
var sent = true;
var starting = 1; 
var lasttime = 0; //Last time displayed on the timer
var change = 0;

function updateScores(){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
            if(request.readyState == 4 && request.status == 200){
                //update page
                var array = JSON.parse(request.responseText);
                document.getElementById('team1score').innerHTML = array[0];
                document.getElementById('team2score').innerHTML = array[1];
                console.log('Scores updated');
                running = array[2];
                if(!running){ newelapsed = array[3];}
            }
    }
    request.open("GET",'public.php?action=plugin_scoreboard&data=' + id,true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
}

function updateElapsed(){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
            if(request.readyState == 4 && request.status == 200){
                //update page
                sent = true;
                console.log(request.responseText);
            }
    }
    console.log('Elapsed sent:' + elapsed);
    request.open("GET",'public.php?action=plugin_scoreboard&timer=' + id + '&elapsed=' + elapsed,true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
}

function updateTimer(){
    
    if(running == 1){
        
        if(starting){
            starting = false;
            start = new Date();
            elapsed = newelapsed;
            
        }
        sent = false;
        var now = new Date();
        
        change = now.getTime() - start.getTime();
        newtime = change + elapsed;
        var seconds = 600 - Math.floor(newtime/1000);
        if(seconds < 0){ seconds = 0; }
        var minutes = Math.floor(seconds/60);
        var excess_seconds = seconds - (minutes*60);
        if (excess_seconds == 0){ excess_seconds = "00"; }
        else if(excess_seconds < 10){ excess_seconds = "0" + excess_seconds; }
        document.getElementById('timer').innerHTML = minutes + ":" + excess_seconds;
    }else{
        
        if(!sent){
            elapsed = elapsed + change;
            updateElapsed();
            sent = true;
        }
        
        starting = true;
        
    }
    
}
