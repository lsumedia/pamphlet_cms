<?php
//PHP data accesibility functions

$hash_method = "SHA256";

function today(){
	return date('jS F Y');
}

function get_username(){
	//
        session_start();
	if($_SESSION['username']){
		return $_SESSION['username'];
	}
	return false;
}

function loggedIn(){
    if(get_username() != false){ return true;}
    return false;
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
    
    public static function count($table){
        
    }
    
    public static function insert($table,$columns){
        
    }
    
    public static function update($table,$id,$columns){
        
    }
    
    
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

function auth_username($username){
	//
	global $connection;
	/*
	$stmt = $connection->prepare("SELECT username FROM users WHERE username=? OR email=?;");
	$stmt->bind_param('ss',$username,$username);
	$stmt->execute();
	if($stmt->num_rows > 0){
		//User exists
		return true;
	}
	return false;
	*/
	$stmt = $connection->prepare("SELECT username FROM users WHERE username=? OR email=?");
	$stmt->bind_param('ss',$username,$username);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows > 0){
		return true;
	}
	/*
	if($res = mysqli_query($connection,'SELECT username FROM users WHERE username="'.$username.'";')){
		if(mysqli_num_rows($res) > 0){
			return true;
		}
	}*/
	return false;
}

function login_to_username($login){
	//Grab connection object
	global $connection;
	//Prepare statement
	$stmt = $connection->prepare("SELECT username FROM users WHERE username=? OR email = ?");
	$stmt->bind_param('ss',$login,$login);
	$stmt->execute();
	$stmt->bind_result($username);
	$stmt->fetch();
	return $username;
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

function profile($username){
    global $connection;	//Import collection
    //Prepare stuff
    $stmt = $connection->prepare("SELECT fullname, email, dpurl, bio, privilege,reg_date FROM users WHERE username = ?");
    $stmt->bind_param('s',$username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($fullname,$email,$dpurl,$bio,$priv,$regdate);
    if ($stmt->fetch()) {
        //Assign values to an array
        $profile = array('username' => $username, 'email' => $email, 'dpurl' => $dpurl, 'fullname' => $fullname, 'bio' => $bio, "privilege" => $priv, "regdate" => $regdate);
        //Update session variables for unimportant calls
        if ($_SESSION['username'] == $username) {
            $_SESSION['fullname'] = $fullname;
        }
        return $profile;
    } 
    return false;
}

function block($priv){
    /* var $priv - minimum privilege level needed to perform this action (0 is highest, 5 is lowest)*/
    $upriv = profile(get_username())['privilege'];
    if($upriv <= $priv){
        return true;
    }
    echo "You do not have permission to perform this action";
    die();
    return false;
}

function priv(){
     return profile(get_username())['privilege'];
}

function allowed($priv){
    $upriv = profile(get_username())['privilege'];
    if($upriv <= $priv){
        return true;
    }
    return false;
}

function blogAuthor($blog){
    global $connection;
    $stmt = $connection->prepare("SELECT author FROM blog WHERE id=?");
    $stmt->bind_param("i",$blog);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
    if($username == get_username()){
        return true;
    }
    return false;
}

function eventAuthor($event){
    global $connection;
    $stmt = $connection->prepare("SELECT author FROM events WHERE id=?");
    $stmt->bind_param("i",$event);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
    if($username == get_username()){
        return true;
    }
    return false;
}

function username_available($username){
	//
	
	global $connection;
	if($res = mysqli_query($connection,'SELECT username FROM users WHERE username="'.$username.'";')){
		if(mysqli_num_rows($res) == 0){
			return true;
		}
	}else{
		echo "MySQL error: (" . $connection->connect_errno . ") " . $connection->connect_error;
	}
	return false;
}

function email_available($email){
    global $connection;
	if($res = mysqli_query($connection,'SELECT email FROM users WHERE email="'.$email.'";')){
		if(mysqli_num_rows($res) == 0){
			return true;
		}
	}else{
		echo "MySQL error: (" . $connection->connect_errno . ") " . $connection->connect_error;
	}
	return false;
}


function get_password_hash($salt,$password){
	//Join salt and password
        global $hash_method;
	$raw = $salt . $password;
	//SHA-256 hash
        $hash = hash($hash_method,$raw);
        return $hash;
}


function new_password($username,$password){
	//Please use SSL for this
	global $connection;
	//Create salt
	$code = time();
	$salt = sha1($code);
	$hash = get_password_hash($salt,$password);
	//Prepared statement
	$stmt = $connection->prepare("UPDATE users SET salt=?, password=? WHERE username=?");
	$stmt->bind_param('sss',$salt,$hash,$username);
	if($stmt->execute()){
		return "Password changed";
        }
	return "Failed to save password: $stmt->error";
}
function del_user($username){
	global $connection;
	$stmt = $connection->prepare("DELETE FROM users WHERE username=?");
	$stmt->bind_param('s',$username);
	if($stmt->execute()){
		return true;
	}
	return false;
}

function check_password($username,$password){
	//Get salt
	global $connection;
	//Prepare statement
	$stmt = $connection->prepare("SELECT salt FROM users WHERE username=? OR email=?;");
	$stmt->bind_param("ss",$username,$username);
	if(!$stmt->execute()){ echo " Salt query failed: " . $stmt->error; return false;}
	$stmt->bind_result($salt);
	$result = $stmt->fetch();
	$stmt->close();
	$hash = get_password_hash($salt,$password);
	//echo $salt.' '.$hash;
	$stmt2 = $connection->prepare("SELECT username FROM users WHERE salt=? AND password=?");
	$stmt2->bind_param('ss',$salt,$hash);
	if(!$stmt2->execute()){ echo ' Confirm query failed '. $stmt2->error; return false;}
	$stmt2->store_result();
	if($stmt2->num_rows > 0){
		return true;
	}
	return false; //failsafe
}

function new_user($username, $fullname, $priv, $email, $password){
	//TODO - injection security
	global $connection;
	//1 - add entry for user
        $regdate = date("Y-m-d");
	$stmt = $connection->prepare("INSERT INTO users (username, fullname, privilege, email, reg_date) VALUES (?,?,?,?,?);"); 
	$stmt->bind_param('sssss',$username,$fullname,$priv,$email,$regdate);
	if(!$stmt->execute()){
		//echo 'Failed to add new user ('. mysqli_error($connection) . ')';
		return false;
	}else{
		//2 - set user password
		if(!new_password($username,$password)){
			//echo 'Error setting password! Account will be deleted';
                        if(!del_user($username)){ echo "Well, this sucks. Fatal error!"; }
			return false;
		}
	}
	//Report account succesfully created
	//$_SESSION['username'] = $username;
	return true;
}

function num_users(){
	global $connection;
	$query = "SELECT username FROM users";
	if(!$result = mysqli_query($connection,$query)){
		echo mysqli_error($connection);
	}
	return mysqli_num_rows($result);
}


function auth_failed(){
	session_start();
	unset($_SESSION['username']);
	header('location:.');
	die();
}

function kill_user(){		//
	session_start();
	unset($_SESSION['username']);
	$_SESSION = array();
	if(session_destroy()){	//Clear username
		return true;
	}
	return false;
}

function content($name){
    return urldecode(filter_input(INPUT_POST,$name));
}

/* Date functions */

$days = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");

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
?>