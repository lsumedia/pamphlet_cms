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
        ce::begin("style=\"max-width:800px;\"");
        if(isset($_GET['edit'])){
            backButton($this->name);
            $id = $_GET['edit'];
            $array = self::getShowById($id);
            $array['delete'] = ['type' => 'button', 'label' => 'Delete', 'action' => 'shows&request=delete&id=' . $id];
            $eform = new customForm($array, 'editShowForm', $this->name . '&request=edit&id=' . $id, 'POST', $this->name);
            $eform->setTitle('Edit show');
            $eform->build('Save changes');
            
            $instanceFormArray = self::instanceFormArray();
            $instanceFormArray['show_id']['value'] = $id;
            $iform = new customForm($instanceFormArray, 'newInstanceForm', $this->name . '&request=newinstance', 'POST', $this->name . "&edit=$id");
            $iform->setTitle('New instance');
            $iform->build('New instance');
            
            $ilist = new ajaxList(self::cleanGetInstancesByShow($id),'instanceList');
            $ilist->title($text);
            $ilist->display();
        }elseif(isset($_GET['editinstance'])){
            $id = $_GET['editinstance'];
            $data = self::getInstanceById($id);
            $show_id = $data['show_id']['value'];
            $show_data = self::getShowById($show_id);
            $show_title = $show_data['title']['value'];
            backButton('shows&edit=' . $show_id);
            $data['delete'] = ['type' => 'button', 'label' => 'Delete', 'action' => 'shows&request=deleteinstance&id=' . $id]; 
            $iform = new customForm($data, 'instanceEditFom', $this->name . '&request=editinstance&id=' . $id, 'POST', 'shows&edit=' . $show_id);
            $iform->setTitle('Editing instance of ' . $show_title);
            $iform->build('Save changes');
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
            case 'newinstance':
                $validate = customForm::simpleArray(self::instanceFormArray(), $_POST);
                if($validate['show_id'] ==  0 ){
                    echo 'Please select a show';
                    break;
                }
                $query = customForm::insertSQL(shows::instanceFormArray(), $_POST, 'schedule_instance');
                if($connection->query($query)){
                    echo 'reload';
                }else{
                    //echo $query;
                    echo 'Error adding show';
                }
                break;
            case 'editinstance':
                $id = $_GET['id'];
                $query = customForm::updateSQL(shows::instanceFormArray(), $_POST, 'schedule_instance', "instance_id='$id'");
                //echo $query; break;
                if($connection->query($query)){
                    echo 'Saved changes';
                }else{
                    echo 'Error saving changes';
                }
                break;
            case 'deleteinstance':
                $id = intval($_GET['id']);
                $query = "DELETE FROM schedule_instance WHERE instance_id=$id;";
                if($connection->query($query)){
                    echo 'reload';
                }else{
                    echo 'Error deleting instance';
                }
                break;
            case 'new':
                $validate = customForm::simpleArray(self::showFormArray(), $_POST);
                if(strlen($validate['title']) < 1 ){
                    echo 'Please enter a title';
                    break;
                }
                $query = customForm::insertSQL(self::showFormArray(), $_POST, 'schedule_shows');
                if($connection->query($query)){
                    echo 'reload';
                }else{
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
                $id = intval($_GET['id']);
                $query = "DELETE FROM schedule_shows WHERE id=$id;";
                if($connection->query($query)){
                    echo 'reload';
                }else{
                    echo 'Error deleting show';
                }
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
            'tag' => ['type' => 'text', 'label' => 'Show category ID', 'value' => ''],
            'description' => ['type' => 'richtext', 'label' => 'Description', 'value' => '']
        ];
    }
    
    public static function instanceFormArray(){
        return [
            'show_id' => ['type' => 'hidden','options' => self::kvpGetShows(), 'label' => 'Show'],
            'schedule_id' => ['type' => 'select', 'options' => schedule::kvpSchedules(), 'label' => 'Schedule', 'value' => '0'],
            'first' => ['type' => 'date', 'label' => 'Start date', 'value' => date('Y-m-d')],
            'frequency' => ['type' => 'select', 'options' => self::intervals(), 'label' => 'Frequency'],
            'start_time' => ['type' => 'time', 'label' => 'Start time'],
            'end_time' => ['type' => 'time', 'label' => 'End time'],
            'description' => ['type' => 'richtext', 'label' => 'Description (optional)', 'value' => '']
        ];
    }
    
    public static function intervals(){
        return[
            0 => 'One-time',
            1 => 'Daily',
            7 => 'Weekly',
            14 => 'Fortnightly',
        ];
    }
    
    /* Get raw data for all shows */
    public static function rawGetShows(){
        global $connection;
        
        $array = array();
        if($result = $connection->query('SELECT * FROM schedule_shows ORDER BY title ASC;')){
            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $array[] = $row;
            }
            $result->close();
            return $array;

        }else{
            return false;
        }
    }
    
    /* Get list-safe data for all shows */
    public static function getAllShows(){
        $raw = self::rawGetShows();
        $clean = [];
        foreach($raw as $show){
            $id = $show['id'];
            $onclick = "cm_loadPage('shows&edit=$id');";
            $colour = $show['theme_colour'];
            $colourbox = "<div style=\"background-color:$colour; width:100%; height:80%;\" ></div>";
            $clean[] = ['Title' => $show['title'], 'Poster URL' => $show['poster_url'], 'Tag'  => $show['tag'], 'Theme colour' => $colourbox, 'onclick' => $onclick];
        }
        return $clean;
    }
    
    public static function rawGetShowById($id){
        global $connection;
        if($result = $connection->query("SELECT * FROM schedule_shows WHERE id=$id;")){
            $row = $result->fetch_array(MYSQLI_ASSOC);
            return $row;
        }else{
            return false;
        }
    }
    /* Get edit form show data for one show */
    public static function getShowById($id){
        global $connection;
        if($raw = self::rawGetShowById($id)){
            return customForm::getEditForm(self::showFormArray(), $raw);
        }
        return false;
    }
    
    /* Get show IDs and names in a key-value pair array */
    public static function kvpGetShows(){
        $array = [0 => '--'];
        foreach(self::rawGetShows() as $show){
            $array[$show['id']] = $show['title'];
        }
        return $array;
    }
    
    /** Get raw data of all instances of a particular show */
    public static function getInstancesByShow($showid){
        global $connection;
        $showid = intval($showid);
        $array = array();
        if($result = $connection->query("SELECT * FROM schedule_instance WHERE show_id='$showid';")){
            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $array[] = $row;
            }
            $result->close();
            return $array;

        }else{
            return false;
        }
    }
    
    public static function cleanGetInstancesByShow($showid){
        $raw = self::getInstancesByShow($showid);
        $clean = [];
        foreach($raw as $instance){
            $id = $instance['instance_id'];
            $onclick = "cm_loadPage('shows&editinstance=$id');";
            $showname = self::kvpGetShows()[$instance['show_id']];
            $frequency = self::intervals()[$instance['frequency']];
            $timestamp = strtotime($instance['first']);
            $date = date('D, jS M Y',$timestamp);
            $iclean = ['Start date' => $date, 'Start' => $instance['start_time'], 'End' => $instance['end_time'], 'Frequency' => $frequency, 'onclick' => $onclick];
            $schedule = schedule::kvpSchedules()[$instance['schedule_id']];
            $clean[] = $iclean;
        }
        return $clean;
    }
    
    /* Get raw instance data based on unique instance_id */
    public static function getRawInstanceByID($id){
        global $connection;
        $id = intval($id);  //Make sure it's an integer to prevent injection
        $final_result;
        if($result = $connection->query("SELECT * FROM schedule_instance WHERE instance_id='$id';")){
            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $final_result = $row;
            }
            $result->close();
            return $final_result;

        }else{
            return false;
        }
    }
    
    /* Get edit form for an instance based on ID */
    public static function getInstanceById($id){
        $raw = self::getRawInstanceByID($id);
        return customForm::getEditForm(self::instanceFormArray(), $raw);
    }
}

