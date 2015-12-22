<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class videos extends optionsPage{
    
    public $name = "plugin_vod";
    public $title = "On Demand Videos";
    
    public function configPage($live){
        global $connection;
        ce::begin("ce-medium;");
        /* Declare $live as false if we don't want to see live videos*/
        if(!isset($live)){
            $live = 0;
        }
        
        if($video = filter_input(INPUT_GET,"edit")){
            /* Editing existing video */
            /* Edit video meta form */
            
            $details = self::getVideo($video, false);
            $live = $details->live;
            if($live){
                backButton('plugin_live');
            }else{                
                backButton('plugin_vod');
            }
            
            $editForm = new ajaxForm("editVideoForm", $this->name . "&edit=" . $video, "POST");
            $editForm->formTitle("Edit video");
            $editForm->labeledInput("title", "text", $details->title, "Title");
            if($live){
                $editForm->kpSelector("type", mediaPlayer::kpLiveTypes(), $details->type, "Stream type");
            }else{                
                $editForm->kpSelector("type", mediaPlayer::kpVodTypes(), $details->type, "Video type");  
            }
            ajaxForm::startOptionalSection("embed","Embed code/Additional parameters");
            $editForm->plainText("code", $details->source, "Custom embed code");
            ajaxForm::endOptionalSection();
            $editForm->labeledInput("tags", "text", $details->tags, "Tags (space seperated)");
            $editForm->labeledInput("date", "date", $details->date, "Date posted");
            $editForm->labeledInput("poster", "text", $details->poster, "Poster URL");
            $editForm->largeText("description", $details->description, "Description");
            $editForm->lockedInput(actualLink() . "/public.php?action=$this->name&iframe=$video", "External embed URL");
            if($live){
                $editForm->otherActionButton("deleteVideo", "Delete video", "&delete=$video",'plugin_live');
            }else{                
                $editForm->otherActionButton("deleteVideo", "Delete video", "&delete=$video",'plugin_vod');
            }
            
            $editForm->submit("Save changes");
            /* New source form */
            $sourceForm = new ajaxForm('newSourceForm', "plugin_vod&add_source&video_id=$video",'POST');
            $sourceForm->formTitle("Add source");
            $sourceForm->labeledInput('source_src', 'text', '', 'Source URL');
            $sourceForm->labeledInput('source_type', 'text', 'video/mp4', 'Source type');
            $sourceForm->labeledInput('source_res', 'number', '720', 'Source vertical resolution');
            $sourceForm->submit('Add source',"plugin_vod&edit=$video");
            /* List existing sources */
            $sources = self::sourcesArray($video);
            $list = new objectList($sources,'sourceList');
            $list->title('Sources');
            $list->display();
            
        }else if($source_id = filter_input(INPUT_GET,'edit_source')){
            /* Editing existing source entry */
            $source = self::getSource($source_id);
            
            $action = "plugin_vod&edit=$source->video_id";
            backButton($action);
            $form = new ajaxForm("sourceForm", "$this->name&edit_source=$source_id", 'POST');
            $form->formTitle("Editing source");
            $form->labeledInput('src', 'text', $source->src, 'Source URL');
            $form->labeledInput('type','text',$source->type,'Source type');
            $form->labeledInput('res', 'number', $source->res, 'Source vertical resolution');
            $form->otherActionButton("deleteSource", "Delete source", "&delete_source=$source_id",$action);
            $form->submit('Update');
        }
        else{
            /* Add video form */
            
            if($live){
                $form = new ajaxForm("newItemForm", 'plugin_vod&live', "POST");
                $form->formTitle("New live stream");
            }else{
                $form = new ajaxForm("newItemForm", 'plugin_vod', "POST");
                $form->formTitle("New video");
            }
            $form->labeledInput("title", "text", "", "Title");
            if($live){
                $form->kpSelector("type", mediaPlayer::kpLiveTypes(), 'html5', "Stream type");
            }else{                
                $form->kpSelector("type", mediaPlayer::kpVodTypes(), 'html5', "Video type");  
            }
            $form->labeledInput("source_src", "text", "", "Primary source URL");
            $form->labeledInput('source_type', 'text', 'video/mp4', 'Primary source type');
            $form->labeledInput('source_res','number','','Primary source vertical resolution');
            ajaxForm::startOptionalSection("embed","Embed code/Additional parameters");
            $form->plainText("code", "", "Custom embed code");
            ajaxForm::endOptionalSection();
            $form->labeledInput("tags", "text", "", "Tags (space seperated)");
            $nowdate = date("Y-m-d");
            $form->labeledInput("date", "date", $nowdate, "Date posted");
            $form->labeledInput("poster", "text", "", "Poster URL");
            $form->largeText("description", "", "Description");
            if($live){
                $form->submit("Add new item",'plugin_live');
            }else{
                $form->submit("Add new item",'plugin_vod');
            }
            
            /* */
            $videos = self::allVideos($live);
            $list = new multiPageList($videos,"videoList");
            $list->title("All videos");
            
            if($live){
                $list->display('plugin_live');
            }else{
               $list->display('plugin_vod');
            }
            
            
        }
        ce::end();
    }
    
    public function updatePage(){
        
        
        global $connection;
        
        $title = filter_input(INPUT_POST,"title");
        $tags = filter_input(INPUT_POST,"tags");
        $date = filter_input(INPUT_POST,"date");
        $description = content("description");
        $type = filter_input(INPUT_POST,"type");
        //$url = filter_input(INPUT_POST,"url");
        $code = content("code");
        $poster = filter_input(INPUT_POST, "poster");
       
        
        if(isset($_GET['delete'])){
            //Deleting video
            $delete = filter_input(INPUT_GET,"delete");
            block(2);
            
            $bdstmt = $connection->prepare("DELETE FROM plugin_vod WHERE id=?");
            $sdstmt = $connection->prepare("DELETE FROM plugin_vod_sources WHERE video_id=?");
            $bdstmt->bind_param("i",$delete);
            $sdstmt->bind_param("i",$delete);
            if($bdstmt->execute() && $sdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete video: $bdstmt->error, $sdstmt->error";
            }
        }
        else if(isset($_GET['edit'])){
            //Editing video
            block(2);
            $edit = filter_input(INPUT_GET,"edit");
            //Editing existing post
            if($bstmt = $connection->prepare("UPDATE plugin_vod SET title=?, tags=?, date=?, description=?, type=? ,url=?, source=?, poster=?  WHERE id=?")){
                $bstmt->bind_param("ssssssssi",$title,$tags,$date,$description,$type,$url,$code,$poster,$edit);
                if($bstmt->execute()){
                    echo "Saved changes";
                    return;
                }
            }
            echo "Error saving changes: $bstmt->error";
            return;
            
        }else if(isset($_GET['delete_source'])){
            block(2);
            $source_id = filter_input(INPUT_GET,'delete_source');
            
            $bdstmt = $connection->prepare("DELETE FROM plugin_vod_sources WHERE source_id=?");
            $bdstmt->bind_param("i",$source_id);
            if($bdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete source: $bdstmt->error";
            }
        }
        else if(isset($_GET['edit_source'])){
            block(2);
            $source_id = filter_input(INPUT_GET,"edit_source");
            $src = filter_input(INPUT_POST,'src');
            $type = filter_input(INPUT_POST,'type');
            $res = filter_input(INPUT_POST,'res');
            //Editing existing post
            if($bstmt = $connection->prepare("UPDATE plugin_vod_sources SET src=?, type=?, res=? WHERE source_id=?")){
                $bstmt->bind_param("sssi",$src,$type,$res,$source_id);
                if($bstmt->execute()){
                    echo "Saved changes";
                    return;
                }
            }
            echo "Error saving changes: $bstmt->error";
            return;
            
        }
        else if(isset($_GET['add_source'])){
            block(2);
            $video_id = filter_input(INPUT_GET,'video_id');
            $src = filter_input(INPUT_POST,'source_src');
            $type = filter_input(INPUT_POST,'source_type');
            $res = filter_input(INPUT_POST,'source_res');
            
            $sstmt = $connection->prepare("INSERT INTO plugin_vod_sources SET video_id=?, src=?, type=?, res=?;");
            $sstmt->bind_param("isss",$video_id,$src,$type,$res);
            
            if($sstmt->execute()){
                    echo 'reload';
                return;
            }else{
                echo "Error adding source: $sstmt->error";
            }
            
            
        }
        else{
            block(2);
            //Creating new video
            
            if(isset($_GET['live'])){
                $live = 1;
            }else{
                $live = 0;
            }
            
            $source_src = filter_input(INPUT_POST,'source_src');
            $source_type = filter_input(INPUT_POST,'source_type');
            $source_res = filter_input(INPUT_POST,'source_res');
            
            if(!$title){
                echo "Please enter a title";
                return;
            }else{
                $nbstmt = $connection->prepare("INSERT INTO plugin_vod (title,tags,date,description,type,source,poster,live) VALUES (?,?,?,?,?,?,?,?);");
                $nbstmt->bind_param("sssssssi",$title,$tags,$date,$description,$type,$source,$poster,$live);
                
                if($nbstmt->execute()){
  
                    $nbstmt->close();
                    $last = $connection->insert_id;
                    $sstmt = $connection->prepare("INSERT INTO plugin_vod_sources SET video_id=?, src=?, type=?, res=?;");
                    $sstmt->bind_param("isss",$last,$source_src,$source_type,$source_res);
                    if($sstmt->execute()){
                        echo "reload";
                        return;
                    }
                    echo "Error adding source: $sstmt->error. Other metadata remains in the database.";
                    echo $connection->insert_id;
                }else{
                    echo "Error adding video: $nbstmt->error";
                }
            }
        }
    }
    
    public function displayPage($live){
        
        if(!isset($live)){
            $live = 0;
        }
        
        if(isset($_GET['list'])){
            echo json_encode(self::listVideos($live));
        }else if(isset($_GET['id'])){
            //html::div("player_container","player1");
            $videoid = filter_input(INPUT_GET,"id");
            $video = self::getVideo($videoid,true);
            echo json_encode($video);
        }else if(isset($_GET['iframe'])){
            $videoid = filter_input(INPUT_GET,"iframe");
            $video = self::getVideo($videoid,true);
            iframeOutput($video->title, $video->source);
        }else{
            echo "VOD iFrame generator<br />", PHP_EOL;
            echo "Valid requests:<br />";
            echo "&list : Returns list of videos in JSON format<br />";
            echo "&id=[id] : Returns a particular video's source code<br />";
            echo "&iframe=[id] : Return a video's source code for use in iframe<br />";
        }
    }

    public static function kpVideos($live){
        global $connection;
        if(!isset($live)){
            $live = 0;
        }
        if($stmt = $connection->prepare("SELECT id,title FROM plugin_vod WHERE live=? ORDER BY id DESC")){
            $stmt->bind_param('i',$live);
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
    
    
    /**
     * 
     * @global type $connection
     * @param int $live psuedo-bool to display either live streams or on demand videos
     * @return boolean
     */
    public static function allVideos($live){
        /* Returns all video information in a multi-dimensional array */
        global $connection;
        if($stmt = $connection->prepare("SELECT id,title,type,tags,date FROM plugin_vod WHERE live=? ORDER BY id DESC")){
            $stmt->bind_param('i',$live);
            $stmt->execute();
            $videos = array();
            $stmt->bind_result($id,$vtitle,$type,$tags,$date);
            while($stmt->fetch()){
                 $onclick = "cm_loadPage('plugin_vod&edit=$id');";
                $videos[] = array("Title" => $vtitle, "Type" => mediaPlayer::kpTypes()[$type], "Tags" => $tags, "Date posted" => $date, "onclick" => $onclick);
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
    public static function listVideos($live){
        /* Returns all video information in a multi-dimensional array ordered by date */
        global $connection;
        if($stmt = $connection->prepare("SELECT id,title,url,type,tags,date FROM plugin_vod WHERE live=? ORDER BY date desc")){
            $stmt->bind_param('i',$live);
            $stmt->execute();
            $ids = array();
            $videos = array();
            $stmt->bind_result($id,$vtitle,$url,$type,$tags,$date);
            while($stmt->fetch()){
                $ids[] = $id;
            }
            foreach($ids as $id){
                //Writes other videos to array
                $video = self::getVideo($id,true);
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
    
    public static function sourcesArray($video_id){
        global $connection;
        $sstmt = $connection->prepare("SELECT source_id, src, type, res FROM plugin_vod_sources WHERE video_id=?");
        $sstmt->bind_param('i',$video_id);
        $sstmt->execute();
        $sstmt->bind_result($source_id,$source_src,$source_type,$source_res);

        $sources = array();
        while($sstmt->fetch()){
            $onclick = "cm_loadPage('plugin_vod&edit_source=$source_id');";
            $sources[] = array('URL' => $source_src, 'Type' => $source_type, 'Resolution' => $source_res, 'onclick' => $onclick);
            //$sources[] = new source($source_src, $source_type, $source_res);
        }
        return $sources;
    }
    
    public static function getSource($source_id){
        /* Returns information for one video source in a source object*/
        global $connection;
        $sstmt = $connection->prepare("SELECT src, type, res, video_id FROM plugin_vod_sources WHERE source_id = ?");
        $sstmt->bind_param('i',$source_id);
        $sstmt->execute();
        $sstmt->bind_result($src,$type,$res,$video_id);
        $sstmt->fetch();
        
        $source = new source($src, $type, $res);
        $source->video_id = $video_id;
        
        return $source;
    }
    
   
    /**
     * 
     * @global type $connection
     * @param bool $id
     * @param bool $build - whether or not to build the player source code
     * @return video
     */
    public static function getVideo($id, $build){
        global $connection;
        
        if(!isset($build)){
            $build = false;
        }
        
        if($vstmt = $connection->prepare("SELECT title,tags,date,description,type,url,source,poster,live FROM plugin_vod WHERE id=?")){
            $vstmt->bind_param("i",$id);
            $vstmt->execute();
            $vstmt->bind_result($title,$tags,$date,$desc,$type,$url,$source,$poster,$live);
            $vstmt->store_result();
            $vstmt->fetch();
            $vstmt->close();
            
            $sstmt = $connection->prepare("SELECT source_id, src, type, res FROM plugin_vod_sources WHERE video_id=?");
            $sstmt->bind_param('i',$id);
            $sstmt->execute();
            $sstmt->bind_result($source_id,$source_src,$source_type,$source_res);

            $sources = array();
            while($sstmt->fetch()){
                $tempsource = new source($source_src,$source_type,$source_res);
                $tempsource->source_id = $source_id;
                $tempsource->video_id = $id;
                $sources[] = $tempsource;
            }
            
            if(count($sources) == 0){
                /* If no sources found, but URL exists, use the obselete URL */
                $sources[] = new source($url,'video/mp4','720');
            }

            //$source1 = new source($url,'video/mp4','720');
            //$sources[] = $source1;

            $video = new video($id, $type, $sources, $source, $poster, $title, $desc, $date, $tags);

            $video->video_id = $id;
            $video->live = $live;

            $players = mediaPlayer::getPlayerTypes();
            if($build == true){
                $built = false;
                foreach($players as $player){
                    if($player->name == $type){
                        //TODO - add setup 
                        $video = $player->build($video,"");
                        $built = true;
                    }
                }
            }
            
            if($build && !$built){
                echo "Failed to build video - player module '$type' not found", PHP_EOL;
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

class videos_live extends optionsPage{
    
    public $name = 'plugin_live';
    public $title = 'Live streams';
    public function configPage(){
        $videos = new videos();
        $videos->configPage(1);
    }
    public function displayPage(){
        $videos = new videos();
        $videos->displayPage(1);
    }
}

$pluginPages[] = new videos_live;