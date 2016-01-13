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
    echo "<!doctype html>
<html data-cast-api-enabled='true'>
<head>
<meta charset=\"UTF-8\">";
    html::css("plugins/video/iframe.css");
    html::title($title);
    html::lockZoom();
    echo "<style>body{ margin:0; padding:0; width:100%; height:100%; overflow:hidden;} .html5vid, iframe{ width: 100%; width:100vw !important; height:100%; height:100vh !important; min-width: 300px; min-height:200px;}</style>";
    //videojs::init();
    html::endHead();
    echo $code;
    //videojs::run();
    html::end();
}

class source{
    public $src;
    public $type;
    public $res;
    public $video_id;
    public $source_id;
            
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

class channel{
    public $id;
    public $title;
    public $programme;
    public $thumbnail;
    public $content_id;
    
    public function __construct($id,$title,$programme,$thumbnail,$content_id) {
        $this->id = $id;
        $this->title = $title;
        $this->programme = $programme;
        $this->thumbnail = $thumbnail;
        $this->content_id = $content_id;
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
    /* bool $live: whether the player supports live playback */
    public $live = true;
    /* bool $ondemand: whether the player supports on-demand playback */
    public $ondemand = true;
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
    
    public static function kpLiveTypes(){
        $players = self::getPlayerTypes();
        $types = array();
        //$types['--'] = var_dump($players) . ' modules found';
        foreach($players as $player){
            $name = $player->name;
            $title = $player->title;
            if($player->live){
                $types[$name] = $title;
            }
        }
        return $types;
    }
    
    public static function kpVodTypes(){
        $players = self::getPlayerTypes();
        $types = array();
        //$types['--'] = var_dump($players) . ' modules found';
        foreach($players as $player){
            $name = $player->name;
            $title = $player->title;
            if($player->ondemand){
                $types[$name] = $title;
            }
        }
        return $types;
    }
    
}

class videoPreview extends uiElement{
    public $name = 'video_preview';
    
    function clientSide(){
        echo '//work in progress';
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
                $form->kpSelector("live", videos::kpVideos(1), $live, "Live stream");
                $form->kpSelector("vod", videos::kpVideos(0), $vod, "On-demand video");
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
            $form->labeledInput("title", "text", "", "Channel name");
            $form->checkBox("visible", "1", "Visible in controller");
            $form->inputWithButton("thumbnail", "", "Thumbnail image", "browseServer()", "Upload");
            $form->kpSelector("type", manager::kpVideoTypes() , "", "Content to display");
            $form->kpSelector("live", videos::kpVideos(1), "", "Live stream");
            $form->kpSelector("vod", videos::kpVideos(0) , "", "On-demand video");
            $form->kpSelector("cover", cover::kpCovers(), "", "Holding screen");
            $form->submit("Add channel");
            
            $slist = new ajaxList(NULL,"streamlist");
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
            iframeOutput($video->title, $video->source);
        }else{
  
            if(isset($_GET['id'])){
                $playerid = filter_input(INPUT_GET,"id");
            }else{
                $playerid = 1;
            }
            
            $video = self::kpChannel($playerid);
            $iframe_link = actualLink() . "/public.php?action=$this->name&iframe=$playerid";
            $video->iframe = $iframe_link;
            //Displays memory for optimisation processes
            //$video->description .= "<p>Memory usage: " . memory_get_peak_usage() . "<br />Stream ID: " . $video->id . "</p>";
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
                    $player_content = videos::getVideo($live,true);
                    break;
                case "vod":
                    $player_content = videos::getVideo($vod,true);
                    break;
                case "cover":
                    $player_content = cover::getImage($cover);
                    break;
            }
            
            $player_content->id = $type . '_' . $player_content->id;
            
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
            $channels[] = new channel($id, $title, "", $thumbnail, "");
        }
        foreach($channels as $key => $channel){
            $id = $channel->id;
            $data = self::kpChannel($id);
            $channels[$key]->programme = $data->title;
            if($data->poster){
                $channels[$key]->thumbnail = $data->poster;
            }
        }
        return $channels;
        
    }
    
}

$pluginPages[] = new manager();

/**
 * Store details of images to be used as holding slides for channels
 */
class cover extends optionsPage{
    
    public $name = "plugin_cover";
    public $title = "Holding Screens";
    
    public function configPage(){
        global $connection;
        ce::begin('');
        if($cover = filter_input(INPUT_GET,"edit")){
            
            $details = self::getImage($cover);
            backButton($this->name);
            $editForm = new ajaxForm("editVideoForm", $this->name . "&edit=" . $cover, "POST");
            $editForm->formTitle("Edit holding screen");
            $editForm->labeledInput("title", "text", $details['title'], "Title");
            $editForm->inputWithButton("url", $details['url'], "Image URL", "browseServer()", "Upload");
            $editForm->largeText("description", $details['description'], "Description");
            $editForm->otherActionButton("deleteVideo", "Delete stream", "&delete=$stream", 'plugin_videomanager');
            $editForm->submit("Save changes",'plugin_cover');
        }else{
        
            $form = new ajaxForm("newItemForm", $this->name, "POST");
            $form->formTitle("New holding screen");
            $form->labeledInput("title", "text", "", "Title");
            $form->inputWithButton("url", "", "Image URL", "browseServer()", "Upload");
            $form->largeText("description", "", "Description");
            $form->submit("Add new item");
            
            $covers = self::allCovers();
            $list = new ajaxList($covers,"videoList");
            $list->title("All images");
            
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
            
            //$array = array("id" => $id, "title" => $title, "description" => $description, "url" => $url, "source" => $source);
            $array = new video($cover_id, "", "", $source, $url, $title, $description, "", "");
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

//$pluginPages[] = new videojs_embed();