$pluginPages[] = new shows();

class schedule extends optionsPage{
    public $name = 'schedule';
    public $title = 'Scheduling';
    
    public function displayPage(){
        
    }
    
    public function configPage() {
        ce::begin();
        if(isset($_GET['edit'])){
            backButton($this->name);
            $sid = $_GET['edit'];
            $events = self::cleanGetInstancesBySchedule($sid);
            
            
            $edata = shows::instanceFormArray();
            $edata['show_id']['type'] = 'select';
            $edata['schedule_id']['type'] = 'hidden';
            $edata['schedule_id']['value'] = $sid;
            
            $eform = new customForm($edata, 'newEventForm', 'shows&request=newinstance', 'POST', $this->name . '&edit=' . $sid);
            $eform->setTitle('New event');
            $eform->build('Add event');
            
            $list = new ajaxList($events,'eventList');
            $list->title('Shows occuring in this schedule');
            $list->display();
        }elseif(isset($_GET['editinstance'])){
            $id = $_GET['editinstance'];
            $data = shows::getInstanceById($id);
            $show_id = $data['show_id']['value'];
            $schedule_id = $data['schedule_id']['value'];
            $show_data = shows::getShowById($show_id);
            $show_title = $show_data['title']['value'];
            backButton('schedule&edit=' . $schedule_id);
            $data['delete'] = ['type' => 'button', 'label' => 'Delete', 'action' => 'shows&request=deleteinstance&id=' . $id]; 
            $iform = new customForm($data, 'instanceEditFom', 'shows&request=editinstance&id=' . $id, 'POST', 'schedule&edit=' . $schedule_id);
            $iform->setTitle('Editing instance of ' . $show_title);
            $iform->build('Save changes');
        }else{
            $form = new customForm(self::formArray(), 'newScheduleForm', $this->name . '&request=new', 'POST', $this->name);
            $form->setTitle('New schedule');
            $form->build('Add new schedule');
            
            $list = new ajaxList(self::getAllSchedules(),'scheduleList');
            $list->title('All schedules');
            $list->display();
        }
       ce:end();
    }
    
