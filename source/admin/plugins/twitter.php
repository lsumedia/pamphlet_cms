<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class twitter extends optionsPage {

    public $name = "plugin_twitter";
    public $title = "Twitter";

    function updatePage() {

        global $connection; //Import connection object

        require_once('plugins/twitter/twitterscreen.php');

        switch ($_GET['request']) {
            case 'setone':
                $id = $_GET['id'];
                //Get raw status from Twitter API
                $status = twitterList::getOne($id);
                $clean = twitterList::rawToClean($status);
                $raw_json = json_encode($status);
                file_put_contents('plugins/twitter/current.json', $raw_json);
                //Get OEmbed status from Twitter API
                echo json_encode($clean);
                break;
            case 'search':
                $term = $_GET['term'];
                echo json_encode(twitterList::getList($term));
                break;
            default:
                $term = $_GET['term'];
                twitterscroller::build($term);
        }
    }

    function configPage() {
        global $connection;
        require('plugins/twitter/twitterscreen.php');
        //Make inner central element
        ce::begin('style="width:60vw;"');
        //Make lists smaller
        echo "<style type=\"text/css\">.listWrapper{ min-height:0; margin-bottom:0; padding-bottom:0; }</style>";
        //Load in current string file
        $currentString = file_get_contents('plugins/twitter/current.json');
        $current = json_decode($currentString, 1);

        $clean = [];
        $clean[] = twitterList::rawToClean($current);

        $currentIL = new ajaxList($clean, 'currenttweet');
        $currentIL->title('Current live tweet');
        $currentIL->display();

        $term = $_GET['term'];
        $custom = $_GET['custom'];
        //echo "<form>";
        echo "<div class=\"form\">";
        
        echo "<div class=\"fieldRow\">";
        echo "<p>Load by URL</p>";
        echo "<input id=\"tweet_url_input\" placeholder=\"Tweet URL\" value=\"$custom\"></input>";
        echo "</div>";
        echo "<div class=\"fieldRow\">";
        echo "<p id='loadUrlError'></p>";
        echo "<button onclick=\"replaceTweetByUrl(document.getElementById('tweet_url_input').value);\" >Load</button>";
        echo "</div>";
        
        
        echo "<div class=\"fieldRow\">";
        echo "<p>Search term</p>";
        echo "<input id=\"term_input\" placeholder=\"Search term\" value=\"$term\"></input>";
        echo "</div>";
        echo "<div class=\"fieldRow\">";
        if (strlen($_GET['term']) > 0){
            $time = date('H:i:s');
            echo "<p>Updated tweets at $time</p>";
        }
        echo "<button onclick=\"window.location.href='?action=plugin_twitter&term=' + document.getElementById('term_input').value + '&custom=' + document.getElementById('tweet_url_input').value;\" >Search</button>";
        echo "</div>";
        
        
        
        echo "</div>";

        $current = file_get_contents('plugins/twitter/current.json');

        //echo "</form>";
        if (strlen($_GET['term']) > 0){
            $term = $_GET['term'];
            $data = twitterList::getCleanList($term);
            $list = new ajaxList($data, 'data');
            $list->title('Matching tweets');
            $list->display();
        }else {
            echo "<p>Enter a search term to see tweets</p>";
        }

        ce::end();
    }

    function displayPage() {
        global $connection;
    }

}

class twitterSetter extends uiElement {

    public $name = 'twitter_setter';

    public static function clientSide() {
        $url = actualLink() . '/request.php?update&action=plugin_twitter&request=setone&id=';
        ?>
//<script>
function replaceCurrentTweet(tweetID){
    //Function to pull some data   
    var request_url = '<?php echo $url ?>' + tweetID;
    var list_id = 'currenttweet';
    $.ajax({
        url: request_url,
        type : 'GET',
        contentType: 'application/json',
        success: function(data){
            var status = JSON.parse(data);
            var list_data_id = list_id + '_data';
            var dataSection = document.getElementById(list_data_id);
            dataSection.innerHTML = '[' + data + ']';
            list_change_page(list_id, list_data_id, 0);
        }
    });
}
function replaceTweetByUrl(url){
    if(url.length > 0 ){
        var tarray = url.split('/');
        var last = tarray[tarray.length -1];
        replaceCurrentTweet(last);
    }else{
        document.getElementById('loadUrlError').innerHTML = 'Invalid tweet URL';
    }
}
        <?php
    }

}

$pluginPages[] = new twitter();
