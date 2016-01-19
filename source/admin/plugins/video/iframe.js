/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if(channelURL){
    function checkContent(){
        var request = new XMLHttpRequest();
	request.onreadystatechange = function(){
		if(request.readyState == 4 && request.status == 200){
                    var string = request.responseText;
                    var array = JSON.parse(string);
                    //console.log('Old:' + nowPlayingId + ' New:' + array['id']);
                    //update page
                    if(nowPlayingId != array['id']){
                        //console.log('Content has changed, reloading frame');
                        document.location.reload(true);   
                    }
                    nowPlayingId = array['id'];
		}
	}
	request.open("GET",channelURL ,true);
	request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	request.send();
    }
    
    interval = window.setInterval(checkContent,10000);
}

