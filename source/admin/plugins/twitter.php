<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class twitter extends optionsPage {

    public $name = "plugin_twitter";
    public $title = "Twitter";

    function setup() {
        
    }

    function displayPage() {

        global $connection; //Import connection object

        require_once('plugins/twitter/twitterscreen.php');

        switch ($_GET['request']) {
            case 'getone':
                $id = $_GET['id'];
                echo json_encode(twitterList::getOne($id));
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
        $user = $current['user'];
        $author = $user['name'];
        $screen_name = '@' . $user['screen_name'];
        $dpurl = $user['profile_image_url_https'];
        $dp = "<img src=\"$dpurl\" />";
        $text = $current['text'];
        $posted = $current['created_at'];
        $new = ['' => $dp, 'Author' => $author, 'Handle' => $screen_name, 'Text' => $text, 'Date' => $posted];
        $clean[] = $new;

        $currentIL = new ajaxList($clean, 'currenttweet');
        $currentIL->title('Current live tweet');
        $currentIL->display();

        $term = $_GET['term'];
        //echo "<form>";
        echo "<div class=\"form\">";
        echo "<div class=\"fieldRow\">";
        echo "<p>Search term</p>";
        echo "<input id=\"term_input\" placeholder=\"Search term\" value=\"$term\"></input>";
        echo "</div>";
        echo "<div class=\"fieldRow\">";
        echo "<button onclick=\"cm_loadPage('plugin_twitter&term=' + document.getElementById('term_input').value);\" >Search</button>";
        echo "</div>";
        echo "</div>";

        $current = file_get_contents('plugins/twitter/current.json');

        //echo "</form>";
        if (isset($_GET['term'])) {
            $term = $_GET['term'];
            $data = twitterList::getCleanList($term);
            $list = new ajaxList($data, 'data');
            $list->title('Matching tweets');
            $list->display();
        } else {
            echo "Please enter a search term";
        }

        ce::end();
    }

    function updatePage() {
        global $connection;
    }

}

class twitterSetter extends uiElement {

    public $name = 'twitter_setter';

    public static function clientSide() {
        $url = actualLink() . 'public.php?action=plugin_twitter&request=getone&id=';
        ?>
<script>
function replaceCurrentTweet(tweetID){
    //Function to pull some data   
    var request_url = '<?php echo $url ?>' + tweetID;
    $.ajax({
        url: request_url,
        type : 'GET',
        contentType: 'application/json',
        success: function(data){
            var featured = JSON.parse(data);
            var html = "";
            for(var i = 0; i < featured.length; i++){
                var item = featured[i];
                var id = item['id'];
                var title = item['title'];
                var poster = item['poster'];

                var type = player_id == channel['id'] ? "active" : "" ;
                html += "<li class=\"" + type + "\" onclick=\"loadVideo('"+ id + "');\">";
                html += "<div class=\"mask\"><img class=\"channel_thumb\" src=\"" + poster + "\"></div>";
                html += "<div class=\"channel_text\">";
                html += "<span class=\"title\">" + title + "</span><br />";
                if(item['nowplaying']){ html += "<span class=\"programme\">" + channel['nowplaying'] + "</span>"; }
                html += "</div>";
                html += "</li>";

            }
            document.getElementById('related_list').innerHTML = html;
        }
});
}
</script>
        <?php
    }

}

$pluginPages[] = new twitter();
