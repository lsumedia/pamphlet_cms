<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
            $editForm->kpSelector("type", mediaPlayer::kpTypes(), $details['type'], "Video type");
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
            $form->kpSelector("type", mediaPlayer::kpTypes(), $current, "Video type");
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
            iframeOutput($video->title, $video->source);
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
                $videos[] = array("Title" => $vtitle, "Type" => mediaPlayer::kpTypes()[$type], "Length" => $length, "Date posted" => $date, "onclick" => $onclick);
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
                $video = self::getVideo($id);
                $video->video_id = $id;
                $videos[] = $video;
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
            
            $sources = array();
            $source1 = new source($url,'video/mp4','720');
            $sources[] = $source1;
            $tags = "";
            
            $video = new video($id, $type, $sources, $source, $poster, $title, $desc, $date, $tags);
            
            $players = mediaPlayer::getPlayerTypes();
            foreach($players as $player){
                if($player->name == $type){
                    //TODO - add setup 
                    $video = $player->build($video,"");
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
    
}

$pluginPages[] = new videos();