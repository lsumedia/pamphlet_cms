<?php
/**
 * Channel manager
 * 
 * This class deals with the top-level "channels"
 * Channels can point to either a live stream, on demand video or holding screen
 * The channel ID constraint is stored as an integer
 */

/** Print page source optimised for iFrames including videoJS include
 * 
 * @param type $title string
 * @param type $code string
 */
function iframeOutput($title,$code){
    require_once('video/videojs/videojs.php');
    html::start();
    html::css("plugins/video/iframe.css");
    html::title($title);
    html::lockZoom();
    echo "<style>body{ margin:0; padding:0; width:100%; height:100%; overflow:hidden;} .html5vid, iframe{ width: 100%; width:100vw !important; height:100%; height:100vh !important; min-width: 300px; min-height:200px;}</style>";
    videojs::init();
    html::endHead();
    echo $code;
    videojs::run();
    html::end();
}

class source{
    public $src;
    public $type;
    public $res;
    
    public function __construct($src,$type,$res) {
        $this->src = $src;
        $this->type = $type;
        $this->res = $res;
    }
}

class video{
    /* Video ID code */
    public $id;

    /* TECH DATA */
    
    /* Array of source objects only */
    public $sources = array();
    /* Video type to determine which mediaPlayer to use */
    public $type;
    /* Generated source code for the video */
    public $source;
    /* Poster image URL for video */
    public $poster;

    /* OTHER METADATA */
    
    /* Video title */
    public $title;
    /* Video description */
    public $description;
    /* Publish date */
    public $date;
    /* Video tags for easier searching */
    public $tags;
    
    /**
     * 
     * @param int $id
     * @param string $type
     * @param array $sources
     * @param string $source
     * @param string $poster
     * @param string $title
     * @param string $description
     * @param date $date
     * @param string $tags
     */
    public function __construct($id,$type,$sources,$source,$poster,$title,$description,$date,$tags){
        $this->id = $id;
        
        $this->type = $type;
        $this->sources = $sources;
        $this->source = $source;
        $this->poster = $poster;
        
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->tags = $tags;
        
    }
    
}
/**
 * Extendable class for defining media player modules
 * 
 * New media players MUST be an extension of this class or they will not be detected
 */
class mediaPlayer {
    
    /* $name: unique and machine-friendly (lowercase, no spaces) name for the player module */
    public $name;
    /* $title: human-friendly title for the player module */
    public $title;
    /* $supported: video player supported sources, array of MIME types as strings */
    public $supported = array();
    /* $properties: defines a set of text properties in key/pair format which may be used by the player */
    public $properties = array();
    /**
     * build
     * Generates HTML source code for the player for use in an iframe.
     * Return as a string.
     * Return false for invalid input.
     * 
     * @param type $video
     * -Video object to generate a player from
     * @param type $setup
     * - Any further setup conditions, may be player-specific
     */
    public static function build($video,$setup){
        return false;
    }
}

/**
 * Channel manager class
 * 
 * Functions for adding, editing and displaying channels
 */
class manager extends optionsPage{
    
    public $name = "plugin_videomanager";
    public $title = "Channel Manager";
    
