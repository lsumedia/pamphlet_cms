/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function loadAllTweets(){
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
    request.open("GET",'plugins/twitter/autofeed.php?term=',true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
}