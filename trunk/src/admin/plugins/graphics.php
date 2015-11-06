<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class scoreboard extends optionsPage{
    //todo
    public $name = "plugin_scoreboard";
    public $title = "Scoreboard";
    
    public function displayPage(){
        if(isset($_GET['data'])){
            $id = filter_input(INPUT_GET,'data');
        }else if(isset($_GET['id'])){
            $id = filter_input(INPUT_GET,'id');
            include('plugins/graphics/scoreboard.php');
            
            $data = self::teams($id);
            if($data){
                $team1name = $data[0];
                $team1img = $data[1];
                $team2name = $data[2];
                $team2img = $data[3];
                graphics_scoreboard::build($id, $team1name, $team1img, $team2name, $team2img);
            }else{
                echo "Fatal error! No team data received";
            }
            
        }
    }
    
    public function configPage(){
        
        global $connection;
        
        if(isset($_GET['edit'])){
            
            global $connection;
            $id = filter_input(INPUT_GET,'edit');
            
            ce::begin();
            backButton($this->name);
            
            if($stmt = $connection->prepare("SELECT id,name,sport,team1name,team1img,team1score,team2name,team2img,team2score,trunning FROM plugin_scoreboard WHERE id=?")){
                $stmt->bind_param('i',$id);
                $stmt->execute();
                $stmt->bind_result($id,$name,$sport,$team1name,$team1img,$team1score,$team2name,$team2img,$team2score,$trunning);
                $stmt->fetch();
                $stmt->close();

                $t1= new ajaxForm("settingsForm","$this->name&settings=$id","POST");
                $t1->formTitle("Scoreboard settings");
                $t1->linkButton("controlButton", "Go to controller", "$this->name&control=$id");
                $t1->labeledInput("name", "text", $name, "Scoreboard Name");
                $t1->kpSelector("sport", self::kpSports(), $sport, "Sport");
                $t1->infoRow("Team 1");
                $t1->labeledInput("team1name", "text", $team1name, "Name");
                $t1->labeledInput("team1img", "url", $team1img, "Image");
                $t1->infoRow("Team 2");
                $t1->labeledInput("team1name", "text", $team2name, "Name");
                $t1->labeledInput("team1img", "url", $team2img, "Image");
                $t1->lockedInput(actualLink() . "/public.php?action=$this->name&id=$id", "External URL");
                $t1->submit("Update details");
                
                
            }else{
                echo "Error - query refused by server. Ensure the database is setup correctly.";
            }
            ce::end();
        }
        
        else if(isset($_GET['control'])){
            $id = filter_input(INPUT_GET,'control');
            ce::begin('style="width:800px;"');
            backButton("$this->name&edit=$id");
            $t1 = new ajaxForm("team1Control","$this->name&control=$id&team1","POST");
            $t1->formTitle("Scoreboard control");
            $t1->infoRow("Team 1");
            $t1->labeledInput("t1score", "number",0, "Score update amount");
            $t1->clipboardSubmit("Update", "Current Score");
            $t2 = new ajaxForm("team2Control","$this->name&control=$id&team2","POST");
            $t2->infoRow("Team 2");
            $t2->labeledInput("t2score", "number",0, "Score update amount");
            $t2->clipboardSubmit("Update", "Current Score");
            $tt = new ajaxForm("timerControl","$this->name&control=$id&timer","POST");
            $tt->infoRow("Timer control");
            $tt->submit("Start/stop");
            
            $tr = new ajaxForm("resetForm","","");
            $tr->formTitle("Reset things");
            $tr->otherActionButton("resetTeam1", "Reset Team 1 Score", "$this->name&control&reset=1");
            $tr->otherActionButton("resetTeam2", "Reset Team 2 Score", "$this->name&control&reset=2");
            ce::end();
        }
        else{
            
            
            ce::begin("ce-medium");
            $list = new multiPageList(self::getScoreboards(),'scoreboard_list');
            $list->title("Active scoreboards");
            $list->display($this->name);
            ce::end();
        }
    }
    public function updatePage(){
        if(isset($_GET['settings'])){
            
        }else if(isset($_GET['control'])){
            $id = filter_input(INPUT_GET,'control');
            $amount = intval($_POST['t1score']);
            if(isset($_GET['team1'])){
                echo self::incrementScore($id,1,$amount);
            }else if(isset($_GET['team2'])){
                echo self::incrementScore($id,2,$amount);
            }
        }
    }
    
    public static function incrementScore($scoreboard,$team,$amount){
        global $connection;
        /* This is disgusting but it works */
        if($team == 1){
            $stmt1 = $connection->prepare("SELECT team1score FROM plugin_scoreboard WHERE id=?");
            $stmt1->bind_param('i',$scoreboard);
            $stmt1->execute();
            $stmt1->bind_result($oldscore);
            $stmt1->fetch();
            $stmt1->close();
            
            $newscore = $oldscore + $amount;
            
            $stmt2 = $connection->prepare("UPDATE plugin_scoreboard SET team1score=? WHERE id=?");
            $stmt2->bind_param('ii',$newscore,$scoreboard);
            $stmt2->execute();
            $stmt2->close();
        }else if($team == 2){
            $stmt1 = $connection->prepare("SELECT team2score FROM plugin_scoreboard WHERE id=?");
            $stmt1->bind_param('i',$scoreboard);
            $stmt1->execute();
            $stmt1->bind_result($oldscore);
            $stmt1->fetch();
            $stmt1->close();
            
            $newscore = $oldscore + $amount;
            
            $stmt2 = $connection->prepare("UPDATE plugin_scoreboard SET team2score=? WHERE id=?");
            $stmt2->bind_param('ii',$newscore,$scoreboard);
            $stmt2->execute();
            $stmt2->close();
        }else{
            return "There is no team $team!";
        }
        return $newscore;
    }
    
    public static function getScoreboards(){
        global $connection;
        
        $stmt = $connection->prepare("SELECT id,name,sport FROM plugin_scoreboard");
        $stmt->execute();
        $stmt->bind_result($id,$name,$sport);
        $results = array();
        while($stmt->fetch()){
            $onclick = "cm_loadPage('plugin_scoreboard&edit=$id');";
            $results[] = array("Name" => $name, "Sport" => $sport, "onclick" => $onclick);
        }
        $stmt->close();
        return $results;
    }
    
    public static function teams($id){
        
        global $connection;
        
        if($stmt = $connection->prepare("SELECT team1name,team1img,team2name,team2img FROM plugin_scoreboard WHERE id=?")){
                $stmt->bind_param('i',$id);
                $stmt->execute();
                $stmt->bind_result($team1name,$team1img,$team2name,$team2img);
                $stmt->fetch();
                $stmt->close();
                
                return array($team1name, $team1img, $team2name, $team2img);
                
        }else{
            return false;
        }
    }
    
    public static function kpSports(){
        return array("basketball" => "Basketball");
    }
}

$pluginPages[] = new scoreboard();