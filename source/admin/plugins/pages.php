<?php
class pages extends optionsPage{
    public $name = "plugin_pages";
    public $title = "Pages";
    //Display function
    function displayPage(){

    }

    //Edit function
    //Use javascript method getPage(url) to pull pages rather than hyperlinks
    function configPage(){
            //Function for listing pages
            global $connection;
            $ce = new centralElement("ce-medium");
            //New page form
            if(isset($_GET['edit'])){
                backButton($this->name);
                $edit = filter_input(INPUT_GET,"edit");
                $peForm = new ajaxForm("editPage", $this->name . "&edit=" . $edit , $method);
                $pstmt = $connection->prepare("SELECT title,content,parent,visible FROM pages WHERE id=?");
                $pstmt->bind_param("i",$edit);
                $pstmt->execute();
                $pstmt->store_result();
                $pstmt->bind_result($title,$content,$parent,$visible);
                $pstmt->fetch();
                $peForm->formTitle("Edit page");
                $peForm->labeledInput("title", "text", $title, "Title");
                $peForm->kpSelector("parent", parentPages($edit), $parent, "Parent page (optional)");
                $peForm->checkBox("visible", $visible, "Visible");
                $peForm->largeText("content", $content, "Content");
                $peForm->otherActionButton("deleteButton", "Delete page", "&delete=" . $edit);
                $peForm->submit("Update page");
            }else{
                $pForm = new ajaxForm("newPage", $this->name, "POST");
                $pForm->formTitle("New page");
                $pForm->labeledInput("title", "text", "", "Title");     //Title box
                $pForm->kpSelector("parent", parentPages(null), "", "Parent page (optional)");
                $pForm->checkBox("visible", "1", "Visible");            //Visible checkbox
                $pForm->largeText("content", "", "Content");            //Content area
                $pForm->submit("Add page");                             //Submit button
                //Existing page liist
                $pList = new ajaxList(null, "pageList");
                $pList->title("All pages");
                $pstmt = $connection->prepare("SELECT id,title,content,parent,visible FROM pages");
                $pstmt->execute();
                $pstmt->store_result();
                $pstmt->bind_result($id,$title,$content,$parent,$visible);
                while($pstmt->fetch()){
                    $shortContent = substr(htmlspecialchars($content),0,140);
                    if($visible == 0){ $visible = "No"; }else{ $visible = "Yes"; }
                    if($parent == ""){ $parent = "None"; }
                    $onclick = "cm_loadPage('$this->name"."&edit=$id')";
                    $pList->addObject(array("Title" => $title, "Visible" => $visible, "Parent" => $parent, "onclick" => $onclick ));
                }
                $pList->display();
            }
            $ce->end();
    }

    //Place update methods/XML return stuff here
    //This will be called using AJAX
    function updatePage(){	

           global $connection;
           
           block(2);
           
           $title = filter_input(INPUT_POST,"title");
           $parent = filter_input(INPUT_POST,"parent");
           $visible = filter_input(INPUT_POST,"visible");
           $content = filter_input(INPUT_POST,"content");
           
           if(isset($_GET['delete'])){
               //Delete page
               $delete = filter_input(INPUT_GET,"delete");
               $dstmt = $connection->prepare("DELETE FROM pages WHERE id=?");
               $dstmt->bind_param("i",$delete);
               if($dstmt->execute()){
                   echo "reload";
                   return;
               }
               echo "Page deletion failed: $dstmt->error";
               return;
           }else if(isset($_GET['edit'])){
               $edit = filter_input(INPUT_GET,"edit");
               $pestmt = $connection->prepare("UPDATE pages SET title=?, content=?, parent=?, visible=? WHERE id=?");
               $pestmt->bind_param("ssssi",$title,$content,$parent,$visible,$edit);
               if($pestmt->execute()){
                   echo "Changes saved";
                   return;
               }
               echo "Error saving changes: $pestmt->error";
               return;
           }else{
               //New page
               $pstmt = $connection->prepare("INSERT INTO pages (title,content,parent,visible) VALUES (?,?,?,?)");
               $pstmt->bind_param("ssss",$title,$content,$parent,$visible);
               if($pstmt->execute()){
                   echo "reload";
                   return;
               }
               echo "Error adding page: $pstmt->error";
               return;
           }
    }
}

$pages = new pages();
$pluginPages[] = $pages;
?>