    /**
     * configPage function
     * @global type $connection
     * Pamphlet configuration page for video manager
     * Two pages: List/add channel, edit channel
     */
    public function configPage(){
        
        global $connection;
        ce::begin("ce-medium");
        if($channel = filter_input(INPUT_GET,"edit")){
            
            backButton($this->name);
            
            if($mstmt = $connection->prepare("SELECT title,type,live,vod,cover,visible,thumbnail FROM plugin_videomanager WHERE id=?")){
                $mstmt->bind_param("i",$channel);
                $mstmt->execute();
                $mstmt->bind_result($title,$type,$live,$vod,$cover,$visible,$thumbnail);
                $mstmt->fetch();
                $mstmt->close();
                
                $form = new ajaxForm("channelEditForm", $this->name . "&edit=" . $channel, "POST");
                $form->formTitle("Edit channel");
                $form->labeledInput("title", "text", $title, "Channel name");
                $form->checkBox("visible", $visible, "Visible in controller");
                $form->inputWithButton("thumbnail", $thumbnail, "Thumbnail image", "browseServer()", "Upload");
                $form->kpSelector("type", self::kpVideoTypes(), $type, "Content to display");
                $form->kpSelector("live", live::kpStreams(), $live, "Live stream");
                $form->kpSelector("vod", videos::kpVideos(), $vod, "On-demand video");
                $form->kpSelector("cover", cover::kpCovers(), $cover, "Holding screen");
                ajaxForm::startOptionalSection("delsec", "Delete button (don't!)");
                $form->otherActionButton("deletebutton", "Delete channel", "&delete=$channel");
                ajaxForm::endOptionalSection();
                $form->lockedInput(actualLink() . "/public.php?action=$this->name&iframe=$channel", "External embed URL");
                $form->submit("Update channel");
                
            }else{
                echo "Error loading database";
            }
            
        }else{
            $form = new ajaxForm("videoManagerForm", $this->name, "POST");
            $form->formTitle("New channel");
            $form->labeledInput("title", "text", "", "Stream name");
            $form->checkBox("visible", "1", "Visible in controller");
            $form->inputWithButton("thumbnail", "", "Thumbnail image", "browseServer()", "Upload");
            $form->kpSelector("type", manager::kpVideoTypes() , "", "Content to display");
            $form->kpSelector("live", live::kpStreams(), "", "Live stream");
            $form->kpSelector("vod", videos::kpVideos() , "", "On-demand video");
            $form->kpSelector("cover", cover::kpCovers(), "", "Holding screen");
            $form->submit("Add channel");
            
            $slist = new multiPageList(NULL,"streamlist");
            $slist->title("Active channels");
            
            $query = "SELECT id,title,type,visible FROM $this->name";
            $query2 = "SELECT vm.id,vm.type,l.title,v.title,c.title FROM plugin_videomanager vm, plugin_vod v, plugin_live l, plugin_cover c WHERE vm.live = l.id OR vm.vod = v.id OR vm.cover = c.id;";
            if($vstmt = $connection->prepare($query)){
                $vstmt->execute();
                $vstmt->bind_result($id,$title,$type,$visible);
                while($vstmt->fetch()){
                    $onclick = "cm_loadPage('$this->name&edit=$id');";
                    $status = ($visible == 1)? "Yes" : "No";
                    $item = array("Title" => $title, "Type" => self::kpVideoTypes()[$type], "Live" => $status, "onclick" => $onclick);
                    $slist->addObject($item);
                }
                $slist->display($this->name);
            }else{
                echo "Error accessing database!";
            }
            
        }
        ce::end();
    }
    /**
     * displayPage function
     * Pamphlet display function for videomanager
     * Two possible options depending on get request
     * If ?list is set:
     * Generates AJAX response containing all active channels and 
     * @global type $connection
     */
    public function displayPage() {
        
        global $connection;
        
        if(isset($_GET['list'])){
            
            //TODO - AJAX response for channel selector
            $channels = self::activeChannels();
            echo json_encode($channels);
            
        }
        else if(isset($_GET['iframe'])){
            $iframeid = filter_input(INPUT_GET,"iframe");
            $video = self::kpChannel($iframeid);
            iframeOutput($video['title'], $video['source']);
        }else{
  
            if(isset($_GET['id'])){
                $playerid = filter_input(INPUT_GET,"id");
            }else{
                $playerid = 1;
            }
            
            $video = self::kpChannel($playerid);
            $iframe_link = actualLink() . "/public.php?action=$this->name&iframe=$playerid";
            $video['iframe'] = $iframe_link;
            //Displays memory for optimisation processes
            //$video['description'] .= "<p>Memory usage: " . memory_get_peak_usage() . "<br />Stream ID: " . $video['id'] . "</p>";
            echo json_encode($video);
        }
    }
    /**
     * 
     * @global type $connection
     * @return type
     * 
     * Update page for video manager
     * 
     * Edit/add/delete channel
     */
    public function updatePage(){
       global $connection;
        
        $title = filter_input(INPUT_POST,"title");
        $type = filter_input(INPUT_POST,"type");
        $live = filter_input(INPUT_POST,"live");
        $vod = filter_input(INPUT_POST,"vod");
        $cover = filter_input(INPUT_POST,"cover");
        $visible = filter_input(INPUT_POST,"visible");
        $thumbnail = filter_input(INPUT_POST,"thumbnail");
        
        
        if(isset($_GET['delete'])){
            
            $delete = filter_input(INPUT_GET,"delete");
            block(2);
            
            $bdstmt = $connection->prepare("DELETE FROM $this->name WHERE id=?");
            $bdstmt->bind_param("i",$delete);
            if($bdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete channel: $bdstmt->error";
            }
        }
        else if(isset($_GET['edit'])){
            
            block(2);
            $edit = filter_input(INPUT_GET,"edit");
            //Editing existing post
            $bstmt = $connection->prepare("UPDATE $this->name SET title=?, type=?, live=?, vod=?, cover=?, visible=?, thumbnail=? WHERE id=?");
            $bstmt->bind_param("sssssisi",$title,$type,$live,$vod,$cover,$visible,$thumbnail,$edit);
            if($bstmt->execute()){
                echo "Saved changes";
                return;
            }
            echo "Error saving changes: $bstmt->error";
            return;
            
        }
        else{
            block(2);
            //Creating new channel
            if(!$title){
                echo "Please enter a title";
                return;
            }else{
                $nbstmt = $connection->prepare("INSERT INTO $this->name (title,type,live,vod,cover,visible,thumbnail) VALUES (?,?,?,?,?,?,?);");
                $nbstmt->bind_param("sssssis",$title,$type,$live,$vod,$cover,$visible,$thumbnail);
                if($nbstmt->execute()){
                    echo "reload";
                    return;
                }
                echo "Error adding post: $nbstmt->error";
            }
        }
    }
    /**
     * 
     * @global type $connection
     * @param type $playerid - Channel to be fetched
     * @return boolean, array
     * 
     * Return channel details in key-pair array or false if channel not found
     * 
     * Array values depend on the individual generator functions
     * live::getStream()
     * videos::getVideo()
     * cover::getImage()
     * 
     * Standard output includes
     * 
     * 
     */
    public static function kpChannel($playerid){
        global $connection;
        
         if($stmt = $connection->prepare("SELECT id,type,live,vod,cover FROM plugin_videomanager WHERE id=?")){
            $stmt->bind_param("s", $playerid);
            $stmt->execute();
            $stmt->bind_result($id,$type,$live,$vod,$cover);
            $stmt->fetch();
            $stmt->close();
        
            switch($type){
                case "live":
                    $player_content = live::getStream($live);
                    break;
                case "vod":
                    $player_content = videos::getVideo($vod);
                    break;
                case "cover":
                    $player_content = cover::getImage($cover);
                    break;
            }
            return $player_content;
        }else {
            echo "Error - database request failed";
            return false;
        }
    }
    /**
     * 
     * @param type $playerid - ID of channel to be displayed
     * 
     * Prints HTML for a given channel for standalone player or iFrame embed
     */
    
