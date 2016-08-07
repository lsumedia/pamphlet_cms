<?php
//pm-manage page


/* Page structure:
	index.php requests pages from response.php returning HTML with AJAX
	Individual sections can request more sub-pages but must contain a save button which sends data to update.php if pressed	

*/
if(0 || isset($_GET['debug'])){	//Error reporting - disable for production
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
}else{
    ini_set('display_errors', '0');     # don't show any errors...
    error_reporting(E_ALL | E_STRICT);  # ...but do log them
}

$session_expiration = time() + 3600 * 24 * 2; // +2 days
session_set_cookie_params($session_expiration);
session_start();


require 'init.php';

$auth = new authenticator();

if(strlen($config['access_perm']) > 0){
    if($auth->server_check_permission($config['access_perm']) == false){
        echo "<p>You do not have permission to access this page</p>";
        die();
    }
}

html::start();

//html::css('css/materialize.min.css');
html::css('css/style.css');		//Import stylesheet
html::css("https://fonts.googleapis.com/icon?family=Material+Icons");
echo "<script>var CKEDITOR_BASEPATH = 'ckeditor/';</script>";
html::lockZoom();
$title = $config['siteTitle'];
html::title($title);
html::jquery();
//html::js('js/materialize.js');
html::js("ckeditor/ckeditor.js");

html::endHead();		//End head tag, start body tag

//Print login info thingy
$auth->status_bug();

//Body section
//Declare objects
$topbar = new cm_topbar();
$leftbar = new cm_leftbar();
$inner = new cm_inner();
$pages = new standardOptionsPages();

$pages->configure();
$leftbar->prefixHtml($title);	

    //Load page objects
$leftbar->elements = $pages->returnNavList();	//Import pagelist to array
            //Set leftbar prefix
$leftbar->addLink('auth_logout', 'Log out');
$leftbar->printBar();                               //Print leftbar
//$inner->printInner();				//Print inner AJAX section                           
//$defaultPage = 'general';

html::js('scripts.js.php');
echo '<div id="central">';

//Logged in!
if(isset($_GET['action'])){
    $action = $_GET['action'];
}else{
    $action = 'general';
}
if($page = $pages->matchObject($action)){
    $page->configPage();
}else{
    echo "Invalid action request";
}

echo '</div>';
//More javascript and end document

//$leftbar->defaultPage($defaultPage);
html::end();

?>