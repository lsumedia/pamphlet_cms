/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var player = document.getElementById('radio1');
var button = document.getElementById('stopstartbtn');
var status = document.getElementById('status');
var volume = document.getElementById('volume');
var mutebtn = document.getElementById('mutebtn');
var nowplaying = document.getElementById('nowplaying');


function updatePlayerStatus(){   
    var status = document.getElementById('status');
    if(player.paused){
        button.src = "plugins/radio/images/play.svg"; 
        status.innerHTML = "";
    }
    else{
        button.src = "plugins/radio/images/pause.svg";
        if(player.duration == "Infinity" || player.duration == "0"){
            status.innerHTML = "LIVE";
        }else{
            status.innerHTML = player.duration;
        }
    }
    if(player.muted || volume.value == 0){
        mutebtn.src = "plugins/radio/images/mute.svg";
    }else{
        if(volume.value > 50){
            mutebtn.src = "plugins/radio/images/volume_up.svg";
        }else{
            mutebtn.src = "plugins/radio/images/volume_down.svg";
        }
    }
   
}

function stopstart(){
    //Load player as an object
    var player =  document.getElementById('radio1');
    
    if(player.paused){
        player.play();
    }
    else{
        player.pause();
    }
    updatePlayerStatus();
}

function play(){
    player.play();
}
function pause(){
    player.pause();
}

function mutetog(){
    if(player.muted && volume.value != 0){
        player.muted = false;
    }else{
        player.muted = true;
    }
    updatePlayerStatus();
}
function playing(){
    var player =  document.getElementById('radio1');
    if(player.paused){
        return false;
    }
    return true;
}



function updateNowPlayingOld(){
    var nowplaying = document.getElementById('nowplaying');
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
            if(request.readyState == 4 && request.status == 200){
                //update page
                if(request.responseText.length > 0){
                    document.getElementById('nowplaying').innerHTML = "Now Playing: "  + request.responseText;
                    document.title = title + ": " + request.responseText;
                    console.log("Received updated now playing info");
                }else{
                    console.log("Received blank now playing info");
                }
            }
    }
    request.open("GET",'plugins/radio/nowplaying.php?url=' + nowplayinginfo,true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
    
}

function updateNowPlaying(){
    var stringed = JSON.stringify(nowplayinginfo);
    console.log(stringed);
    $.ajax({
        url: 'plugins/radio/nowplaying.php',
        type: 'POST',
        contentType: 'application/json',
        data: stringed,
        success: function(data){
            document.getElementById('nowplaying').innerHTML = "Now Playing: "  + data;
            document.title = title + ": " + data;
        },
        error: function(xhr, ajaxOptions, thrownError){
            document.getElementById('nowplaying').innerHTML = 'Error fetching song data: ' + thrownError;
            console.log('Song fetch error: ' + thrownError + ', ' + ajaxOptions);
        }
    });
}


player.onplaying = function(){
    updatePlayerStatus();  
};

player.onerror = function(){
    status.innerHTML = "ERROR";
};


volume.oninput = function(){
    player.volume = volume.value/100;
    if(player.muted){
        mutetog();
    }
    if(volume.value === 0 && !player.muted){
        mutetog();
    }
    updatePlayerStatus();
    
};

var intoInterval = setInterval(updateNowPlaying, "10000");
updateNowPlaying();

