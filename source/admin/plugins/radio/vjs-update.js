/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



function updatePoster(){
    $.ajax({
        url: json_url,
        type : 'GET',
        contentType: 'application/json',
        success: function(data){
            var player = document.getElementsByTagName('video')[0];
            dataArray = JSON.parse(data);
            var poster = dataArray['poster'];
            console.log(poster);
            player.setAttribute('poster',poster);
            $('.vjs_poster').css({'background-image' : 'url(' + poster + ');'});
            /*
            nowplaying.innerHTML = 'Now Playing: ' + dataArray['nowplaying'];
            title.innerHTML = dataArray['plaintitle'];
            document.title = dataArray['plaintitle'] + ': ' + dataArray['nowplaying'];
            document.getElementById('player_outer_wrapper').style.backgroundImage = 'url(\'' + dataArray['poster'] + '\')';
            */
        }
    });
}

var poster_timer = window.setInterval(function(){ updatePoster(); }, 10000);