    /**
     * 
     * @return string
     * Possible video types for ajaxForm select input
     */
    static function kpVideoTypes(){
        /* Key-pair array of available video types */
        $types = array("live" => "Live stream", "vod" => "On-demand video", "cover" => "Holding screen");
        return $types;
    }
    
    /**
     * 
     * @global type $connection
     * @return type array
     * 
     * Returns array of channels that are currently set as live
     * 
     * id = channel id
     * title = channel title
     * thumbnail = channel thumbnail
     * programme = active programme title
     * 
     */
    static function activeChannels(){
        global $connection;
        
        $stmt = $connection->prepare("SELECT id,title,type,live,vod,cover,thumbnail FROM plugin_videomanager WHERE visible=1;");
        $stmt->execute();
        $stmt->bind_result($id,$title,$type,$live,$vod,$cover,$thumbnail);
        $channels = array();
        while($stmt->fetch()){
            $channels[] = array("id" => $id, "title" => $title, "thumbnail"=> $thumbnail);
        }
        foreach($channels as $key => $channel){
            $id = $channel["id"];
            $data = self::kpChannel($id);
            $channels[$key]["programme"] = $data['title'];
            if($data['poster']){
                $channels[$key]['thumbnail'] = $data['poster'];
            }
        }
        return $channels;
        
    }
    
}

$pluginPages[] = new manager();

class videos extends optionsPage{
    
    public $name = "plugin_vod";
    public $title = "On Demand Videos";
    
    public function configPage(){
        global $connection;
        ce::begin("ce-medium;");
        if($video = filter_input(INPUT_GET,"edit")){
            
            backButton($this->name);
            $details = self::getVideo($video);
            $editForm = new ajaxForm("editVideoForm", $this->name . "&edit=" . $video, "POST");
            $editForm->formTitle("Edit video");
            $editForm->labeledInput("title", "text", $details['title'], "Title");
            $editForm->kpSelector("type", videos::kpTypes(), $details['type'], "Video type");
            $editForm->labeledInput("url", "text", $details['url'], "Video URL/ID");
            ajaxForm::startOptionalSection("embed","Embed code (Custom code)");
            $editForm->plainText("code", $details['code'], "Custom embed code");
            ajaxForm::endOptionalSection();
            $editForm->labeledInput("length", "text", $details['length'], "Video length");
            $editForm->labeledInput("date", "date", $details['date'], "Date posted");
            $editForm->labeledInput("poster", "text", $details['poster'], "Poster URL");
            $editForm->largeText("description", $details['description'], "Description");
            $editForm->lockedInput(actualLink() . "/public.php?action=$this->name&id=$video", "External embed URL");
            $editForm->otherActionButton("deleteVideo", "Delete video", "&delete=$video");
            $editForm->submit("Save changes");
        }else{
        
            $form = new ajaxForm("newItemForm", $this->name, "POST");
            $form->formTitle("New video");
            $form->labeledInput("title", "text", "", "Title");
            $form->kpSelector("type", videos::kpTypes(), $current, "Video type");
            $form->labeledInput("url", "text", "", "Video URL/ID");
            ajaxForm::startOptionalSection("embed","Embed code (Custom code)");
            $form->plainText("code", "", "Custom embed code");
            ajaxForm::endOptionalSection();
            $form->labeledInput("length", "text", "", "Video length");
            $nowdate = date("Y-m-d");
            $form->labeledInput("date", "date", $nowdate, "Date posted");
            $form->labeledInput("poster", "text", "", "Poster URL");
            $form->largeText("description", "", "Description");
            $form->submit("Add new item");
            
            $videos = self::allVideos();
            $list = new multiPageList($videos,"videoList");
            $list->title("All videos");
            
            $list->display($this->name);
        }
        ce::end();
    }
    
