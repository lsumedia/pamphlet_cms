/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function updateScores(){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
            if(request.readyState == 4 && request.status == 200){
                //update page
                var array = JSON.parse(request.responseText);
                document.getElementById('team1score').innerHTML = array[0];
                document.getElementById('team2score').innerHTML = array[1];
                console.log('Scores updated');
            }
    }
    request.open("GET",'public.php?action=plugin_scoreboard&data=' + id,true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
}

