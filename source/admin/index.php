<?php
//pm-manage page


/* Page structure:
	index.php requests pages from response.php returning HTML with AJAX
	Individual sections can request more sub-pages but must contain a save button which sends data to update.php if pressed	

*/
if(0){	//Error reporting - disable for production
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
}

$session_expiration = time() + 3600 * 24 * 2; // +2 days
session_set_cookie_params($session_expiration);
session_start();

require 'functions/elements.php';

$html = new htmlStuff();
$html->htmlStart();			//Start HTML doc
$html->htmlJquery();//Import JQuery file
html::css('style.css');		//Import stylesheet
html::css("https://fonts.googleapis.com/icon?family=Material+Icons");
echo "<script>var CKEDITOR_BASEPATH = 'ckeditor/';</script>";
html::js("ckeditor/ckeditor.js");
html::lockZoom();
ajaxForm::opSecScript();
$html->htmlEndHead();		//End head tag, start body tag

//Body section
//Declare objects
$topbar = new cm_topbar();
$leftbar = new cm_leftbar();
$inner = new cm_inner();
$pages = new standardOptionsPages();

$pages->configure();
$leftbar->prefixHtml("Pamphlet 3");		
if(isset($_SESSION['username'])){
    	//Load page objects
    $leftbar->elements = $pages->returnNavList();	//Import pagelist to array
   		//Set leftbar prefix
    $leftbar->addLink("logout", "Sign out");
    $leftbar->printBar();							//Print leftbar
    $inner->printInner();							//Print inner AJAX section
    $inner->prepAjax();                                                     //Send AJAX code
    $leftbar->defaultPage("general");
}else{
    //$leftbar->addLink("login","Log in");
    $leftbar->printBar();
    $inner->printInner();
    $inner->prepAjax();
    $leftbar->defaultPage("login");
}
//End html doc
$html->htmlEnd();

?>