<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of committee
 *
 * @author Cameron
 */
class committee extends optionsPage {
    public $name = "plugin_committee";
    public $title = "Committee";
    
    function configPage(){
        global $connection;
        $ce = new centralElement("ce-medium");
        
        if($id = filter_input(INPUT_GET,'position')){
            backButton($this->name);
            $cpstmt = $connection->prepare("SELECT position,username,description FROM committee WHERE id=?");
            $cpstmt->bind_param("i",$id);
            $cpstmt->execute();
            $cpstmt->store_result();
            $cpstmt->bind_result($posName, $username, $description);
            $cpstmt->fetch();
            $cpstmt->close();
            
            $cpForm = new ajaxForm("committeeForm", $this->name . "&edit=" . $id, "POST");
            $cpForm->formTitle("Edit committee position");
            $cpForm->labeledInput("position", "text", $posName, "Position name");
            $cpForm->kpSelector("username", kpFullnames(), $username, "Current holder");
            $cpForm->largeText("description", $description, "Position description");
            $cpForm->submit("Save changes");
        }else{
            $cstmt = $connection->prepare("SELECT c.id, c.position, c.username, u.fullname FROM committee c, users u WHERE c.username = u.username");
            $cstmt->execute();
            $cstmt->bind_result($id,$position,$username,$fullname);
            $cList = new ajaxList(null, "committeeList");
            while($cstmt->fetch()){
                $member = ["Position" => $position, "Name" => $fullname, "action" => "$this->name&position=$id"];
                $cList->addObject($member);
            }
            $cList->title("Committee");
            $cList->display();
        }
        $ce->end();
    }
    function updatePage() {
        global $connection;
        if($edit = filter_input(INPUT_GET,"edit")){
            block(2);
            $position = filter_input(INPUT_POST,"position");
            $username = filter_input(INPUT_POST,"username");
            $description = urldecode(filter_input(INPUT_POST,"description"));
            $cestmt = $connection->prepare("UPDATE committee SET position=?, username=?, description=? WHERE id=? ");
            $cestmt->bind_param("sssi",$position,$username,$description,$edit);
            if($cestmt->execute()){
                echo "Saved changes";
                return;
            }
            echo "Error applying changes";
        }
        else{ echo "Invalid request received"; }
    }
}

$plugin_committee = new committee();

$pluginPages[] = $plugin_committee;
