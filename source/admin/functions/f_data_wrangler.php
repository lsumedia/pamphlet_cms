<?php
//PHP data accesibility functions

$hash_method = "SHA256";

function today(){
	return date('jS F Y');
}

/*
function get_username(){
	//
        session_start();
	if($_SESSION['username']){
		return $_SESSION['username'];
	}
	return false;
}*/

//Horrid hack
function loggedIn(){
    return true;
}

class DB {

    public static function fetch($query){
        
        /*
         * This method does not prevent against inject, please use native mysqli_stmt for
         * any calls where the user inputs raw data, or at least escape the query inputs
         * 
         */
        
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $stmt->store_result();
        
        $res = $stmt->fetch_result();
        $resarray = array();
        while($row = $res->fetch_array(MYSQLI_ASSOC)){
            $resarray[] = $row;
        }
        return $resarray;
        
    }
    
    /* todo
    public static function count($table){
        
    }
    
    public static function insert($table,$columns){
        
    }
    
    public static function update($table,$id,$columns){
        
    }
    */
    
    /**
     * Turn array into an escaped mysql UPDATE query
     * Can be used directly with $_POST
     * 
     * 
     * @param string $table_name
     * @param array $array
     * @param string $condition
     * @return string
     */
    public static function array_to_update_query($table_name,$array, $condition){
        
        $query = "UPDATE $table SET ";
        $first = true;  //For first run
        foreach($array as $key->$value){
            if($first){ $comma == ',';} else{ $comma = ''; }
            $first = false;
            $escaped_key = mysql_real_escape_string($key);
            $escaped_value = mysql_real_escape_string($content);
            $query .= "$comma $escaped_key'='$escaped_value',";
        }
        $query .= $condition;
        return $query;
    }
}

function fullname($username){
	return profile($username)->fullname;
}

function onMobile(){
    $iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
    $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
    $palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
    $berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
    $ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
    return ($iphone || $android || $palmpre || $berry || $ipod)? true : false;
}


function content($name){
    return urldecode(filter_input(INPUT_POST,$name));
}

/* Date functions */

$days = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");

function days(){
    return [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    ];
}

function nicedate($numdate){
	$month = date("l jS F Y",strtotime($numdate));
	return $month;
}
function combodate($startdate,$enddate){
	$sdate = strtotime($startdate);
	$edate = strtotime($enddate);
	if($edate < $sdate){
		return date("jS F Y",strtotime($startdate));
	}
	if(date("Y",$sdate) != date("Y",$edate)){
		return nicedate($startdate) . " - " . nicedate($enddate);
	}else if(date("M",$sdate) != date("M",$edate)){
		return date("jS F", $sdate) . " - " . date("jS F",$edate) . " " . date("Y",$edate); 
	}
	else if(date("Y-m-d",$sdate) == date("Y-m-d",$edate)){
		return date("jS F Y",$sdate);
	}
	return date("jS",$sdate) . " - " . date("jS",$edate). " " . date("F Y",$edate);
}

/* Tag handling functions */

function tagsToString($rawTags){
    $tagList = split(" ", $rawTags);
    $tagString = "";
    foreach($tagList as $key => $tag){
        if($key == 0){
            $tagString .= $tag;
        }else{
            $tagString .= ", " . $tag;
        }
    }
    return $tagString;
}

function allUsernames(){
    global $connection;
    $stmt = $connection->prepare("SELECT username FROM users");
    $stmt->execute();
    $users = array();
    $stmt->bind_result($user);
    while($stmt->fetch()){
        $users[] = $user;
    }
    $stmt->close();
    return $users;
}
function kpFullnames(){
    /* Array of all usernames as key-pair values */
    global $connection;
    $stmt = $connection->prepare("SELECT username,fullname FROM users ORDER BY reg_date DESC");
    $stmt->execute();
    $users = array();
    $stmt->bind_result($username,$fullname);
    while($stmt->fetch()){
        $users[$username] = $fullname;
    }
    $stmt->close();
    return $users;
}

function kpOtherFullnames($username){
    $allnames = kpFullnames();
    unset($allnames[$username]);
    return $allnames;
}

function eventCats(){
    global $connection;
    $stmt = $connection->prepare("SELECT DISTINCT type FROM events");
    $stmt->execute();
    $cats = array();
    $stmt->bind_result($cat);
    while($stmt->fetch()){
        $cats[] = $cat;
    }
    $stmt->close();
    return $cats;
}

function kpPermissionLevels(){
    return array("1" => "Administrator", "2" => "Editor", "3" => "Writer", "4" => "Subscriber", "5" => "Disabled");
}

function parentPages($exclude){
    global $connection;
    $stmt = $connection->prepare("SELECT id, title, parent FROM pages");
    $stmt->execute();
    $pages = array();
    $pages[" "] = "--";
    $stmt->bind_result($id,$title,$parent);
    while($stmt->fetch()){
        if($id != $exclude){
            $pages[$id] = $title;
        }
    }
    return $pages;
}

function actualLink(){
    $full_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $link_array = explode("/",$full_link);  //Split array into sections
    array_pop($link_array); //Remove request.php bit from array
    return implode("/",$link_array);
}

function pm_plugins_dir(){
    return actualLink() . '/plugins/';
}

function pm_installation_name(){
    
}
?>