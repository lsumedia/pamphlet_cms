<?php
//Run this only from elements.php

$standardPages = array();

class generalSettings extends optionsPage{
	public $name = "general";
	//public $title = "General Settings";
	
        function displayPage(){
            phpinfo();
        }
        
	function configPage(){
		ce::begin();
                $stats = new ajaxForm("stats", "", "");
                $stats->formTitle("Information");
                $stats->lockedInput(num_users(), "Number of users");
                $stats->lockedInput("", "Number of blog posts");
                $stats->lockedInput("", "Number of events");
                echo "</div>", PHP_EOL;
                
                $gForm = new ajaxForm("generalSettigns", $this->name, "POST");
                $gForm->formTitle("Site settings");
                $gForm->labeledInput("stitle", "text", "", "Site title");
                $gForm->largeText("sdescription", "", "Site description");
                $gForm->submit("Save changes");
                
                ce::end();
	}
	function updatePage(){
            block(1);
            echo "Page not configured";
	}
}

$generalSettings = new generalSettings();
$standardPages[] = $generalSettings;

class userSettings extends optionsPage{
	public $name = "user";
	public $title = "Users";
	
	function configPage(){
            global $connection;
                if($username = filter_input(INPUT_GET,"username")){
                    ce::begin();
                    
                    backButton($this->name);
                    $profile = profile($username);
                    $bio = $profile['bio'];
                    $editForm = new ajaxForm("editUserForm", $this->name . "&edit=" . $username, "POST");
                    $editForm->formTitle("Edit profile");
                    $editForm->lockedInput($username, "Username");
                    //$editForm->labeledInput("username","text",$profile['username'],"Username");
                    $editForm->labeledInput("fullname","text",$profile['fullname'],"Full name");
                    $editForm->labeledInput("email","email",$profile['email'],"Email address");
                    $editForm->labeledInput("dpurl","text",$profile['dpurl'],"Profile picture");
                    $editForm->kpSelector("privilege", kpPermissionLevels(), $profile['privilege'], "Permissions level");
                    $editForm->largeText("bio", $bio, "Bio");
                    $editForm->otherActionButton("deleteButton", "Delete user", "&delete=$username");
                    $editForm->submit("Update Profile");
                    
                    $pwordForm = new ajaxForm("passwordForm", $this->name . "&reset=" . $username, "POST");
                    $pwordForm->labeledInput("password", "password", "", "New password");
                    $pwordForm->submit("Change password");
                    
                    ce::end();
                }else{
                    ce::begin();
                    /* New User form section */
                    if(true){
                        //TODO: authentication system
                        //Requires higher permissions
                        //$addDiv = new div("newUser","blockForm");
                        $userForm = new ajaxForm("newUserForm", $this->name, "POST");
                        $userForm->formTitle("Add user");
                        $userForm->labeledInput("username", "text", "", "Username");
                        $userForm->labeledInput("fullname", "text", "", "Full name");
                        $userForm->labeledInput("password", "password", "", "Password ");
                        $userForm->labeledInput("email", "text", "", "Email address");
                        $userForm->kpSelector("privilege", kpPermissionLevels(), "4", "Permissions level");
                        $userForm->submit("Add User");
                        $userForm->end();
                        //$addDiv->close();
                    }
                    
                    $ustmt = $connection->prepare("SELECT username,fullname,email,privilege FROM users ORDER BY reg_date DESC");
                    $ustmt->execute();
                    $ustmt->bind_result($username,$fullname,$email,$priv);
                    $usersList = new ajaxList(null,"usersList");
                    $usersList->style("style=\"min-width:500px;\"");
                    while($ustmt->fetch()){
                        $userArray = array("Username" => $username, "Display name" => $fullname, "Email address" => $email, 'Permissions' => kpPermissionLevels()[$priv], "onclick" => "cm_loadPage('user&username=$username');");
                        $usersList->addObject($userArray);
                    }
                    
                    $usersList->title("Current Users");
                    $usersList->display($this->name);
                    /*
                    $first = $this->name . "&username=" . "test";
                    echo "<button onclick=\"cm_loadPage('$first');\">test</button>";
                    $action = $this->name;
                    $form = new ajaxForm("users",$action,"POST");
                    $form->input("username", "text", "");
                    $form->input("password","password","");
                    $form->submit();
                    $form->end();
                    */
                    ce::end();
                }
	}
	
