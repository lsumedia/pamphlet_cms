/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var team1score;
var team2score;
var trunning;
var telapsed;
var quarter;


function updateScore(){
    
}

function toggleTimer(){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
            if(request.readyState == 4 && request.status == 200){
               console.log(request.responsetText);
            }
    }
    request.open("GET",'request.php?update&action=plugin_scoreboard&control=' + id + '&timer',true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
    if(trunning == 0){
        document.getElementById('timerStopStart').innerHTML = "Stop";
    }else{
        document.getElementById('timerStopStart').innerHTML = "Start";
    }
}

function resetTimer(){
    
}

function updateQuarter(){
    var quarter = document.getElementById('quarter').value;
    
}


function updateScores(){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
            if(request.readyState == 4 && request.status == 200){
                //update page
                var array = JSON.parse(request.responseText);
                team1score = array[0];
                team2score = array[1];
                trunning = array[2];
                telapsed = array[3];
                quarter = array[4];
                document.getElementById('currentscore_1').value = team1score;
                document.getElementById('currentscore_2').value = team2score;
                //document.getElementById('team2score').innerHTML = array[1];
                console.log('Scores updated');
                msToTimer(telapsed);
                
               
                if(trunning == 1){
                    document.getElementById('timerStopStart').innerHTML = "Stop";
                }else{
                    document.getElementById('timerStopStart').innerHTML = "Start";
                }
            }
    }
    request.open("GET",'public.php?action=plugin_scoreboard&data=' + id,true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
}

function msToTimer(milliseconds){
    var seconds = 600 - Math.floor(milliseconds/1000);
    var minutes = Math.floor(seconds/60);
    var excess_seconds = seconds - (minutes*60);
    if (excess_seconds == 0){ excess_seconds = "00"; }
    else if(excess_seconds < 10){ excess_seconds = "0" + excess_seconds; }
    document.getElementById('timerdisplay').value = minutes + ":" + excess_seconds;
}
var update = setInterval(updateScores,1000);