    public function updatePage() {
        global $connection;
        block(3);
        switch($_GET['request']){
            case 'new':
                $validate = customForm::simpleArray(self::formArray(), $_POST);
                if(strlen($validate['title']) < 1 ){
                    echo 'Please enter a title';
                    break;
                }
                $query = customForm::insertSQL(self::formArray(), $_POST, 'schedule');
                if($connection->query($query)){
                    echo 'reload';
                }else{
                    echo 'Error adding schedule';
                }
                break;
            case 'edit':
                $id = $_GET['id'];
                $query = customForm::updateSQL(self::formArray(),$_POST,'schedule', "id=$id");
                if($connection->query($query)){
                    echo 'Saved changes';
                }else{
                    echo $query;
                    echo 'Error saving changes';
                }
                break;
            case 'delete':
                $id = intval($_GET['id']);
                $query = "DELETE FROM schedule WHERE id=$id;";
                if($connection->query($query)){
                    echo 'reload';
                }else{
                    echo 'Error deleting show';
                }
                break;
            default:
                echo 'Error - request not specified';
        }
        
        
    }

    
    public static function processVideo($video,$schedule_id){
        $events = self::getCurrentEvents($schedule_id);
        if($showID = $events[0]['show_id']){
            $show = shows::rawGetShowById($showID);

            //$video->title = $show['title'];
            $video->poster = $show['poster_url'];
            $video->nowplaying = $show['title'];    
            $video->description = $show['description'];
        }
        return $video;
    }
    
    /**
     * Convert a string representing a time to a value representing
     * minutes since midnight
     * @param type $string
     * @return type
     */
    public static function timeToMinutes($time){
        $timestamp = strtotime($time);
        return (intval(date('G',$timestamp)) * 60) + intval(date('i',$timestamp));      
    }
    /** Return array of currently running events */
    public static function getCurrentEvents($schedule_id){
        $events = self::getInstancesBySchedule($schedule_id);
        $matching = array();

        //Day - the current day of the week
        $day = intval(date('N'));
        //Minutes since midnight
        $nowminutes = (intval(date('G')) * 60) + intval(date('i'));      
        
        foreach($events as $event){
            //Timestamp of first occurence of show
            $sts = strtotime($event['first']);
            $sstart = self::timeToMinutes($event['start_time']);
            $send = self::timeToMinutes($event['end_time']);
            //If nowtime is between the show start and end time
            if($sstart <= $nowminutes && $nowminutes < $send){
                $matching[] = $event;
            }
            //$matching[] = array($sstart,$send,$nowminutes);
        }
        return $matching;
    }
    
    public static function formArray(){
        return [
            'title' => ['type' => 'text', 'label' => 'Title', 'value' => ''],
            'timezone' => ['type' => 'number', 'label' => 'Time zone', 'value' => 0],
            'replace_title' => ['type' => 'checkbox', 'label' => 'Replace title', 'value' => 0],
            'replace_nowplaying' => ['type' => 'checkbox', 'label' => 'Replace nowplaying', 'value' => 0],
            'replace_description' => ['type' => 'checkbox', 'label' => 'Replace description', 'value' => 0]
        ];
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
    
    public static function getAllSchedules(){
        $raw = self::rawGetSchedules();
        $clean = [];
        foreach($raw as $schedule){
            $id = $schedule['id'];
            $onclick = "cm_loadPage('schedule&edit=$id');";
            $colourbox = "<div style=\"background-color:$colour; width:100%; height:80%;\" ></div>";
            $clean[] = ['Title' => $schedule['title'], 'onclick' => $onclick];
        }
        return $clean;
    }
    
    public static function kvpSchedules(){
        $array = ['null' => 'Inactive'];
        foreach(self::rawGetSchedules() as $schedule){
            $id = $schedule['id'];
            $array[$id] = $schedule['title'];
        }
        return $array;
    }
    
    public static function getInstancesBySchedule($schedule_id){
        global $connection;
        $schedule_id = intval($schedule_id);
        $array = array();
        if($result = $connection->query("SELECT * FROM schedule_instance WHERE schedule_id='$schedule_id';")){
            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $array[] = $row;
            }
            $result->close();
            return $array;

        }else{
            return false;
        }
    }
    
    public static function cleanGetInstancesBySchedule($sid){
        $raw = self::getInstancesBySchedule($sid);
        $clean = [];
        foreach($raw as $instance){
            $id = $instance['instance_id'];
            $onclick = "cm_loadPage('schedule&editinstance=$id');";
            $showname = shows::kvpGetShows()[$instance['show_id']];
            $frequency = shows::intervals()[$instance['frequency']];
            $timestamp = strtotime($instance['first']);
            $date = date('D, jS M Y',$timestamp);
            $iclean = ['Show name' => $showname, 'Start date' => $date, 'Start' => $instance['start_time'], 'End' => $instance['end_time'], 'Frequency' => $frequency, 'onclick' => $onclick];
            $schedule = self::kvpSchedules()[$instance['schedule_id']];
            $clean[] = $iclean;
        }
        return $clean;
    }
}

$pluginPages[] = new schedule();