    public function updatePage(){
        global $connection;
        
        $title = filter_input(INPUT_POST,"title");
        $length = filter_input(INPUT_POST,"length");
        $date = filter_input(INPUT_POST,"date");
        $description = content("description");
        $type = filter_input(INPUT_POST,"type");
        $url = filter_input(INPUT_POST,"url");
        $code = content("code");
        $poster = filter_input(INPUT_POST, "poster");
       
        
        if(isset($_GET['delete'])){
            
            $delete = filter_input(INPUT_GET,"delete");
            block(2);
            
            $bdstmt = $connection->prepare("DELETE FROM $this->name WHERE id=?");
            $bdstmt->bind_param("i",$delete);
            if($bdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete video: $bdstmt->error";
            }
        }
        else if(isset($_GET['edit'])){
            
            block(2);
            $edit = filter_input(INPUT_GET,"edit");
            //Editing existing post
            if($bstmt = $connection->prepare("UPDATE $this->name SET title=?, length=?, date=?, description=?, type=? ,url=?, source=?, poster=?  WHERE id=?")){
                $bstmt->bind_param("ssssssssi",$title,$length,$date,$description,$type,$url,$code,$poster,$edit);
                if($bstmt->execute()){
                    echo "Saved changes";
                    return;
                }
            }
            echo "Error saving changes: $bstmt->error";
            return;
            
        }
        else{
            block(2);
            //Creating new video
            if(!$title){
                echo "Please enter a title";
                return;
            }else{
                $nbstmt = $connection->prepare("INSERT INTO $this->name (title,length,date,description,type,url,source,poster) VALUES (?,?,?,?,?,?,?,?);");
                $nbstmt->bind_param("ssssssss",$title,$length,$date,$description,$type,$url,$source,$poster);
                if($nbstmt->execute()){
                    echo "reload";
                    return;
                }
                echo "Error adding post: $nbstmt->error";
            }
        }
    }
    
    public function displayPage(){
        if(isset($_GET['list'])){
            echo json_encode(self::listVideos());
        }else if(isset($_GET['id'])){
            //html::div("player_container","player1");
            $videoid = filter_input(INPUT_GET,"id");
            $video = self::getVideo($videoid);
            iframeOutput($video['title'], $video['source']);
        }
    }

    public static function kpVideos(){
        global $connection;
        if($stmt = $connection->prepare("SELECT id,title FROM plugin_vod ORDER BY id DESC")){
            $stmt->execute();
            $videos = array();
            $stmt->bind_result($id,$vtitle);
            $videos['null'] = "--";
            while($stmt->fetch()){
                $videos[$id] = $vtitle;
            }
            return $videos;
        }else{
            return false;
        }
    }
    
    public static function allVideos(){
        /* Returns all video information in a multi-dimensional array */
        global $connection;
        if($stmt = $connection->prepare("SELECT id,title,type,length,date FROM plugin_vod ORDER BY id DESC")){
            $stmt->execute();
            $videos = array();
            $stmt->bind_result($id,$vtitle,$type,$length,$date);
            while($stmt->fetch()){
                 $onclick = "cm_loadPage('plugin_vod&edit=$id');";
                $videos[] = array("Title" => $vtitle, "Type" => self::kpTypes()[$type], "Length" => $length, "Date posted" => $date, "onclick" => $onclick);
            }
            return $videos;
        }else{
            return false;
        }
    }
    /**
     * 
     * @global type $connection
     * @return array
     * 
     * Returns all video information for a JSON array
     */
    public static function listVideos(){
        /* Returns all video information in a multi-dimensional array ordered by date */
        global $connection;
        if($stmt = $connection->prepare("SELECT id,title,url,type,length,date FROM plugin_vod ORDER BY date desc")){
            $stmt->execute();
            $ids = array();
            $videos = array();
            $stmt->bind_result($id,$vtitle,$url,$type,$length,$date);
            while($stmt->fetch()){
                $ids[] = $id;
            }
            foreach($ids as $id){
                //Writes other videos to array
                $videos[] = self::getVideo($id);
            }
            return $videos;
        }else{
            return false;
        }
    }

    /*
    public static function kpTypes(){
        return array("html5" => "HTML 5", "iframe" => "IFrame Embed", "custom" => "Custom embed code", "youtube" => "YouTube ID");
    } */
    
    public static function getPlayerTypes(){
        $players = array();
        foreach(get_declared_classes() as $class){
            if(is_subclass_of($class, 'mediaPlayer')){
                $players[] = new $class();
            }
        }
        return $players;
    }
    
    public static function kpTypes(){
        $players = self::getPlayerTypes();
        $types = array();
        //$types['--'] = var_dump($players) . ' modules found';
        foreach($players as $player){
            $name = $player->name;
            $title = $player->title;
            $types[$name] = $title;
        }
        return $types;
    }
    
