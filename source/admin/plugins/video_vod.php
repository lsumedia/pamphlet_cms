<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class videos extends optionsPage{
    
    public $name = "plugin_vod";
    public $title = "On Demand Videos";
    
    
    
    public static function formArray($live,$new){
        /* AjaxForm2 options array for future use */
        if($live){
                $playerOptions = mediaPlayer::kpLiveTypes();
            }else{                
                $playerOptions = mediaPlayer::kpVodTypes();
            }   
            
        $array = [
            'title' => ['type' => 'text', 'label' => 'Title'],
            'type' => ['type' => 'select', 'label' => 'Video type', 'options' => $playerOptions],
            'code' => ['type' => 'plaintext', 'label' => 'Source code/additional parameters'],
            'tags' => ['type' => 'text', 'label' => 'Tags (space seperated)'],
            'date' => ['type' => 'date', 'label' => 'Posted date'],
            'author' => ['type' => 'select', 'label' => 'Author', 'options' => kpFullnames(), 'value' => get_username()],
            'poster' => ['type' => 'url', 'label' => 'Poster'],
            
          
            'description' => ['type' => 'richtext', 'label' => 'Description']
        ];
        
        if(!$new){ 
            $array['external_url'] = ['type' => 'readonly', 'label' => 'External URL'];
            $array['delete'] = ['type' => 'button', 'label' => 'Delete video']; 
        }
          
        return $array;
    }
    
    public function configPage($live){
        
        global $connection;
        global $config;
        
        $perm_required = $config['access_perm'];
        
        ce::begin("style='min-width:800px'");
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
            
            if($live){
                $editForm->formTitle("Edit stream");
                $editForm->labeledInput("title", "text", $details->title, "Title");
                $editForm->kpSelector("type", mediaPlayer::kpLiveTypes(), $details->type, "Stream type");
            }else{
                $editForm->formTitle("Edit video");
                $editForm->labeledInput("title", "text", $details->title, "Title");
                $editForm->kpSelector("type", mediaPlayer::kpVodTypes(), $details->type, "Video type");  
            }
            ajaxForm::startOptionalSection("embed","Embed code/Additional parameters");
            $editForm->plainText("code", $details->source, "Custom embed code");
            ajaxForm::endOptionalSection();
            $editForm->labeledInput("tags", "text", $details->tags, "Tags (space seperated)");
            $editForm->labeledInput("date", "date", $details->date, "Date posted");
            if($live){
                $editForm->kpSelector("poster", cover::kpCovers(), $details->poster, "Poster URL");
            }else{
                $editForm->file("poster", $details->poster, "Poster URL"); 
            }
            $editForm->largeText("description", $details->description, "Description");
            $editForm->lockedInput(actualLink() . "/public.php?action=$this->name&iframe=$video", "External embed URL");
            if($live){
                $editForm->otherActionButton("deleteVideo", "Delete video", "&delete=$video",'plugin_live');
            }else{                
                $editForm->otherActionButton("deleteVideo", "Delete video", "&delete=$video",'plugin_vod');
            }
            
            $editForm->submit("Save changes");
            
            /* New type form - work in progress. Look how tiny it is!*//*
            $form = new customForm(self::formArray($live, false), 'editform', "nothingrightnow", 'POST');
            $form->setTitle('Edit video');
            $form->build('Save changes');
                    */
            /* New source form */
            $sourceForm = new ajaxForm('newSourceForm', "plugin_vod&add_source&video_id=$video",'POST');
            $sourceForm->formTitle("Add source");
            $sourceForm->file('source_src', '', 'Source URL');
            $sourceForm->labeledInput('source_type', 'text', 'video/mp4', 'Source type');
            $sourceForm->labeledInput('source_res', 'number', '720', 'Source vertical resolution');
            $sourceForm->submit('Add source',"plugin_vod&edit=$video");
            
            /* List existing sources */
            $sources = self::sourcesArray($video);
            $list = new ajaxList($sources,'sourceList');
            $list->title('Sources');
            $list->display();
            
            
        }else if($source_id = filter_input(INPUT_GET,'edit_source')){
            /* Editing existing source entry */
            $source = self::getSource($source_id);
            
            $action = "plugin_vod&edit=$source->video_id";
            backButton($action);
            $form = new ajaxForm("sourceForm", "$this->name&edit_source=$source_id", 'POST');
            $form->formTitle("Editing source");
            $form->file('src', $source->src, 'Source URL');
            $form->labeledInput('type','text',$source->type,'Source type');
            $form->labeledInput('res', 'text', $source->res, 'Source vertical resolution');
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
            $form->file("source_src", "", "Primary source URL");
            $form->labeledInput('source_type', 'text', 'video/mp4', 'Primary source type');
            $form->labeledInput('source_res','text','','Primary source vertical resolution');
            ajaxForm::startOptionalSection("embed","Embed code/Additional parameters");
            $form->plainText("code", "", "Custom embed code");
            ajaxForm::endOptionalSection();
            $form->labeledInput("tags", "text", "", "Tags (space seperated)");
            $nowdate = date("Y-m-d");
            $form->labeledInput("date", "date", $nowdate, "Date posted");
            if($live){
                $form->kpSelector("poster", cover::kpCoverURLs(), '', "Poster URL");
            }else{
                $form->file("poster", '', "Poster URL");
            }
            $form->largeText("description", "", "Description");
            if($live){
                $form->submit("Add new item",'plugin_live');
            }else{
                $form->submit("Add new item",'plugin_vod');
            }
            
            /* */
            $videos = self::allVideos($live);
            /*
            $list = new multiPageList($videos,"videoList");
            $list->title("All videos");

            if($live){
                $list->display('plugin_live');
            }else{
               $list->display('plugin_vod');
            }
            */
            $list2 = new ajaxList($videos, 'videoList2');
            if($live){
                $list2->title('All streams');
            }else{
                $list2->title('All videos');
            }
            $list2->display();
            
        }
        ce::end();
    }
    
    public function updatePage(){
        
        
        global $connection;
        global $config;
        
        $perm_required = $config['access_perm'];
        
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
            block($perm_required);
            
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
            block($perm_required);
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
            block($perm_required);
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
            block($perm_required);
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
            block($perm_required);
            $video_id = filter_input(INPUT_GET,'video_id');
            $src = filter_input(INPUT_POST,'source_src');
            $type = filter_input(INPUT_POST,'source_type');
            $res = filter_input(INPUT_POST,'source_res');
            
            if($src){
                $sstmt = $connection->prepare("INSERT INTO plugin_vod_sources SET video_id=?, src=?, type=?, res=?;");
                $sstmt->bind_param("isss",$video_id,$src,$type,$res);

                if($sstmt->execute()){
                        echo 'reload';
                    return;
                }else{
                    echo "Error adding source: $sstmt->error";
                }
            }else{
                echo "Please enter a URL";
            }
            
        }
        else{
            block($perm_required);
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
                $nbstmt->bind_param("sssssssi",$title,$tags,$date,$description,$type,$code,$poster,$live);
                
                if($nbstmt->execute()){
  
                    $nbstmt->close();
                    if(strlen($source_src) > 0){
                        $last = $connection->insert_id;
                        $sstmt = $connection->prepare("INSERT INTO plugin_vod_sources SET video_id=?, src=?, type=?, res=?;");
                        $sstmt->bind_param("isss",$last,$source_src,$source_type,$source_res);
                        $sstmt->execute();
                    }
                   
                    echo "reload";
                    return;
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
            $limit = $_GET['limit'];
            echo json_encode(self::listVideos($live,$limit));
        }else if(isset($_GET['id'])){
            //html::div("player_container","player1");
            $videoid = filter_input(INPUT_GET,"id");
            $video = self::getVideo($videoid,true);
            echo json_encode($video);
        }else if(isset($_GET['iframe'])){
            $videoid = filter_input(INPUT_GET,"iframe");
            $video = self::getVideo($videoid,true);
            iframeOutput($video->title, $video->source);
        }else if(isset($_GET['tag'])){
            $tags = $_GET['tag'];
            echo json_encode(self::searchByTag($live, $tags));
        }else if(isset($_GET['search'])){
            $term = $_GET['search'];
            echo json_encode(self::searchByString($live, $term));
        }else{
            echo "VOD iFrame generator<br />", PHP_EOL;
            echo "Valid requests:<br />";
            echo "&list : Returns list of videos in JSON format<br />";
            echo "+&limit=[limit] : Limit list to a given size<br />";
            echo "+&before=[date] : Only show results before a given date<br />";
            echo "+&after=[date] : Only show results after a given date<br />";
            echo "&id=[id] : Returns a particular video's source code<br />";
            echo "&iframe=[id] : Return a video's source code for use in iframe<br />";
            echo "&tag=[tag] : Returns a list of all videos with a specified tag<br />";
            echo "+&limit=[limit] : Limit results to a given size<br />";
            echo "&search=[term] : Returns a list of all videos matching a search term<br />";
            echo "+&limit=[limit] : Limit results to a given size<br />";
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
                 $action = "plugin_vod&edit=$id";
                $videos[] = array("Title" => $vtitle, "Type" => mediaPlayer::kpTypes()[$type], "Tags" => $tags, "Date posted" => $date, "action" => $action);
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
    public static function listVideos($live, $limit){
        /* Returns all video information in a multi-dimensional array ordered by date */
        global $connection;
        $limitstr = (isset($_GET['limit']))? "LIMIT " . $connection->escape_string($_GET['limit']) : "";
        $beforestring = (isset($_GET['before']))? "AND date < '" . $connection->escape_string($_GET['before']) . "'" : "";
        $afterstring = (isset($_GET['after']))? "AND date > '" . $connection->escape_string($_GET['after']) . "'": "";
        $query = "SELECT id,title,url,type,tags,date FROM plugin_vod WHERE live=? $beforestring $afterstring ORDER BY date desc $limitstr";
        if($stmt = $connection->prepare($query)){ 
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
    
    public static function searchByTag($live,$searchtags){
        global $connection;
        $limitstr = (isset($_GET['limit']))? "LIMIT " . $connection->escape_string($_GET['limit']) : "";
        $beforestring = (isset($_GET['before']))? "AND date < '" . $connection->escape_string($_GET['before']) . "'" : "";
        $afterstring = (isset($_GET['after']))? "AND date > '" . $connection->escape_string($_GET['after']) . "'": "";
        $query = "SELECT id,title,url,type,tags,date FROM plugin_vod WHERE tags COLLATE UTF16_GENERAL_CI LIKE ? AND live=? $beforestring $afterstring ORDER BY date desc $limitstr";
        if($stmt = $connection->prepare($query)){
            $searchtags = '%' . $searchtags . '%';
            $stmt->bind_param('si',$searchtags,$live);
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
    
    public static function searchByString($live,$term){
        global $connection;
        $limitstr = (isset($_GET['limit']))? "LIMIT " . $connection->escape_string($_GET['limit']) : "";
        $beforestring = (isset($_GET['before']))? "AND date < '" . $connection->escape_string($_GET['before']) . "'" : "";
        $afterstring = (isset($_GET['after']))? "AND date > '" . $connection->escape_string($_GET['after']) . "'": "";
        $querystring  = "SELECT id,title,url,type,tags,date FROM plugin_vod WHERE (LOWER(tags) LIKE LOWER(?) OR LOWER(title) LIKE LOWER(?) OR LOWER(description) LIKE LOWER(?)) AND live=? $beforestring $afterstring ORDER BY date desc $limitstr";
        if($stmt = $connection->prepare($querystring)){
        //if($stmt = $connection->prepare("SELECT id,title,url,type,tags,date FROM plugin_vod WHERE (tags LIKE ? COLLATE latin1_general_ci OR title LIKE ? COLLATE latin1_general_ci OR description LIKE ? ) AND live=? ORDER BY date desc ")){
            $termre = '%' . $term . '%';
            $stmt->bind_param('sssi',$termre,$termre,$termre,$live);
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
            echo $connection->error;
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
            $action = "plugin_vod&edit_source=$source_id";
            $sources[] = array('URL' => $source_src, 'Type' => $source_type, 'Resolution' => $source_res, 'action' => $action);
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
            
            if(is_numeric($poster) && $build){
                //$poster = cover::getImage($poster);
                $poster = cover::getImageUrl($poster);
            }
            
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
           
            
        }else{
            echo "Error - VOD database request failed!";
        }
    }
    
    public static function buildRawVideo($video){
        
        if(is_numeric($video->poster)){
                //$poster = cover::getImage($poster);
                $video->poster = cover::getImageUrl($video->poster);
        }
            
        $players = mediaPlayer::getPlayerTypes();
        $type = $video->type;
            $built = false;
            foreach($players as $player){
                if($player->name == $type){
                    //TODO - add setup 
                    $video = $player->build($video,"");
                    $built = true;
                    return $video;
                }
            }
        return false;
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

class video_tags extends optionsPage{
    
    public $name = 'plugin_video_tags';
    public $title = 'Video tags';
    
    public static function formArray(){
        return [
            'tag_id' => [ 'type' => 'text', 'label' => 'Tag name'],
            'title' => ['type' => 'text', 'label' => 'Tag title'],
            'parent' => ['type' => 'select', 'label' => 'Parent tag', 'options' => self::kvpTags()],
            'type' => ['type' => 'select', 'label' => 'Type', 'options' => self::kvpTagTypes(), 'value' => 'other'],
            'bannerurl' => ['type' => 'url', 'label' => 'Header image (12:5)'],
            'coverurl' => ['type' => 'url', 'label' => 'Background image (1:1)'],
            'primarycolor' => ['type' => 'color', 'label' => 'Primary colour'],
            'description' => ['type' => 'richtext', 'label' => 'Description']
        ];
    }
    
    public function configPage(){
        ce::begin('');
        
        $form = new customForm(self::formArray(), 'tagform', $this->name, 'POST');
        $form->setTitle('New tag');
        $form->build('Add tag');
        
        ce::end();
    }
    
    public function updatePage(){
        
        $results = customForm::decodeResult(self::formArray(), $_POST);

        if($delete = $_GET['delete']){
            //delete custom tag
            
        }else if($id = $_GET['id']){
            //edit custom tag
            
        }else{
            //new tag
            
        }
    }
    
    /**
     * Find all tags that are currently in use
     * for use in drop-down menus
     */
    public static function kvpTags(){
        //get tags from all videos
        
        //split tags into array for each video
        
        //add tags to tag array which are not yet present
        
        //return tag array
        
        return [
            '' => '--',
            'lsutv' => 'lsutv',
            'lcr' => 'Loughborough Campus Radio'
        ];
    }
    
    public static function kvpTagTypes(){
        return [
            'channel' => 'Channel',
            'program' => 'Programme',
            'event' => 'Event',
            'other' => 'Other'
        ];
    }
}

$pluginPages[] = new video_tags();