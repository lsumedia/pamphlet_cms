/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function updateScores(){
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
    request.open("GET",'public.php?action=plugin_scoreboard&data=' + id,true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
}