    public static function getVideo($id){
        global $connection;
        
        $video = array();
        $video['id'] = "vod_" . $id;
        $video['video_id'] = $id;
        
        
        if($vstmt = $connection->prepare("SELECT title,length,date,description,type,url,source,poster FROM plugin_vod WHERE id=?")){
            $vstmt->bind_param("i",$id);
            $vstmt->execute();
            $vstmt->bind_result($title,$length,$date,$desc,$type,$url,$source,$poster);
            $vstmt->store_result();
            $vstmt->fetch();

            $video['title'] = $title;
            $video['date'] = $date;
            $video['description'] = $desc;
            $video['type'] = $type;
            $video['sources'] = array();
            $video['code'] = $source;
            $video['poster'] = $poster;
            
            $video['url'] = $url;
            $source = new source($url,'video/mp4','720');
            $video['sources'][] = $source;
            
            $players = self::getPlayerTypes();
            foreach($players as $player){
                if($player->name == $type){
                    $video = $player->build($video,$setup);
                }
            }
            return $video;
            /*
            switch($type){
                case "iframe":
                    $video['source'] = self::iframeCode($url);
                    break;
                case "html5":
                    $video['source'] = self::htmlCode($url,$poster);
                    break;
                case "youtube":
                    $video['source'] = self::ytCode($url);
                    if(!$poster){ 
                        $video['poster'] = "http://img.youtube.com/vi/$url/maxresdefault.jpg";
                    }
                    break;
                default:
                    $video['source'] = $source;
                    break;
            }
            */
            //return $video;
        }else{
            echo "Error - VOD database request failed!";
        }
    }
    
    public static function generateCorrectCode(){
        
    }
    
    public static function iframeCode($url){
        return "<iframe frameborder=\"0\" class=\"vidplayer\" width=\"100%\" height=\"100%\" allowfullscreen src=\"$url\"></iframe>";
    }
    public static function htmlcode($url,$poster){
        return "<video class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$poster\" controls autoplay data-setup='{\"techOrder\": [\"html5\",\"flash\"]}'>" . "<source src=\"$url\" type=\"video/mp4\">" . "Your browser does not support the video tag" . "</video>";
    }
    public static function ytCode($url){
        return "<iframe frameborder=\"0\" class=\"vidplayer\" width=\"100%\" height=\"100%\" allowfullscreen src=\"https://www.youtube.com/embed/$url?autoplay=1\"></iframe>";
    }
    
}

$pluginPages[] = new videos();

class live extends optionsPage{
    
    public $name = "plugin_live";
    public $title = "Live Streaming";
    
    public function configPage(){
        global $connection;
        ce::begin("ce-medium;");
        if($stream = filter_input(INPUT_GET,"edit")){
            if($stmt = $connection->prepare("SELECT title,type,mobile,url,source,description,cover FROM plugin_live WHERE id=?")){
                $stmt->bind_param('i',$stream);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($title,$type,$mobile,$url,$source,$description,$cover);
                $stmt->fetch();
                
                backButton($this->name);
                $editForm = new ajaxForm("editVideoForm", $this->name . "&edit=" . $stream, "POST");
                $editForm->formTitle("Edit stream");
                $editForm->labeledInput("title", "text", $title, "Title");
                $editForm->kpSelector("type", self::kpStreamTypes(), $type, "Stream type");
                $editForm->kpSelector("mobile", self::kpOtherStreams($stream), $mobile, "Mobile stream");
                $editForm->labeledInput("url", "text", $url, "Stream URL");
                $editForm->kpSelector("cover", cover::kpCovers(), $cover, "Loading screen");
                 ajaxForm::startOptionalSection("sourceSection", "Custom code");
                $editForm->plainText("source", $source, "Custom embed code");
                ajaxForm::endOptionalSection();
                $editForm->largeText("description", $description, "Description");
                $editForm->lockedInput(actualLink() . "/public.php?action=$this->name&id=$stream", "External embed URL");
                $editForm->otherActionButton("deleteVideo", "Delete stream", "&delete=$stream");
                $editForm->submit("Save changes");
            }else{
                echo "Error accessing database";
            }
        }else{
        
            $form = new ajaxForm("newItemForm", $this->name, "POST");
            $form->formTitle("New stream");
            $form->labeledInput("title", "text", "", "Title");
            $form->kpSelector("type", self::kpStreamTypes(), "custom", "Stream type");
            $form->kpSelector("mobile", self::kpStreams(), "", "Mobile stream");
            $form->labeledInput("url", "text", "", "Stream URL");
            $form->kpSelector("cover", cover::kpCovers(), "", "Loading screen");
            ajaxForm::startOptionalSection("sourceSection", "Custom code");
            $form->plainText("source", "", "Custom embed code");
            ajaxForm::endOptionalSection();
            $form->largeText("description", $details['description'], "Description");
            $form->submit("Add new item");
            
            $videos = self::allStreams();
            $list = new multiPageList($videos,"videoList");
            $list->title("All streams");
            
            $list->display($this->name);
        }
        ce::end();
    }
    
