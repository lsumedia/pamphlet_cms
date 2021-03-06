<?php

/* 
 * Authenticator Plugin
 * 
 * This class is standalone and can be placed anywhere it is needed 
 * - just make sure to adjust the configuration!
 * 
 */

class authenticator{
    
    public $key;
    
    public $user;
    
    public static $config = [
        /* Authentication server root folder with trailing slash */
        'server_root' => 'http://grovestreet.me/projects/user_manager/source/',
        
        /* Prefix for session variables (Should be different for multiple sites 
         * on the same server & domain */
        'session_prefix' => 'lsutv',
        
        /* (Optional) If set, users will be redirected to this address after login
        instead of the original requested page. Should be null if not used. */
        'custom_redirect' => null
    ];
    
    /**
     * Always use this to pull config data - adds in necessary URLs
     * 
     * @return string
     */
    private static function config(){
        $config = self::$config;
        $config['server_address'] = $config['server_root'] . 'auth.php';
        $config['login_page_address'] = $config['server_root'] . 'auth/?p=login';
        $config['logout_page_address'] = $config['server_root'] . 'auth/?p=logout';
        return $config;
    }
    
    /** Create authenticator object
     */
    public function __construct() {
        session_start();
        $this->check_login();
        $this->server_get_profile();
    }
    
    public function check_login(){
        
        $config = self::config();
        
        $sess_prefix = $config['session_prefix'];
        
        
        //Set or overwrite session stored key
        if($key = $this->get_url_key()){ 
            //GET key overwrites session key
            $this->set_session_key($key);
            
        }else if($key = $this->get_post_key()){
            //POST key overwrites session key
            $this->set_session_key($key);
            
        }
        
        if($this->get_session_key() == false){
            //Redirect to login page if no valid key set
            $this->redirect_to_login();
            //Ensure function is broken, even though script should die
            return false;
        }
        
        /* If session key invalid, not registered or timed out */
        if($this->server_check_key() == false ){
            //Clear invalid key from session
            $this->clear_session_key();
            //Redirect to login page
            $this->redirect_to_login();
            return false;
        }
        
    }
   
    /* Server request methods */
    
    public function server_check_key(){
        
        $srv_addr = self::config()['server_address'];
        
        $user_ip = $_SERVER['REMOTE_ADDR'];
        
        $api_url = $srv_addr . "?check_key&key=" . $this->key . "&ip=" . $user_ip . '&source_url=' . current_page_url() ;
        
        $data = json_decode(file_get_contents($api_url),true);
        
        if($data['valid'] == true && $data['key'] == $this->key){
            return true;
        }else{
            var_dump($data);
            return false;
        }
        
    }
    
    /**
     * server_check_permission - liase with the server to see if the 
     * user has a given permission
     * 
     * @param string $permission - permission name to check for 
     */
    public function server_check_permission($permission){
       
        $config = self::config();
        $srv_addr = $config['server_address'];
        
        $api_url = $srv_addr . '?check_perm&key=' . $this->key . '&perm_name=' . $permission;
        
        $data = json_decode(file_get_contents($api_url),true);
        
        if(isset($data['has_permission'])){
            if($data['perm_name'] == $permission && $data['has_permission'] == true){
                return true;
            }
        }
        
        return false;
        
    }
    
    /**
     * Get user profile from server
     * 
     * 
     * @return boolean
     */
    public function server_get_profile(){
        
        $config = self::config();
        $srv_addr = $config['server_address'];
        
        $api_url = $srv_addr . '?user_profile&key=' . $this->key;
        
        $data = json_decode(file_get_contents($api_url),true);
        
        if(isset($data['username'])){ 
            $this->user = $data;
            return $data;
        }
        
        return false;
    }
    
    
    /**
     * Set access key to session variable
     * 
     * @param type $key
     */
    public function set_session_key($key){
        $config = self::config();
        $sess_prefix = $config['session_prefix'];
        $_SESSION[$sess_prefix . '_key'] = $key;
    }
    
    /**
     * Get session key from session variable 
     * 
     * @return boolean
     */
    public function get_session_key(){
        $config = self::config();
        $sess_prefix = $config['session_prefix'];
        if(isset($_SESSION[$sess_prefix . '_key'])){
            $key = $_SESSION[$sess_prefix . '_key'];
            $this->key = $key;
            return $key;
        }
        return false;
    }
    
    public function clear_session_key(){
        $config = self::config();
        $sess_prefix = $config['session_prefix'];
        $_SESSION[$sess_prefix . '_key'] = null;
        unset($_SESSION[$sess_prefix . '_key']);
    }
    
    /* Get session key stored in GET variable */
    public function get_url_key(){
         if(isset($_GET['key'])){
            return $_GET['key'];
        }
        return false;
    }
    