    function updatePage(){
           global $connection;
           
           if(isset($_GET['reset'])){
               
               $username = filter_input(INPUT_GET,"reset");
               
               if($username != get_username()){
                   block(1);
               }
               
               $password = filter_input(INPUT_POST,"password");
               
               if(strlen($password) < 8){
                   echo "Please choose a longer password";
                   return;
               }
               if($password == "password"){
                   echo "Please choose a different password";
                   return;
               }
               echo new_password($username, $password);
               return;
           }
           if(isset($_GET['delete'])){
               
               //Permissions - SUPER IMPORTANT
               if(!block(1)){ return; }
               
               $delete = filter_input(INPUT_GET,"delete");
               if($delete == get_username()){
                   echo "You cannot delete your own account!";
                   return;
               }
               $stmt = $connection->prepare("DELETE FROM users WHERE username=?");
               $stmt->bind_param("s",$delete);
               if($stmt->execute()){
                   echo "reload";
                   return;
               }
               echo "Could not delete user: $stmt->error";
               return;
           }
           if(isset($_GET['edit'])){
               $username = filter_input(INPUT_GET,"edit");
               $priv = filter_input(INPUT_POST,"privilege");
               $fullname = filter_input(INPUT_POST,"fullname");
               $email = filter_input(INPUT_POST,"email");
               $dpurl = filter_input(INPUT_POST,"dpurl");
               $bio = content("bio");
               
               if($username != get_username()){
                   block(1);
                   //Only administrators can edit other people's profiles
               }else{
                   $priv = profile(get_username())['privilege'];
                   //Other users cannot change their own privilege setting - set it to the existing value
               }
              
               
               $ustmt = $connection->prepare("UPDATE users SET fullname=?, email=?, dpurl=?, privilege=?, bio=? WHERE username=?");
               $ustmt->bind_param("ssssss",$fullname,$email,$dpurl,$priv,$bio,$username);
               
               if($ustmt->execute()){
                   echo "Updated profile";
                   return;
               }
               echo "Error updating profile";
               $ustmt->close();
               return;
           }
           else{
               block(1);
               //Only administrators can add new users
               //New user
               $username = filter_input(INPUT_POST,"username");
               if(!username_available($username)){
                    echo "Username $username is taken";
                    return;
               }
               

               $fullname = filter_input(INPUT_POST,"fullname");
               $email = filter_input(INPUT_POST,"email");
               $password = filter_input(INPUT_POST,"password");
               $priv = filter_input(INPUT_POST,"privilege");
               
               if(strlen($username) < 2 || strlen($email) < 2){
                   echo "Please enter a username and email adress";
                   return;
               }
               
               if(new_user($username, $fullname, $priv, $email, $password)){
                   echo "reload";
                   return;
               }
               echo "Failed to add user";
           
            //echo "reload";
           }
            
	}
        
}

$userSettings = new userSettings();
$standardPages[] = $userSettings;

class profile extends optionsPage{
    //put your code here
    public $name = "profile";
    public $title = "Profile";
    public function configPage(){
        $profile = profile(get_username());
        ce::begin();
        
        //Profile form
        $form = new ajaxForm("profileForm", "user&edit=" . $profile['username'], "POST");
        $form->formTitle("Edit profile");
        $form->lockedInput(get_username(), "Username");
        $form->labeledInput("fullname", "text", $profile['fullname'], "Full name");
        $form->labeledInput("email","email", $profile['email'], "Email address");
        $form->labeledInput("dpurl", "text", $profile['dpurl'], "Profile picture");
        $form->largeText("bio", $profile['bio'], "Bio");
        $form->submit("Update profile");
        
        $pwordForm = new ajaxForm("passwordForm", "user&reset=" . $profile['username'], "POST");
        $pwordForm->labeledInput("password", "password", "", "New password");
        $pwordForm->submit("Change password");
                    
        ce::end();
    }
}

$profile = new profile();
$standardPages[] = $profile;

class login extends optionsPage{
    public $name = "login";
    //No title - hidden page
    