    public function updatePage(){
        global $connection;
        
        $title = filter_input(INPUT_POST,"title");
        $type = filter_input(INPUT_POST,"type");
        $mobile = filter_input(INPUT_POST,"mobile");
        $url = filter_input(INPUT_POST,"url");
        $source = content("source");
        $description = content("description");
        $cover = filter_input(INPUT_POST,"cover");

        if(isset($_GET['delete'])){
            
            $delete = filter_input(INPUT_GET,"delete");
            block(2);
            
            $bdstmt = $connection->prepare("DELETE FROM $this->name WHERE id=?");
            $bdstmt->bind_param("i",$delete);
            if($bdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete stream: $bdstmt->error";
            }
        }
        else if(isset($_GET['edit'])){
            block(2);
            
            $edit = filter_input(INPUT_GET,"edit");
            //Editing existing post
            $equery = "UPDATE $this->name SET title=?, type=?, mobile=?, url=?, source=?, description=?, cover=?  WHERE id=?";
            if($estmt = $connection->prepare($equery)){
                $estmt->bind_param("ssisssii",$title,$type,$mobile,$url,$source,$description,$cover,$edit);
                if($estmt->execute()){
                    echo "Saved changes";
                    $estmt->close();
                    return;
                }
            }
            echo "Error saving changes: $estmt->error";
            return;
            
        }
        else{
            block(2);
            //Creating new stream
            if(!$title){
                echo "Please enter a title";
                return;
            }else{
                $nbstmt = $connection->prepare("INSERT INTO $this->name (title,type,mobile,url,source,description,cover) VALUES (?,?,?,?,?,?,?);");
                $nbstmt->bind_param("ssisssi",$title,$type,$mobile,$url,$source,$description,$cover);
                if($nbstmt->execute()){
                    echo "reload";
                    return;
                }
                echo "Error adding post: $nbstmt->error";
            }
        }
    }
    
    public function displayPage(){
        if(isset($_GET['list'])){
            echo json_encode(self::listStreams());
        }else if(isset($_GET['id'])){
            $streamid = filter_input(INPUT_GET,"id");
            $stream = self::getStream($streamid);
            iframeOutput($stream['title'], $stream['source']);
        }else{
            echo "Live stream iFrame generator<br />", PHP_EOL;
            echo "Valid requests:<br />";
            echo "&list : Returns list of streams in JSON format<br />";
            echo "&id=id : Returns a particular stream's source code";
        }
    }
    
