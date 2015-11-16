/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var tweets = null;
var tweetIndex;
var tweet;
var batchIndex = 0;

function loadAllTweets(){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
            if(request.readyState == 4 && request.status == 200){
                //update page
                tweets = JSON.parse(request.responseText);
                tweetIndex = 0;
                batchIndex++;
                console.log('Loaded batch ' + batchIndex);
                if(tweets['statuses'].length > 0){
                    loadNewTweet();
                }else{
                    console.log('No results received, trying again in 10 seconds');
                }
            }
    }
    request.open("GET",'plugins/twitter/autofeed.php?term=' + term,true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send();
}

function loadNewTweet(){
    if(tweet = tweets['statuses'][tweetIndex]){
        document.getElementById('result').innerHTML = tweet['text'];
        document.getElementById('name').innerHTML = tweet['user']['name'];
        document.getElementById('handle').innerHTML = '@' + tweet['user']['screen_name'];
        document.getElementById('profilepic').innerHTML = "<img src=\"" + tweet['user']['profile_image_url'] + "\">";
        if(tweet['entities']['media'] != null){
            var url = tweet['entities']['media'][0]['media_url'];
            console.log("Contains image:" + url);
            document.getElementById('bgimage').innerHTML = "<img src=\"" + url + "\">";
           
        }else{
            document.getElementById('bgimage').innerHTML = "<img style=\"transform:translate(0,-30%);\" src=\"/live/images/hustings.jpg\">";
        }
        console.log('Loaded tweet ' + tweetIndex + ' from batch ' + batchIndex);
        tweetIndex++;
    }else{
        tweetIndex = 0;
        loadAllTweets();
    }
}