<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
                $editForm->kpSelector("type", mediaPlayer::kpLiveTypes(), $type, "Stream type");
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
            $form->kpSelector("type", mediaPlayer::kpLiveTypes(), "custom", "Stream type");
            $form->kpSelector("mobile", self::kpStreams(), "", "Mobile stream");
            $form->labeledInput("url", "text", "", "Stream URL");
            $form->kpSelector("cover", cover::kpCovers(), "", "Loading screen");
            ajaxForm::startOptionalSection("sourceSection", "Custom code");
            $form->plainText("source", "", "Custom embed code");
            ajaxForm::endOptionalSection();
            $form->largeText("description", "", "Description");
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
            $stream = self::getStream($streamid,true);
            echo json_encode($stream);
        }else if(isset($_GET['iframe'])){
            $streamid = filter_input(INPUT_GET,"id");
            $stream = self::getStream($streamid,true);
            iframeOutput($stream->title, $stream->source);
        }else{
            echo "Live stream iFrame generator<br />", PHP_EOL;
            echo "Valid requests:<br />";
            echo "&list : Returns list of streams in JSON format<br />";
            echo "&id=[id] : Returns a particular stream's source code<br />";
            echo "&iframe=[id] : Return a stream's source code for use in iframe<br />";
        }
    }
    
    public static function kpStreamTypes(){
        return array("hds" => "HDS", "hls" => "HLS", "rtmp" => "RTMP","icecast" => "Radio", "custom" => "Custom embed code" );
    }
    public static function getStream($id, $build){     
        global $connection;
        
        if(!isset($build)){
            $build = false;
        }
        
        if($stmt = $connection->prepare("SELECT title,type,url,mobile,source,description,cover FROM plugin_live WHERE id=?")){
            $stmt->bind_param("i",$id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($title, $type, $url, $mobile, $code, $description,$cover);
            $stmt->fetch();
            
            
            $cover = cover::getImage($cover);
            $cover_url = $cover->poster;
            
            /*TODO - get rid of this and build multiple sources into database */
            $src = new source($url,'rtmp/mp4','720');
            $sources = array($src);
            
            
            $video = new video($id, $type, $sources, $code, $cover_url, $title, $description, "", "");
            
            if($mobile && onMobile()){
                $video = self::getStream($mobile,true);
                $video->title = $title;
            }else{
                
                $players = mediaPlayer::getPlayerTypes();
                
                if($build == true){
                    foreach($players as $player){
                        if($player->name == $type){
                            //TODO - add setup 
                            $video = $player->build($video,"");
                        }
                    }
                }
                
            }
            
            /* Remove video poster so channel logo is displayed instead */
            $video->poster = null;
            
            return $video;
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
                $streams[] = array("Title" => $title, "Type" => mediaPlayer::kpTypes()[$type], "onclick" => $onclick);
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