    public static function kpStreamTypes(){
        return array("hds" => "HDS", "hls" => "HLS", "rtmp" => "RTMP","icecast" => "Radio", "custom" => "Custom embed code" );
    }
    public static function getStream($id){
        global $connection;
        if($stmt = $connection->prepare("SELECT title,type,url,mobile,source,description,cover FROM plugin_live WHERE id=?")){
            $stmt->bind_param("i",$id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($title, $type, $url, $mobile, $code, $description,$cover);
            $stmt->fetch();
            
            
            $cover_url = cover::getImage($cover)['url'];
            if($mobile && onMobile()){
              $mobStream = self::getStream($mobile);
              $source = $mobStream['source'];
              $description = $mobStream['description'];
            }else{
            
                switch($type){
                    case "hds":
                        $source = self::hdsStream($url,$cover_url);
                        break;
                    case "hls":
                        $source = self::hlsStream($url,$cover_url);
                        break;
                    case "rtmp":
                        $source = self::rtmpStream($url,$cover_url);
                        break;
                    case "icecast":
                        require_once('plugins/radio/player.php');
                        $source = self::icecastStream($url, $cover_url, $code, $title);
                        $title = radioPlayer::getNowPlaying($code, $title);
                        break;
                    default:
                        $source = $code;
                        break;
                }
            }
            
            return array("id" => "live_$id", "title" => $title, "type" => $type, "mobile" => $mobile, "code" => $code, "url" => $url, "description" => $description, "source" => $source, "cover" => $cover);
        }else{
            return false;
        }
    }
    public static function kpStreams(){
        global $connection;
        if($stmt=$connection->prepare("SELECT id,title FROM plugin_live")){
            $stmt->execute();
            $stmt->bind_result($id,$title);
            $streams = array();
            $streams['null'] = "--";
            while($stmt->fetch()){
                $streams[$id] = $title;
            }
            return $streams;
            
        }else{
            return false;
        }
    }
    public static function kpOtherStreams($exclude){
        $array = self::kpStreams();
        unset($array[$exclude]);
        return $array;
    }
    public static function allStreams(){
        /*
         * Returns list of streams for use in stream editor
         */
        global $connection;
        if($stmt=$connection->prepare("SELECT id,title,type,mobile,url,source,description,cover FROM plugin_live")){
            $stmt->execute();
            $stmt->bind_result($id,$title,$type,$mobile,$url,$source,$description,$cover);
            $streams = array();
            while($stmt->fetch()){
                $onclick = "cm_loadPage('plugin_live&edit=$id');";
                $streams[] = array("Title" => $title, "Type" => self::kpStreamTypes()[$type], "onclick" => $onclick);
            }
            return $streams;
            
        }else{
            return false;
        }
    }
    /**
     * 
     * @global type $connection
     * @return boolean
     * 
     * Returns list of streams for JSON reply
     */
     public static function listStreams(){
        global $connection;
        if($stmt=$connection->prepare("SELECT id,title,type,mobile,url,source,description,cover FROM plugin_live")){
            $stmt->execute();
            $stmt->bind_result($id,$title,$type,$mobile,$url,$source,$description,$cover);
            $cover_url = cover::getImage($cover);
            $streams = array();
            while($stmt->fetch()){
                $onclick = "cm_loadPage('plugin_live&edit=$id');";
                $streams[] = array("id" => $id, "title" => $title, "type" => $type, "url"  => $url, "source" => $source, "description" => $description, "cover" => $cover_url);
            }
            return $streams;
            
        }else{
            return false;
        }
    }
    
    /*Individual stream code generators
     * hdsStream - HDS, requires OSMF
     */
    public static function hdsStream($url){
        return "<video class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$cover\" controls autoplay data-setup='{\"techOrder\": [\"flash\",\"html5\"]}'>" . "<source src=\"$url\" type=\"application/adobe-f4m\">" . "Your browser does not support the video tag" . "</video>";
    }
    public static function hlsStream($url,$cover){
        //Designed for videoJS with FlasHLS
        return "<video class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$cover\" controls autoplay data-setup='{\"techOrder\": [\"flash\",\"html5\"]}'>" . "<source src=\"$url\" type=\"application/x-mpegURL\">" . "Your browser does not support the video tag" . "</video>";
    }
    public static function rtmpStream($url,$cover){
        return "<video class=\"vidplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" poster=\"$cover\" controls autoplay>" . "<source src=\"$url\" type=\"rtmp/mp4\">" . "Your browser does not support the video tag" . "</video>";        
    }
    public static function shoutStream($url,$cover){
        //Deprecated
        return "<video class=\"vidplayer radplayer video-js vjs-default-skin html5vid\" width=\"100%\" height=\"100%\" data-setup='{ \"inactivityTimeout\": 0, \"allowfullscreen\":\"false\" }' poster=\"$cover\" controls autoplay>" . "<source src=\"$url\" type=\"audio/mp3\">" . "</video>";
    }
    public static function icecastStream($url,$cover,$nowplaying, $title){
        //Uses external plugin - do not try to run if radio folder is not installed!
        require_once('plugins/radio/player.php');
        return radioPlayer::build($url,$cover,$nowplaying, $title);
    }
}
$pluginPages[] = new live();

/**
 * Description of schedule
 *
 * Work in progress, no current applications available
 * @author Cameron
 */
class schedule extends optionsPage{
    
    public $name = "plugin_schedule";
    //public $title = "Schedule";
    
    public function configPage(){
        global $connection;
       
        
       
        
        $ce = new centralElement("ce-medium");
        
        $form = new ajaxForm("newItemForm", $this->name, "POST");
        $form->formTitle("New Scheduled Item");
        $form->labeledInput("title", "text", "", "Title");
        $form->largeText("description", "", "Summary");
        $form->submit("Add new item");
        $ce->end();
    }
    
}


/**
 * Store details of images to be used as holding slides for channels
 */
class cover extends optionsPage{
    
    public $name = "plugin_cover";
    public $title = "Holding Screens";
    
    public function configPage(){
        global $connection;
        ce::begin("ce-medium");
        if($cover = filter_input(INPUT_GET,"edit")){
            
            $details = self::getImage($cover);
            backButton($this->name);
            $editForm = new ajaxForm("editVideoForm", $this->name . "&edit=" . $cover, "POST");
            $editForm->formTitle("Edit holding screen");
            $editForm->labeledInput("title", "text", $details['title'], "Title");
            $editForm->inputWithButton("url", $details['url'], "Image URL", "browseServer()", "Upload");
            $editForm->largeText("description", $details['description'], "Description");
            $editForm->otherActionButton("deleteVideo", "Delete stream", "&delete=$stream");
            $editForm->submit("Save changes");
        }else{
        
            $form = new ajaxForm("newItemForm", $this->name, "POST");
            $form->formTitle("New holding screen");
            $form->labeledInput("title", "text", "", "Title");
            $form->inputWithButton("url", "", "Image URL", "browseServer()", "Upload");
            $form->largeText("description", "", "Description");
            $form->submit("Add new item");
            
            $videos = self::allCovers();
            $list = new multiPageList($videos,"videoList");
            $list->title("All streams");
            
            $list->display($this->name);
        }
        ce::end();
    }
    
