<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of blog
 * Simple blog plugin, can send posts with AJAX
 *
 * @author Cameron
 */
class blog extends optionsPage{
    public $name = "plugin_blog";
    public $title = "Blog";
    
    function setup(){
        
    }
    
    function displayPage(){
        
        global $connection; //Import connection object
        
        if(isset($_GET['id'])){
            $post = filter_input(INPUT_GET,'id');blic.
            $pstmt = $connection->prepare("SELECT title, content, author, timestamp,tags FROM blog WHERE id=?");
            $pstmt->bind_param('i',$post);
            $pstmt->execute();
            $pstmt->store_result();
            $pstmt->bind_result($title,$content,$author,$timestamp,$tags);
            $pstmt->fetch();
            
            $pArray = array("title" => $title, "author"=> $author, "tags" => $tags, "date" => $timestamp, "content" => $content);
            $pJSON = json_encode($pArray);
            echo $pJSON;
            return true;
            
        }else{
            echo "Pamphlet Blogs JSON Request<br />", PHP_EOL;
            echo "&list: Returns list of posts<br />";
            echo "&id=postid: Returns one post's data";
        }
        
        
    }
    
    function configPage(){
        global $connection;
        $ce = new centralElement("ce-medium");
        
        if($post = filter_input(INPUT_GET,'post')){
            /* Single post editor */
            backButton($this->name);
            $pstmt = $connection->prepare("SELECT title, content, author, timestamp,tags FROM blog WHERE id=?");
            $pstmt->bind_param('i',$post);
            $pstmt->execute();
            $pstmt->store_result();
            $pstmt->bind_result($title,$content,$author,$timestamp,$tags);
            $pstmt->fetch();
            $editBlog = new ajaxForm("editBlogForm", $this->name . "&edit=" . $post, "POST");
            $editBlog->formTitle("Edit Blog Post");
            $editBlog->labeledInput("title", "text", $title, "Title");
            $editBlog->labeledInput("tags", "text", $tags, "Tags");
            $editBlog->kpSelector("author", kpFullnames(), $author, "Author");
            $editBlog->largeText("content", $content, "Content");
            $editBlog->otherActionButton("deleteBlog", "Delete post", "&delete=$post");
            $editBlog->submit("Update post");
            $pstmt->close();
            
        }else{
        
            $newBlog = new ajaxForm("newBlogForm", $this->name, "POST");
            $newBlog->formTitle("New blog post");
            $newBlog->labeledInput("title", "text", "", "Title");
            $newBlog->labeledInput("tags", "text", "", "Tags (space separated)");
            $newBlog->kpSelector("author", kpFullnames(), get_username(), "Author");
            $newBlog->largeText("content", "", "Content");
            $newBlog->submit("Add post");
            $newBlog->end();

            $bstmt = $connection->prepare("SELECT b.id,b.title,b.author,b.timestamp,b.tags,u.fullname FROM blog b, users u WHERE b.author = u.username ORDER BY b.timestamp DESC");
            $bstmt->execute();
            $bstmt->bind_result($id,$title,$author,$postdate,$tags,$fullname);
            $blogList = new multiPageList(null, "bloglist");
            while($bstmt->fetch()){
                $nicedate = date("jS F Y",strtotime($postdate));
                $tagString = tagsToString($tags);
                $post = array("Title" => $title, "Author" => $fullname, "Tags" => $tagString, "Posted" => $nicedate, "onclick" => "cm_loadPage('$this->name&post=$id');");
                $blogList->addObject($post);
            }


            $blogList->title("Current posts");
            $blogList->display($this->name);
            $bstmt->close();
        }
        $ce->end();
        
    }
    function updatePage(){
        global $connection;
        
        $title = filter_input(INPUT_POST,"title");
        $tags = filter_input(INPUT_POST,"tags");
        $author = filter_input(INPUT_POST,"author");
        $content = content("content");
        
        if(isset($_GET['delete'])){
            
            $delete = filter_input(INPUT_GET,"delete");
            if(!blogAuthor($delete)){
                block(2);
            }
            block(3);
            
            $bdstmt = $connection->prepare("DELETE FROM blog WHERE id=?");
            $bdstmt->bind_param("i",$delete);
            if($bdstmt->execute()){
                echo "reload";
            }else{
                echo "Unable to delete post: $bdstmt->error";
            }
        }
        else if(isset($_GET['edit'])){
            if(!blogAuthor($delete)){
                block(2);
            }
            block(3);
            $edit = filter_input(INPUT_GET,"edit");
            //Editing existing post
            $bstmt = $connection->prepare("UPDATE blog SET title=?, author=?, tags=?, content=? WHERE id=?");
            $bstmt->bind_param("ssssi",$title,$author,$tags,$content,$edit);
            if($bstmt->execute()){
                echo "Saved changes";
                return;
            }
            echo "Error saving changes: $bstmt->error";
            return;
            
        }
        else{
            block(3);
            if(profile(get_username()['privilege']) < 3){
                $author = get_username();
            }
            //Creating new post
            if(!$title || !$author){
                echo "Please enter a title";
                return;
            }else{
                $nbstmt = $connection->prepare("INSERT INTO blog (title,content,author,tags) VALUES (?,?,?,?);");
                $nbstmt->bind_param("ssss",$title,$content,$author,$tags);
                if($nbstmt->execute()){
                    echo "reload";
                    return;
                }
                echo "Error adding post: $nbstmt->error";
            }
        }
    }
    function orphanage($olduser,$newuser){
        
    }
    
}

$plugin_blog = new blog();
$pluginPages[] = $plugin_blog;
