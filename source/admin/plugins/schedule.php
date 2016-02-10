<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class shows extends optionsPage{
    public $name = 'shows';
    public $title = 'Shows';
    
    public function displayPage(){
       global $connection;
    }
    public function configPage() {
        global $connection;
        ce::begin();
        if(isset($_GET['edit'])){
            backButton($this->name);
            $id = $_GET['edit'];
            $array = self::getShowById($id);
            $eform = new customForm(self::getShowById($id), 'editShowForm', $this->name . '&request=edit&id=' . $id, 'POST', $this->name . "&edit=$id");
            $eform->setTitle('Edit show');
            $eform->build('Save changes');
            
            $iform = new customForm(self::instanceFormArray(), 'newInstanceForm', $this->name . '&request=newInstance', 'POST', $this->name . "&edit=$id");
            $iform->setTitle('New instance');
            $iform->build('New instance');
        }else{

            $nForm = new customForm(self::showFormArray(), 'newShowForm', $this->name . '&request=new', 'POST', $this->name);
            $nForm->setTitle('New show');
            $nForm->build('Add new show');

            $list = new ajaxList(self::getAllShows(),'showList');
            $list->display();

            
        }
        ce::end();
    }
    
    public function updatePage() {
        global $connection;
        block(3);
        switch($_GET['request']){
            case 'new':
                $query = customForm::insertSQL(self::showFormArray(), $_POST, 'schedule_shows');
                if($connection->query($query)){
                    echo 'reload';
                }else{
                    //echo $query;
                    echo 'Error adding show';
                }
                break;
            case 'edit':
                $id = $_GET['id'];
                $query = customForm::updateSQL(self::showFormArray(),$_POST,'schedule_shows', "id=$id");
                if($connection->query($query)){
                    echo 'Saved changes';
                }else{
                    echo $query;
                    echo 'Error saving changes';
                }
                break;
            case 'delete':
                break;
            default:
                echo 'Error - request not specified';
        }
        
        
    }
    
    public static function showFormArray(){
        return [
            'title' => ['type' => 'text', 'label' => 'Title', 'value' => ''],
            'poster_url' => ['type' => 'url', 'label' => 'Poster image', 'value' => ''],
            'theme_colour' => ['type' => 'color', 'label' => 'Theme colour', 'value' => '#FFFFFF'],
            'description' => ['type' => 'richtext', 'label' => 'Description', 'value' => '']
        ];
    }
    
    public static function instanceFormArray(){
        return [
            'schedule_id' => ['type' => 'select', 'options' => schedule::kvpSchedules(), 'label' => 'Schedule', 'value' => ''],
            'recurrence' => ['type' => 'select', 'options' => self::intervals(), 'label' => 'Frequency', 'value' => '0'],
            'length' => ['type' => 'number', 'label' => 'Length (minutes)', 'value' => '60'],
            'start' => ['type' => 'date', 'label' => 'Start date']
        ];
    }
    public static function rawGetShows(){
        global $connection;
        
        $array = array();
        if($result = $connection->query('SELECT * FROM schedule_shows;')){
            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $array[] = $row;
            }
            $result->close();
            return $array;

        }else{
            return false;
        }
    }
    
    public static function getAllShows(){
        $raw = self::rawGetShows();
        $clean = [];
        foreach($raw as $show){
            $id = $show['id'];
            $onclick = "cm_loadPage('shows&edit=$id');";
            $colour = $show['theme_colour'];
            $colourbox = "<div style=\"background-color:$colour; width:100%; height:80%;\" ></div>";
            $clean[] = ['Title' => $show['title'], 'Poster URL' => $show['poster_url'], 'Theme colour' => $colourbox, 'onclick' => $onclick];
        }
        return $clean;
    }
    
    public static function getShowById($id){
        global $connection;
        
        if($result = $connection->query("SELECT * FROM schedule_shows WHERE id=$id;")){
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $formarray = customForm::getEditForm(self::showFormArray(), $row);
            return $formarray;
        }else{
            return false;
        }
    }
    
    public static function kvpGetShows(){
        $array = [0 => '--'];
        foreach(self::rawGetShows() as $show){
            $array[$show['id']] = $show['title'];
        }
        return $array;
    }
    
    public static function intervals(){
        return [
            0 => 'One-time event',
            3600 => 'Hourly',
            86400 => 'Daily',
            604800 => 'Weekly',
            1209600 => 'Fortnightly',
            2419200 => 'Monthly (Every 4 weeks)'
        ];
    }
    
}

$pluginPages[] = new shows();

class schedule extends optionsPage{
    public $name = 'schedule';
    public $title = 'Scheduling';
    
    public function displayPage(){
        
    }
    public function configPage() {
       
    }
    
    public function updatePage() {
    }
    
    public static function rawGetSchedules(){
        global $connection;
        
        $array = array();
        if($result = $connection->query('SELECT * FROM schedule;')){
            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $array[] = $row;
            }
            $result->close();
            return $array;

        }else{
            return false;
        }
    }
    
    public static function kvpSchedules(){
        $array = ['null' => 'Inactive'];
        foreach(self::rawGetSchedules() as $schedule){
            $id = $schedule['id'];
            $array[$id] = $schedule['title'];
        }
        return $array;
    }
}

$pluginPages[] = new schedule();