    public function updatePage(){
        global $connection;
        
        $title = filter_input(INPUT_POST,"title");
        $description = content("description");
        $url = filter_input(INPUT_POST,"url");

        if(isset($_GET['delete'])){
            
            $delete = filter_input(INPUT_GET,"delete");
            block(2);
            
            $bdstmt = $connection->prepare("DELETE FROM $this->name WHERE id=?");
            $bdstmt->bind_param("i",$delete);
            if($bdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete stream: $bdstmt->error";
            }
        }
        else if(isset($_GET['edit'])){
            block(2);
            
            $edit = filter_input(INPUT_GET,"edit");
            //Editing existing post
            $equery = "UPDATE $this->name SET title=?, description=?, url=?  WHERE id=?";
            if($estmt = $connection->prepare($equery)){
                $estmt->bind_param("sssi",$title,$description,$url,$edit);
                if($estmt->execute()){
                    echo "Saved changes";
                    $estmt->close();
                    return;
                }
            }
            echo "Error saving changes: $estmt->error";
            return;
            
        }
        else{
            block(2);
            //Creating new stream
            if(!$title){
                echo "Please enter a title";
                return;
            }else{
                $nbstmt = $connection->prepare("INSERT INTO $this->name (title,description,url) VALUES (?,?,?);");
                $nbstmt->bind_param("sss",$title,$description,$url);
                if($nbstmt->execute()){
                    echo "reload";
                    return;
                }
                echo "Error adding post: $nbstmt->error";
            }
        }
    }

    
    public static function getImage($cover_id){
        global $connection;
        
        if($stmt = $connection->prepare("SELECT title,description,url FROM plugin_cover WHERE id=?")){
            $stmt->bind_param("i",$cover_id);
            $stmt->execute();
            $stmt->bind_result($title,$description,$url);
            $stmt->fetch();
            $stmt->close();
            
            $id = "cover_" . $cover_id;
            $source = "<img class=\"vidplayer cover\" src=\"$url\" alt=\"$title\">";
            
            $array = array("id" => $id, "title" => $title, "description" => $description, "url" => $url, "source" => $source);
            return $array;
        }
    }
    /**
     * Returns all covers in key-pair array for use in dropdown menus
     */
    public static function kpCovers(){
        global $connection;
        if($stmt = $connection->prepare("SELECT id,title FROM plugin_cover")){
            $stmt->execute();
            $stmt->bind_result($id,$title);
            $covers = array();
            $covers['null'] = "--";
            while($stmt->fetch()){
                $covers[$id] = $title;
            }
            return $covers;
        }else{
            return false;
        }
    }
    public static function allCovers(){
        global $connection;
        if($stmt = $connection->prepare("SELECT id,title,description,url FROM plugin_cover")){
            $stmt->execute();
            $stmt->bind_result($id,$title,$description,$url);
            $covers = array();
            while($stmt->fetch()){
                $onclick = "cm_loadPage('plugin_cover&edit=$id')";
                $covers[] = array("Title" => $title, "Description" => $description, "URL" => $url, "onclick" => $onclick);
            }
            return $covers;
        }else{
            return false;
        }
    }
}

$pluginPages[] = new cover();

class video_setup extends optionsPage{
    
    public $name = "plugin_videosetup";
    
    function updatePage(){
        
    }
}



/**
 * Generate code for creating a dynamically generated videoJS player that can be embedded on another site
 * 
 * 
 */
class videojs_embed extends optionsPage{
    public $name = "videojs_embed";
    public $title = "Player Maker";
    
    public function displayPage(){
        $url = filter_input(INPUT_GET,"url");
        $poster = filter_input(INPUT_GET, "poster");
        iframeOutput("", videos::htmlcode($url, $poster));
    }
    
    public function configPage(){ 
       ce::begin('ce-medium');
        $form = new ajaxForm("embedCodeForm",$this->name,"POST");
        $form->formTitle("VideoJS Player Maker");
        $form->labeledInput("url", "text", "", "Video URL");
        $form->labeledInput("poster", "text", "", "Poster URL");
        $form->labeledInput("width", "text", "100%", "Width");
        $form->labeledInput("height", "text", "100%", "Height");
        $form->clipboardSubmit("Generate","Embed code");
        ce::end;
    }
    public function updatePage(){
        $url = htmlspecialchars(filter_input(INPUT_POST,'url'));
        $poster = htmlspecialchars(filter_input(INPUT_POST,'poster'));
        $width = filter_input(INPUT_POST,'width');
        $height = filter_input(INPUT_POST,'height');
        
        if(strlen($url) > 0){ echo self::iframe($url,$poster,$width,$height); }
        else{ echo "Enter a URL"; }
    }
    
    public static function iframe($url, $poster, $width, $height){
        $src = actualLink() . "/public.php?action=videojs_embed&url=$url&poster=$poster";
        return "<iframe src=\"$src\" width=\"$width\" height=\"$height\" frameborder=\"0\" allowfullscreen></iframe>";
    }
}

$pluginPages[] = new videojs_embed();