    /* Get session key stored in POST variable */
    public function get_post_key(){
        if(isset($_POST['key'])){
            return $_POST['key'];
        }
        return false;
    }
    
    public function redirect_url(){
        if(isset(self::$config['custom_redirect'])){
            return self::$config['custom_redirect'];
        }else{
            return current_page_url();
        }
    }
    
    public function redirect_to_login(){
        $config = self::config();
        
        /* Send current page URL in address to ensure the user can get back here */
        $current = rawurlencode($this->redirect_url());
        
        header('location:' . $config['login_page_address'] . '&redirect=' . $current);
        die();
    }
    
    
    public function logout_url(){
        return self::config()['logout_page_address'] . '&key=' . $this->key . '&source=' . urlencode($this->redirect_url());
    }
    
    public function profile(){
        $user = $this->user;
        $user['logout_url'] = $this->logout_url();
        $user['edit_profile_url'] = self::config()['server_root'] . 'auth/?p=profile&key=' . $this->key;
        return $user;
    }
    
    
    /* Append key to URL and return the key 
     * (for linking to external sites in the same SSO network) */
    public function append_key($url){
        $u_v = (strpos($url, '?') === false)? '?' : '&';
        
        return $url . $u_v . 'key=' . $this->key;
    }
    
    /**
     * status_bug
     * 
     * Displays a small notice on the page which displays current login information
     * and a sign out button
     */
    public function status_bug(){
        $config = self::config();
        $sess_prefix = $config['session_prefix'];
        
        $page_prefix = $sess_prefix . '_profile';
        $logout_icon_url = $config['server_root'] . 'res/logout.svg';
        $profile_page_url = $config['server_root'] . 'auth/?p=profile&key=' . $this->key;
        ?>
<style>
    .authenticator_bug_main{
        background-color:black;
        color:white;
        width:300px;
        height:65px;
        overflow:hidden;
        position:fixed;
        bottom:50px;
        right:50px;
        box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        font-family: Arial, sans-serif;
    }
    .authenticator_bug_main p{
        margin:0;
        padding:0;
    }
    .authenticator_bug_main img{
        height:55px;
    }
    .authenticator_bug_left, .authenticator_bug_middle, .authenticator_bug_right{
        height:65px;
        padding-top:5px;
        padding-bottom:5px;
        box-sizing:content-box;
    }
    .authenticator_bug_left{
        width:65px;
        float:left;
        padding-left:5px;
        overflow:hidden;
    }
    .authenticator_bug_middle{
        width:170px;
        float:left;
        padding-top:10px;
        cursor:pointer;
    }
    .authenticator_bug_right{
        width:60px;
        float:left;
    }
    .authenticator_bug_dp_container{
        height:55px;
        width:55px;
        overflow:hidden;
        background-color:white;
    }
    .authenticator_bug_fullname{
        font-weight:500;
        font-size:18px;
        line-height:22px;
    }
    .authenticator_bug_username{
        color:#aaa;
    }
</style>
<div class="authenticator_bug_main" id="<?= $sess_prefix ?>_bug">
    <div class="authenticator_bug_left">
        <div class="authenticator_bug_dp_container">
            <img src="<?= $this->user['dp_url']; ?>" />
        </div>
    </div>
    <div class="authenticator_bug_middle" onclick="window.open('<?= $profile_page_url ?>', '<?= $page_prefix ?>', 'menubar=0,status=0,toolbar=0,width=940px,height=700px');" title="Edit profile">
        <p class="authenticator_bug_fullname"><?= $this->user['fullname'] ?></p>
        <p class="authenticator_bug_username"><?= $this->user['username'] ?></p>
    </div>
    <div class="authenticator_bug_right">
        <a href="<?= $this->logout_url() ?>"><img src="<?= $logout_icon_url ?>" alt="Log out" title="Log out" /></a>
    </div>
</div>        
<?php
        
    }
    
    /* Non-authentication SSO data functions */
    
    /* Get public data for SSO users */
    public static function server_get_users(){
        $config = self::config();
        
        $srv_addr = $config['server_address'];
        
        $api_url = $srv_addr . '?public_user_data';
        
        return json_decode(file_get_contents($api_url),true);
    }
    
    /* Get all SSO groups */
    public static function server_get_groups(){
        $config = self::config();
        
        $srv_addr = $config['server_address'];
        
        $api_url = $srv_addr . '?public_group_data';
        
        return json_decode(file_get_contents($api_url),true);
    }
    
}

/* This bit from StackOverflow, gotta be honest */
function strleft($s1, $s2) { return substr($s1, 0, strpos($s1, $s2)); }

 function current_page_url() 
{ 
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
} 