    function configPage(){
        ce::begin();
        
        
        if(isset($_GET['register'])){
            $regForm = new ajaxForm("login", $this->name. "&register", "POST");
            $regForm->formTitle("Register");
            $regForm->labeledInput("firstname", "text", "", "First name");
            $regForm->labeledInput("lastname", "text", "", "Last name");
            $regForm->labeledInput("email", "email", "", "Email address");
            $regForm->labeledInput("code", "text", "", "Registration code");
            $regForm->linkButton("login", "Sign in instead", $this->name);
            $regForm->submit("Register");
        }else{
            $loginForm = new ajaxForm("login", $this->name, "POST");
            $loginForm->formTitle("Sign in");
            $loginForm->labeledInput("username", "text", "", "Username");
            $loginForm->labeledInput("password","password","","Password");
            $loginForm->linkButton("register", "Register for an account", $this->name . "&register");
            $loginForm->submit("Sign in");
            ce::end();
        }
    }
    function updatePage(){
        global $connection;
        if(isset($_GET['register'])){
            //TODO Register
            echo "Registration is currently closed";
            return;   
        }
        else if(isset($_GET['forgot'])){
            //Forgotten password
            $forgot = filter_input(INPUT_GET,"forgot");
            $to = profile($forgot)['email'];
            if(email($to,"Password reset","Reset link:")){
                return "Password reset email sent";
            }
            return;
        }else{
            //Log in
            $username = filter_input(INPUT_POST,"username");
            $password = filter_input(INPUT_POST,"password");
            /* @var $stmt type -> salt fetch query */
            $stmt = $connection->prepare("SELECT salt,privilege FROM users WHERE username=?;");
            $stmt->bind_param('s',$username);
            $stmt->execute();
            $stmt->bind_result($salt,$privilege);
            if(!$stmt->fetch()){
                echo "Username not recognised";
                return;
            }
            if($privilege >= 5){
                echo("You do not have permission to log in");
                return;
            }
            $stmt->close();
            $hash = get_password_hash($salt, $password);
            $pstmt = $connection->prepare("SELECT username FROM users WHERE username=? AND password = ?");
            $pstmt->bind_param("ss",$username,$hash);
            $pstmt->execute();
            $pstmt->bind_result($safe_username);
            if($pstmt->fetch()){
                $_SESSION['username'] = $safe_username;
                echo "refresh";
                return;
            }
            echo "Incorrect password";
            
            //Hash method
            
        }
        
    }
}

$login = new login();
$standardPages[] = $login;

class logout extends optionsPage{
    public $name = "logout";
    //No title - hidden function
    
    public function configPage(){
        //Overwrite session var
        $_SESSION = array();
        //Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        //Destroy session
        session_destroy();
        echo "<script id=\"loadScript\">location.reload()</script>",PHP_EOL;
    }
}

$logout = new logout();
$standardPages[] = $logout;

class setup extends optionsPage{
    
    public $name = "setup";
    
    function configPage(){
        ce::begin("ce-medium");
        $setupForm = new ajaxForm("setupForm","setup","POST");
        $setupForm->infoRow("Click Setup to run the database setup process");
        $setupForm->infoRow("Default login details:");
        $setupForm->infoRow("Username: Administrator");
        $setupForm->infoRow("Password: password");
        $setupForm->submit("Setup");

        ce::end();
    }
    
    function updatePage(){
        
        /*
         * Check if users table already exists
         * Do not run if so, this would allow any user to rest the admin account
         */
        
        global $connection;
        
        $result = $connection->query("SHOW TABLES LIKE 'users'");
        if($result->num_rows == 0){
            /* Safe to run */
        
    $setupQuery1 = <<<END
CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `fullname` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `bio` longtext COLLATE latin1_general_ci,
  `password` char(64) COLLATE latin1_general_ci NOT NULL,
  `privilege` int(11) NOT NULL,
  `salt` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `dpurl` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `reg_date` date NOT NULL,
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Password in SHA-256';
END;
    
    $setupQuery2 = <<<END
INSERT INTO `users` (`username`, `fullname`, `bio`, `password`, `privilege`, `salt`, `dpurl`, `email`, `reg_date`) VALUES
('Administrator', 'Administrator', '<p>This is the default administrator account. Please change the password immediately.</p>\n', '30ae0449fde97c290f92d5425629488aec6a907bd7e2e637423b8df40a80e4e6', 1, 'e7b98d9ff0f72cdf287981c8d457df0e4fd52a34', '', '', '2015-09-16');
     
END;
            $success1 = $connection->query($setupQuery1);
            $success2 = $connection->query($setupQuery2);
            if($success1 && $success2){
                echo "reload";
                return;
            }
            echo "Failed to install - please check your database settings";
        }
        else{
            echo "Setup has already been performed or database is dirty";
        }
    }
    
    static function isSetup(){
        global $connection;
        $result = $connection->query("SHOW TABLES LIKE 'users'");
        if($result->num_rows == 0){
            return false;
        }else{
            return true;
        }
    }
    
}

$standardPages[] = new setup();
?>