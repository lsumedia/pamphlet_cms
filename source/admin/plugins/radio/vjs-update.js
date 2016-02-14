/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function rgba(hex, a){
    rgb = hexToRgb(hex);
    return "rgba(" + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + a + ')';
}

function isWhite(hex){
    hex = hexToRgb(hex);
    if(hex.r == 255 && hex.g == 255 && hex.b == 255){
        return true;
    }
    return false;
}

function updateDetails(){
    $.ajax({
        url: json_url,
        type : 'GET',
        contentType: 'application/json',
        success: function(data){
            var player = document.getElementsByTagName('video')[0];
            dataArray = JSON.parse(data);
            var poster = dataArray['poster'];
            //console.log(poster);
            player.setAttribute('poster',poster);
            $('.vjs_poster').css({'background-image' : 'url(' + poster + ');'});
            document.title = dataArray['title'];
            //Control bar colour change section
            $('.vjs-control-bar').css("background-color","");
            if(dataArray['theme_colour'] != null){
                var hex = dataArray['theme_colour'];
                var colour = rgba(hex, 0.5);
                if(!isWhite(hex)){
                    $('.vjs-control-bar').css("background-color",colour);
                }
            }
            
            console.log('VJS Updated Info');
            /*
            nowplaying.innerHTML = 'Now Playing: ' + dataArray['nowplaying'];
            title.innerHTML = dataArray['plaintitle'];
            document.title = dataArray['plaintitle'] + ': ' + dataArray['nowplaying'];
            document.getElementById('player_outer_wrapper').style.backgroundImage = 'url(\'' + dataArray['poster'] + '\')';
            */
        }
    });
}

updateDetails();
var poster_timer = window.setInterval(function(){ updateDetails(); }, 10000);