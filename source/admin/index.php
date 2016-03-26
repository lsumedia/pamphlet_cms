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

require 'connect.php';
require 'functions/data_wrangler.php';
require 'functions/elements.php';

html::start();

html::css('css/materialize.min.css');
html::css('css/style.css');		//Import stylesheet
html::css("https://fonts.googleapis.com/icon?family=Material+Icons");
echo "<script>var CKEDITOR_BASEPATH = 'ckeditor/';</script>";
html::lockZoom();
$title = 'Pamphlet 3.4';
html::title($title);
html::jquery();
html::js('js/materialize.js');
html::js("ckeditor/ckeditor.js");
html::js('functions/scripts.js.php');
html::endHead();		//End head tag, start body tag

//Body section
//Declare objects
$leftbar = new cm_leftbar();
$pages = new standardOptionsPages();

$pages->configure();
//$leftbar->prefixHtml($title);		

if(isset($_SESSION['username'])){
    	//Load page objects
    $leftbar->elements = $pages->returnNavList();	//Import pagelist to array
   		//Set leftbar prefix
    $leftbar->addLink("logout", "Sign out");
}
?>

<nav>
    
</nav>
<div class="row">
    <div id="" class="col s12 m4 l3">
        <ul>
            <?php $leftbar->printBar(); ?>
        </ul>
    </div>
    
    <div id="central" class="col s12 m8 l9">
    
        <?php
        /* Page content generator section */
        if(isset($_SESSION['username'])){
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
        }
        else if(!setup::isSetup()){
            $page = $pages->matchObject("setup");
            $page->configPage();
        }else{
            $page = $pages->matchObject("login");
            $page->configPage();
        }

        ?>
    </div>
</div>

<?php
html::end();
?>
