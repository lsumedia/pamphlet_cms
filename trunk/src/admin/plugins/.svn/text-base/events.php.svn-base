<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of blog
 *
 * @author Cameron
 */
class events extends optionsPage{
    public $name = "plugin_events";
    public $title = "Events";
    
    function configPage(){
        global $connection;
        $ce = new centralElement("ce-medium");
        
        if($post = filter_input(INPUT_GET,'event')){
            /* Single post editor */
            backButton($this->name);
            $pstmt = $connection->prepare("SELECT title, description, author, date, time, enddate, endtime, location, type FROM events WHERE id=?");
            $pstmt->bind_param('i',$post);
            $pstmt->execute();
            $pstmt->store_result();
            $pstmt->bind_result($title,$desc,$author,$date,$time,$enddate,$endtime,$location,$type);
            $pstmt->fetch();
            $editBlog = new ajaxForm("editBlogForm", $this->name . "&edit=" . $post, "POST");
            $editBlog->formTitle("Edit event");
            $editBlog->labeledInput("title", "text", $title, "Title");
            //$editBlog->labeledInput("type", "text", $type, "Category");
            $editBlog->selector("type", eventCats(), $type, "Category");
            
            $editBlog->labeledInput("location", "text", $location, "Location");
            //Date section
            $editBlog->labeledInput("date","date",$date,"Start Date");
            ajaxForm::startOptionalSection("optionaldates", "More date options");
            $editBlog->labeledInput("time","time",$time,"Start Time");
            $editBlog->labeledInput("enddate","date",$enddate,"End Date");
            $editBlog->labeledInput("endtime","time",$endtime,"End Time");
            ajaxForm::endOptionalSection();
            
            $editBlog->kpSelector("author", kpFullnames(), $author, "Author");
            $editBlog->largeText("description", $desc, "Event description");
            
             $editBlog->otherActionButton("deleteEvent", "Delete event", "&delete=$post");
            $editBlog->submit("Update post");
            $pstmt->close();
            
        }else{
        
            $newBlog = new ajaxForm("newBlogForm", $this->name, "POST");
            $newBlog->formTitle("New event");
            $newBlog->labeledInput("title", "text", "", "Title");
            $newBlog->selector("type", eventCats(), "Other", "Category");
            $newBlog->labeledInput("location", "text", "", "Location");
            $newBlog->kpSelector("author", kpFullnames(), get_username(), "Author");
            $newBlog->labeledInput("date","date","","Start Date");
            ajaxForm::startOptionalSection("optionaldates", "More date options");
            $newBlog->labeledInput("time","time","","Start Time");
            $newBlog->labeledInput("enddate","date","","End Date");
            $newBlog->labeledInput("endtime","time","","End Time");
            ajaxForm::endOptionalSection();
            $newBlog->largeText("description", "", "Description");
            $newBlog->submit("Add event");
            $newBlog->end();

            $bstmt = $connection->prepare("SELECT e.id,e.title,e.author,e.date,e.enddate,e.type,u.fullname FROM events e, users u WHERE e.author = u.username ORDER BY e.date DESC");
            $bstmt->execute();
            $bstmt->bind_result($id,$title,$author,$date,$enddate,$type,$fullname);
            $blogList = new multiPageList(null, "bloglist");
            while($bstmt->fetch()){
                //$nicedate = date("jS F Y",strtotime($date));
                $combodate = combodate($date,$enddate);
                $post = array("Title" => $title, "Author" => $fullname, "Category" => $type, "Date" => $combodate, "onclick" => "cm_loadPage('$this->name&event=$id');");
                $blogList->addObject($post);
            }


            $blogList->title("Recent and upcoming events");
            $blogList->display($this->name);
            $bstmt->close();
        }
        $ce->end();
        
    }
    function updatePage(){
        global $connection;
        
        
        $title = filter_input(INPUT_POST, "title");
        $type = filter_input(INPUT_POST, "type");
        $location = filter_input(INPUT_POST,"location");
        $author = filter_input(INPUT_POST, "author");
        $description = content("description");
        $date = filter_input(INPUT_POST,"date");
        $time = filter_input(INPUT_POST,"time");
        $enddate = filter_input(INPUT_POST,"enddate");
        $endtime = filter_input(INPUT_POST,"endtime");
            
        if(isset($_GET['delete'])){
            $delete = filter_input(INPUT_GET,"delete");
             if(!eventAuthor($delete)){
                block(2);
            }
            block(3);
            $bdstmt = $connection->prepare("DELETE FROM events WHERE id=?");
            $bdstmt->bind_param("i",$delete);
            if($bdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete page: $estmt->error";
            }
        }
        else if(isset($_GET['edit'])){
            
            $edit = filter_input(INPUT_GET,"edit");
             if(!eventAuthor($edit)){
                block(2);
            }
            block(3);
            $estmt = $connection->prepare("UPDATE events SET title = ?, type = ?, location = ?, author = ?, description = ?, date=?, time=?, enddate=?, endtime=? WHERE id=? ");
            $estmt->bind_param('sssssssssi',$title,$type,$location,$author,$description,$date,$time,$enddate,$endtime,$edit);
            if($estmt->execute()){
                echo "Changes saved";
                return;
            }else{
                echo "Error saving changes: $estmt->error";
            }
            $estmt->close();
        }else{
            block(3);
            if(!$title || !$author){
                echo "Please enter a title";
                return;
            }
            $enstmt = $connection->prepare("INSERT INTO events (title,type,location,author,description,date,time,enddate,endtime) VALUES (?,?,?,?,?,?,?,?,?)");
            $enstmt->bind_param("sssssssss",$title,$type,$location,$author,$description,$date,$time,$enddate,$endtime);
            if($enstmt->execute()){
                echo "reload";
                return;
            }
            echo "Error adding event: $enstmt->error";
            $enstmt->close();
            return;
        }
    }
    
}

$pluginPages[] = new events();