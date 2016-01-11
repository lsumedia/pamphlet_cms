<?php
//Ensure data_wrangler.php is added before this
//Generic htmldoc setup (required in index.php ONLY)

$cke_path = "/live/admin/ckeditor/ckeditor.js";

/**
 * Represents an element type loaded asynchronously that requires
 * preloaded client-side functions to run. If the clientSide function is set
 * the 
 */
class uiElement{
    
    /**
     * Set name for debugging purposes
     * @var string
     */
    public $name;
    /**
     * Echo client side code in here
     * @return boolean
     */
    public function clientSide(){
        return false;
    }
    
    public static function getDeclaredUiElements(){
        $elementTypes = array();
        foreach(get_declared_classes() as $class){
            if(is_subclass_of($class, 'uiElement')){
                $elementTypes[] = new $class();
            }
        }
        return $elementTypes;
    }
    
    public static function loadUiElements(){
        $elements = self::getDeclaredUiElements();
        echo "<script>", PHP_EOL;
        $numElements = count($elements);
        echo "/* $numElements elements to load */";
        foreach($elements as $element){
            echo PHP_EOL,"/* Code for $element->name */", PHP_EOL;
            $element->clientSide();
        }
        echo PHP_EOL, "</script>", PHP_EOL;
    }
    
}

class htmlStuff{
	function htmlStart(){	//Declares headers, opens <head>
		echo "<!doctype html>". PHP_EOL. "<html>" . PHP_EOL . "<head>". PHP_EOL . "<meta charset=\"UTF-8\">", PHP_EOL;
	}
	function htmlJquery(){
		echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>',PHP_EOL;
		return;
	}
	function htmlJqueryUi(){	//Note - this will slow down page loading, only use when needed
		echo '<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>',PHP_EOL;
		echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">',PHP_EOL;
		return;
	}
	function htmlCustomJS($url){	//Embed a custom JS file
		echo '<script src="'.$url.'"></script>',PHP_EOL;
		return;
	}
	function htmlStyleSheet($url){	//Embed custom stylesheet
		echo '<link rel="stylesheet" href="'.$url.'">',PHP_EOL;
		return;
	}
	function htmlEndHead(){		//End <head>, start <body>
		echo '</head>',PHP_EOL;
		echo '<body>',PHP_EOL;
		return;
	}
	function htmlEnd(){	 //End HTML document
		echo '</body>',PHP_EOL;
		echo '</html>',PHP_EOL;
		return;
	}
        function title($title){
            echo "<title>$title</title>", PHP_EOL;
        }
        
        static function emptyBody(){
            echo "<html><body style=\"margin:0\">";
        }
        static function endEmptyBody(){
            echo "</body></html>";
        }
}

class html{
    static function start(){
        echo "<!doctype html>
<html>
<head>
<meta charset=\"UTF-8\">";
    }
    static function endHead(){
        echo '</head>',PHP_EOL;
        echo '<body>',PHP_EOL;
        return;
    }
    static function end(){
        echo '</body>',PHP_EOL;
        echo '</html>',PHP_EOL;
        return;
    }
    static function css($url){
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$url\">",PHP_EOL;
        return;
    }
    static function js($url){
        echo "<script src=\"$url\"></script>",PHP_EOL;
        return;
    }
    static function meta($code){
        echo "<meta $code >", PHP_EOL;
    }
    static function title($text){
        echo "<title>$text</title>", PHP_EOL;
    }
    static function div($class,$id){
        echo "<div id=\"$id\" class=\"$class\">", PHP_EOL;
    }
    static function closeDiv(){
        echo "</div>", PHP_EOL;
    }
    static function comment($text){
        echo "<!-- $text -->", PHP_EOL;
    }
    static function lockZoom(){
        echo '<meta name="viewport" content="width=device-width, user-scalable=no" />';
    }
}

class div{
    public $id;
    public $class;
    public $otherTags;
    
    //Whether there is an inner section div
    public $innerDiv = false;
    
    public function __construct(){
        $a = func_get_args(); 
        $i = func_num_args(); 
        if (method_exists($this,$f='__construct'.$i)) { 
            call_user_func_array(array($this,$f),$a); 
        }else{ 
        echo "<div>", \PHP_EOL;
        }
 
    }
    
    public function __construct1($id){
        $this->id = $id;
        echo "<div id=\"$this->id\">",  \PHP_EOL;
    }
    public function __construct2($id,$class){
        $this->id = $id;
        $this->class = $class;
        echo "<div id=\"$this->id\" class=\"$this->class\">", PHP_EOL;
    }
    public function __construct3($id,$class,$tag){
        $this->id = $id;
        $this->class = $class;
        $this->otherTags = $tag;
        echo "<div id=\"$this->id\" class=\"$this->class\" $this->otherTags>", PHP_EOL;
    }
    
    public function innerDIV($id,$class){
        $this->innerDiv = true;
        echo "<div id=\"$id\" class=\"$class\">";
    }
    
    public function close(){
        if($this->innerDiv) echo "</div>", PHP_EOL;
        echo "</div>", PHP_EOL;
    }
}

//Content section inner elements

function backButton($action){
    echo "<div class=\"backbutton\" onclick=\"cm_loadPage('$action')\"><img src=\"images/back.png\">Back</div>";
}

function centralElement($html,$style){
	echo "<div class=\"centralelement $style\" >$html</div>";
}

class centralElement{
    function __construct($style){
        echo '<div class="centralelement '.$style.'">';
    }
    function end(){
        echo '</div>';
    }
    
}
class ce{
    static function begin($style){
         echo "<div class=\"centralelement\" $style >";
    }
    static function end(){
        echo "</div>";
    }
}

class objectList{
    
    //Array of arrays containing key-value pairs with friendly names for both
    //Each entry must have same keys!
    public $objects = array();
    //Array of strings containing 
    public $headers = array();
    //DOM ID the table will have
    public $id;
    public $tags;
    public $title;
    
    public function __construct($objects, $id){
        $this->objects = $objects;
        $this->id = $id;
    }
    
    public function addObject($object){
        $this->objects[] = $object;
    }
    
    public function style($tags){
        if($tags){ $this->tags = $tags; }
    }
    
    public function title($text){
        //echo "<div class=\"listtitle\">$text</div>", PHP_EOL;
        $this->title = $text;
    }
    public function display($pageName){
        echo "<!-- objectList -->", PHP_EOL;
        echo "<div class='form'>", PHP_EOL;
        if($this->title){ echo "<div class=\"listtitle\">$this->title</div>", PHP_EOL; }
        echo "<table class=\"objectList\" id=\"$this->id\" $this->tags >",PHP_EOL;
        
        $first = $this->objects[0];
        foreach($first as $key => $value){
            if($key != "onclick"){
                $this->headers[] = $key;
            }
        }
        echo "<thead><tr>", \PHP_EOL;
        foreach($this->headers as $header){
            echo "<th>". $header . "</th>", \PHP_EOL;
        }
        echo "</tr></thead><tbody>", \PHP_EOL;
        
        foreach($this->objects as $object){
            if($onclick = $object['onclick']){
                echo "<tr onclick=\"$onclick\">";
            }else{
                echo "<tr>", \PHP_EOL;
            }
            foreach($object as $key => $value){
                if($key != "onclick"){
                    echo "<td>".$value."</td>", \PHP_EOL;
                }
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>", PHP_EOL;
    }
}

class multiPageList extends objectList{
    public function display($pageName){
        echo "<!-- multiPageList -->", PHP_EOL;
        echo "<div class='form'>", PHP_EOL;
        $offset = filter_input(INPUT_GET,"offset");
        if(!$offset){ $offset = 0; }
        $count = count($this->objects);
        echo "<div class=\"listtitle\">$this->title</div>", PHP_EOL;
        if($count > 10){
            $ten = $offset + 10;
            $back; $next;
            $page = floor(($offset-1) / 10) + 1;
            $numpages = floor(($count-1) / 10 ) + 1;
            if($offset >= 10){ $minus = $offset - 10; $back = "<img onclick=\"cm_loadPage('$pageName&offset=$minus');\" src=\"images/back_black.png\" />"; }
            if($count > $ten){ $next = "<img onclick=\"cm_loadPage('$pageName&offset=$ten');\" src=\"images/next_black.png\" />"; }
            echo "<div class=\"listnav\"><p>Page $page of $numpages</p>$back$next</div>";
        }
        echo "<table class=\"objectList\" id=\"$this->id\" $this->tags >",PHP_EOL;
        
        $first = $this->objects[0];
        foreach($first as $key => $value){
            if($key != "onclick"){
                $this->headers[] = $key;
            }
        }
        echo "<thead><tr>", \PHP_EOL;
        foreach($this->headers as $header){
            echo "<th>". $header . "</th>", \PHP_EOL;
        }
        echo "</tr></thead><tbody>", \PHP_EOL;
        
        foreach($this->objects as $index=>$object){
            if($index >= $offset && $index < $offset + 10){
                if($onclick = $object['onclick']){
                    echo "<tr onclick=\"$onclick\">";
                }else{
                    echo "<tr>", \PHP_EOL;
                }
                foreach($object as $key => $value){
                    if($key != "onclick"){
                        echo "<td>".$value."</td>", \PHP_EOL;
                    }
                }
                echo "</tr>";
            }
        }
        echo "</tbody></table>";
        echo "</div>", PHP_EOL;
    }
}

class ajaxList extends uiElement{
    
    public $name = 'ajaxList';
    
    public function clientSide() {
        echo <<<END
var convert = function(convert){
    return $("<span />", { html: convert }).text();
};

        
function list_change_page(list,values,pagenumber){
}
        
function list_search(list,values,term){
}
        
function list_all(listBodyId, data_location){
        var string = document.getElementById(data_location).innerHTML;
        //var string = $('#' + data_location).value.html();
        //console.log(string);
        var data = JSON.parse(string);
        var first = data[0];
        var headers = [];
        for(var header in first){
            if(header != 'onclick'){
                headers.push(header);
                console.log(header);
            }
        }
        
        //console.log(data);
        var html = "";
        
        
        for(var i=0; i < data.length; i++){
            var row = data[i];
            if(row){
                html += '<tr onclick="' + row['onclick'] + '">';
                for(var key in row){
                    if(key != 'onclick'){
                        html += "<td>" + convert(row[key]) + "</td>";
                    }
                }
                html += "</tr>";
            }   
        }
        
        document.getElementById(listBodyId).innerHTML = html;
}
        
END;
    }
    
    //Array of arrays containing key-value pairs with friendly names for both
    //Each entry must have same keys!
    public $objects = array();
    //Array of strings containing 
    public $headers = array();
    //DOM ID the table will have
    public $id;
    public $tags;
    public $title;
    
    public function __construct($objects, $id){
        $this->objects = $objects;
        $this->id = $id;
    }
    
    public function addObject($object){
        $this->objects[] = $object;
    }
    
    public function style($tags){
        if($tags){ $this->tags = $tags; }
    }
    
    public function title($text){
        //echo "<div class=\"listtitle\">$text</div>", PHP_EOL;
        $this->title = $text;
    }
    
    public function display(){
        echo "<!-- ajaxList $this->id -->", PHP_EOL;
        echo "<div class='form'>", PHP_EOL;
        $data_id = $this->id . '_data';
        $body_id = $this->id . '_body';
        self::arrayToCrapJson($data_id, $this->objects);
        
        $count = count($this->objects);
        echo "<div class=\"listtitle\">$this->title</div>", PHP_EOL;
        if($count > 10){
            $ten = $offset + 10;
            $back; $next;
            $page = floor(($offset-1) / 10) + 1;
            $numpages = floor(($count-1) / 10 ) + 1;
            $back = "<img onclick=\"list_all('$body_id','$data_id');\" display='none' src=\"images/back_black.png\" />";
            if($count > $ten){ $next = "<img onclick=\"cm_loadPage('$pageName&offset=$ten');\" src=\"images/next_black.png\" />"; }
            echo "<div class=\"listnav\"><p>Page 1 of $numpages</p>$back$next</div>";
        }
        echo "<table class=\"objectList\" id=\"$this->id\" $this->tags >",PHP_EOL;
        
        $first = $this->objects[0];
        foreach($first as $key => $value){
            if($key != "onclick"){
                $this->headers[] = $key;
            }
        }
        echo "<thead><tr>", \PHP_EOL;
        foreach($this->headers as $header){
            echo "<th>". $header . "</th>", \PHP_EOL;
        }
        echo "</tr></thead><tbody id='$body_id'>", \PHP_EOL;
        
        foreach($this->objects as $index=>$object){
            if($index >= $offset && $index < $offset + 10){
                if($onclick = $object['onclick']){
                    echo "<tr onclick=\"$onclick\">";
                }else{
                    echo "<tr>", \PHP_EOL;
                }
                foreach($object as $key => $value){
                    if($key != "onclick"){
                        echo "<td>".$value."</td>", \PHP_EOL;
                    }
                }
                echo "</tr>";
            }
        }
        echo "</tbody></table>";
        echo "</div>";
    }
    
    public static function arrayToCrapJson($id, $objects){
        echo "<div style='display:none;' id='$id'>" . htmlspecialchars(json_encode($objects)) . "</div>", PHP_EOL;
    }
    
}

/**
 * Class for making forms
 * Deprecated, use customFom instead
 */
class ajaxForm{
    
    public $id;
    public $action;
    public $method;
    public $fieldNames = array();

    public function __construct($id,$action,$method,$onReloadAction){
        //Starts the form duh
        $this->id = $id;
        $this->action = $action;
        $this->method = $method;
        echo "<!-- ajaxForm $this->id starts-->", PHP_EOL;
        echo "<div id=\"$id\" class=\"form\">", \PHP_EOL;
    }
    function formTitle($text){
        //Simple title for the form
        echo "<div class=\"formtitle\">$text</div>", PHP_EOL;
    }
    function input($name,$type,$value){
        //DEPRECATED - input with no label
        echo "<div class=\"fieldRow\">", \PHP_EOL;
        echo "<input type=\"$type\" id=\"$name\" name=\"$name\" value=\"$value\" />", \PHP_EOL;
        echo "</div>", \PHP_EOL;
        $this->fieldNames[] = $name;
    }
    /* function inputWithButton(name,value,label,onlick,blabel
     * name: the input's name for the post request
     * value: the input's existing value
     * label: the field input label
     * onclick: the action performed by the button
     * blabel: the button's label
     */
    function inputWithButton($name,$value,$label,$onclick,$blabel){
        //Input with a label and an JS button. Useful for image uploaders
        echo "<div class=\"fieldRow\" id=\"filePath\"><p>$label</p>";
        echo "<input id=\"$name\" type=\"text\" value=\"$value\">";
        echo "<button onclick=\"$onclick\">$blabel</button>";
        echo "</div>";
        $this->fieldNames[] = $name;
    }
    
    function labeledInput($name,$type,$value,$label){
        //Regular text input
        echo "<div class=\"fieldRow\"><p>$label</p><input type=\"$type\" id=\"$name\" name=\"$name\" value=\"$value\" placeholder=\"$label\"/></div>", \PHP_EOL;
        $this->fieldNames[] = $name;
    }
    function lockedInput($value,$label){
        //Readonly text input
        echo "<div class=\"fieldRow\"><p>$label</p><input onClick=\"this.select();\" readonly value=\"$value\"></div>";
    }
     function number($name,$min,$max,$value,$label){
        echo "<div class=\"fieldRow\"><p>$label</p><input type=\"number\" id=\"$name\" name=\"$name\" value=\"$value\" min=\"$min\" max=\"$max\"/></div>", \PHP_EOL;
        $this->fieldNames[] = $name;
    }
    function lockedInputJs($id,$label){
        echo "<div class=\"fieldRow\"><p>$label</p><input onClick=\"this.select();\" readonly id=\"$id\"></div>";
    }
    function checkBox($name,$value,$label){
        //Simple checkbox
       $checked = ($value != false)? "checked":"";
       echo "<div class=\"fieldRow checkbox\"><p>$label</p><input id=\"$name\" name=\"$name\" type=\"checkbox\" value=\"$value\" $checked onclick=\"$(this).val(this.checked ? 1 : 0)\"><label for=\"$name\"><span></span></div>";
       $this->fieldNames[] = $name;
    }
    function selector($name,$elements,$value,$label){
        //Regular selector (takes array of strings)
        echo "<div class=\"fieldRow\"><p>$label</p>", PHP_EOL;
        echo "<select id=\"$name\" name=\"$name\">", PHP_EOL;
        foreach($elements as $key => $choice){
            if($choice == $value){
                echo "<option value=\"$choice\" selected>$choice</option>", PHP_EOL;
            }else{
                echo "<option value=\"$choice\">$choice</option>", PHP_EOL;
            }
        }
        echo "</select></div>", PHP_EOL;
        $this->fieldNames[] = $name;
    }
    function kpSelector($name,$kpelements,$current,$label){
        //Select field with key-pair elements ("value"=>"label")
        echo "<div class=\"fieldRow\"><p>$label</p>", PHP_EOL;
        echo "<select id=\"$name\" name=\"$name\">", PHP_EOL;
        foreach($kpelements as $key => $choice){
                if($key == $current){
                echo "<option value=\"$key\" selected>$choice</option>", PHP_EOL;
            }else{
                echo "<option value=\"$key\">$choice</option>", PHP_EOL;
            }
        }
        echo "</select></div>", PHP_EOL;
        $this->fieldNames[] = $name;
    }
    function plainText($name,$value,$label){
        //Plain textarea, no formatting options
        echo "<div class=\"fieldRow\"><p>$label</p></div>", \PHP_EOL;
        echo "<textarea class=\"richtext\" id=\"$name\" id=\"$name\" rows=\"5\">";
        echo $value;
        echo "</textarea>";
        $this->fieldNames[] = $name;
    }
    function largeText($name,$value,$label){
        //textarea with CKEditor - one per page as this takes over the loadScript
        //TODO - upgrade loadScript for multiple elements
        echo "<div class=\"fieldRow\"><p>$label</p></div>", \PHP_EOL;
        echo "<textarea class=\"richtext\" id=\"$name\" id=\"$name\" rows=\"5\">";
        echo $value;
        echo "</textarea>";
        $editor = $name . "editor";
        echo <<<END
        <script id="loadScript">
            var $editor = CKEDITOR.replace('$name');
            $editor.on( 'change', function( evt ) {
                $editor.updateElement();
            });
            $editor.on( 'loaded', function(evt){
                    $('.cke').css('border','none');
                    $('.cke').css('box-shadow','none');
                    $('.cke_bottom').css('background-color','#2196F3');
                });
                //CKFinder Setup (TODO: replace with free alternative)
            CKFinder.setupCKEditor($editor, '/cms/ckfinder/');
        </script>
END;
        $this->fieldNames[] = $name;
    }
    function infoRow($content){
        echo "<div class=\"fieldRow\"><p>$content</p></div>",PHP_EOL;
    }
    
    function submit($label,$onReloadAction){
        //Puts all the fields together for AJAX request
        $fields = "[";
        foreach($this->fieldNames as $key => $field){
            if($key != 0){ $fields .= ","; }
            $fields .= "'$field'";
        }
        $fields .= "]";
        $idArray = explode("=", $this->action);
        $lastId = end($idArray);
        echo "<div class=\"fieldRow\">";
        echo "<p class=\"response\" id=\"$this->id-response\"></p>";
        echo "<button title=\"Item id: $lastId\"onclick=\"cm_updateForm($fields,'$this->action','POST','$this->id-response','$onReloadAction');\">$label</button>", \PHP_EOL;
        echo "</div>";
        echo "</div>", PHP_EOL;
        echo "<!-- ajaxForm $this->id ends -->", PHP_EOL;
        
    } 
    function clipboardSubmit($label,$resultLabel,$onReloadAction){
        //Puts all the fields together for AJAX request
        $fields = "[";
        foreach($this->fieldNames as $key => $field){
            if($key != 0){ $fields .= ","; }
            $fields .= "'$field'";
        }
        $fields .= "]";
        $idArray = explode("=", $this->action);
        $lastId = end($idArray);
        echo "<div class=\"fieldRow\">";
        echo "<button title=\"Item id: $lastId\"onclick=\"cm_updateForm($fields,'$this->action','POST','$this->id-response','$onReloadAction');\">$label</button>", \PHP_EOL;
        echo "</div>";
        $this->lockedInputJs("$this->id-response",$resultLabel);
        echo "</div>", PHP_EOL;
        echo "<!-- ajaxForm $this->id ends -->", PHP_EOL;
        
    }
    function otherActionButton($id,$label,$action,$onReloadAction){
        //A button which performs a custom cp_updateForm action
        $newAction = $this->action . $action;
        if(!isset($onReloadAction)){
            $onReloadAction = $action;
        }
        echo "<div class=\"fieldRow\">";
        echo "<button id=\"$id\" onclick=\"if(confirm('$label?')){ cm_updateForm([],'$newAction','GET','$this->id-response','$onReloadAction');};\">$label</button>";
        echo "</div>", PHP_EOL;
    }
    function linkButton($id,$label,$action){
        //A button which requests a cp_loadPage page
        echo "<div class=\"fieldRow\">";
        echo "<button id=\"$id\" onclick=\"cm_loadPage('$action');\">$label</button>";
        echo "</div>", PHP_EOL;
    }
    function jsActionButton($id,$label,$action){
        //A button which performs a custom cp_updateForm action
        echo "<div class=\"fieldRow\">";
        echo "<button id=\"$id\" onclick=\"$action\">$label</button>";
        echo "</div>", PHP_EOL;
    }
    
    static function startOptionalSection($id,$label){
        //Start an optional selection section - only one level of this is allowed (no opsec within an opsec)... opsection
        echo "<div class=\"fieldRow togglebutton\" onclick=\"expand('$id');\"><p>$label</p><img src=\"images/down_black.png\"></div>", \PHP_EOL;
        echo "<div id=\"$id\" class=\"hiddensection\">";
    }
    
    static function endOptionalSection(){
        //End an optional selection section
        echo "</div>", PHP_EOL;
    }
    
    static function opSecScript(){
        echo <<<END
<script>
        function expand(id){
        var thing = document.getElementById(id);
        if(thing.style.maxHeight != "500px"){
            thing.style.maxHeight = "500px";
        }else{
            thing.style.maxHeight = "0px";
        }
   }
</script>
END;
    }
    
    function end(){
        //echo "</div>", \PHP_EOL;
    }
}

//Global simple components

abstract class optionsPage{	//Class to be used for loading options pages
	public $name;	//Ugly title (use prefix "plugin_" for plugins
	public $title;	//
	
	function publicPage(){ return false; }	//Code to be run for public display
	function configPage(){ return false; }	//Code to be run for config mode
	function updatePage(){ return false; }	//Code to be run for updating databases
        function orphanage($olduser,$newuser){ return false;  } //Code to be run when deleting users
}

class standardOptionsPages{
	public $pageArray = array();	//array of optionsPage objects
	
	function importStandardPages(){
		require 'functions/options_pages.php';
		 $this->pageArray = array_merge($this->pageArray,$standardPages);
	}
	/**
         * Import all plugin pages from the plugin folder
         * All files in the plugin folder containing .php will be executed
         */
	function importPluginPages(){
            $pluginPages = array();
            $plugindir = './plugins';
            $plugins = array_diff(scandir($plugindir), array('..', '.','_notes'));
            foreach($plugins as $plugin){
                if(strpos($plugin,".php") != false){
                    include $plugindir . '/' . $plugin;
                }
            }
            //print_r($plugins);
            if(isset($pluginPages)) $this->pageArray = array_merge($this->pageArray,$pluginPages);
	}
	
	function configure(){
		$this->importStandardPages();
		$this->importPluginPages();
	}
	
	function returnNavList(){
		$navList = array();
		foreach($this->pageArray as $page){
                        if($page->title){ 
                            $listItem = (object) array("name"=> $page->name, "title" => $page->title);
                            $navList[] = $listItem;
                        }
		}
		return $navList;
	}
	function returnObjectList(){
		return $this->pageArray();
	}
	function matchObject($name){
		foreach($this->pageArray as $page){
			if($page->name == $name){
				return $page;
			}
		}
		return false;
	}
        /* public function orphanage
         * Takes a username of a user that is being deleted
         * and a user that is not being deleted
         * If no new username is provided, the user's posts are deleted
         */
        function orphanage($old,$new){
            $successes = array();
            foreach($this->pageArray as $page){
                if($page->orphanage($old,$new)){
                    $succesese[] = $page->name;
                }
            }
            return successes;
        }
	
}

//Outer (static) components (access from index.php)

class cm_topbar{
	
	public $elements = array();
	
	function addElement($title,$name){
		$elements[] = (object) array("title" => $title, "name" => $name);
	}
	
	function printBar(){
		foreach($elements as $element){
			
		}
	}
}

class cm_leftbar{
	
	public $elements = array();
	public $prefix;
	
	function addLink($name,$title){ $this->elements[] = (object) array("name" => $name, "title" => $title);}
	
	function addLabel($title){ $this->elements[] = $title; }
	
	function prefixHtml($html){ $this->prefix = $html;}
	
	function printBar(){
		echo '<div id="leftbar">',PHP_EOL;
		if($this->prefix) echo  "<h3>$this->prefix</h3>", PHP_EOL;
		echo '<ul class="leftbar_list">',PHP_EOL;
		foreach($this->elements as $element){
			if(is_object($element)){	//Echo link item
					$title = $element-> title;
					$name = $element-> name;
					echo "<li class=\"leftbar_item link\" onclick=\"cm_loadPage('$name');\" id=\"leftbar_$name\">$title</li>",PHP_EOL;
			}
			else{		//Echo label item
				echo "<li class=\"leftbar_item label\" id=\"leftbar_$element\">$element</li>",PHP_EOL;
			}
		}
		echo '</ul>';
		echo '</div>',PHP_EOL;
	}
        
        function defaultPage($name){
            echo <<<END
    <script id="defaultPage">
    cm_loadPage('$name');            
    </script>
END;
        }
	
}

class cm_inner extends uiElement{
	
        public $name = 'page loader';
    
	//Run this first
	function printInner(){
		echo '<div id="central">',PHP_EOL;
		
		echo '</div>',PHP_EOL;
	}
	//Run this second
	public function clientSide(){
		echo <<<END
function cm_loadPage(code){
	var request = new XMLHttpRequest();
	request.onreadystatechange = function(){
		if(request.readyState == 4 && request.status == 200){
                    //update page
                    document.getElementById('central').innerHTML = request.responseText;
                    if(code.indexOf('&') == -1){
                        $('.leftbar_item').css("background-color","transparent");
                        $('#leftbar_' + code).css("background-color","rgba(0,0,0,0.2)");
                    }
                    eval(document.getElementById('loadScript').innerHTML);
                    $('central').css('opacity','1');
                    $('central').css('pointer-events','auto');
                    //todo - show box
		}
	}
	request.open("GET","request.php?action=" + code,true);
	request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	request.send();
        $('central').css('pointer-events','none');
        $('central').css('opacity','0');
        
            //show loading animation
}
END;
echo PHP_EOL;
		
	}
}


//Inner (dynamic) components (access from request.php)

class customForm extends uiElement{
    
    private $optionsArray = [];
    public $id; /* ID for each individual form */
    public $method; /* Method (GET or POST) */
    private $optionsIDs = [];
    public $title;
    public $name = 'customForm';
    
    /**
     * Javascript for sending form request
     */
    public function clientSide(){
        echo <<<END
       function cm_updateForm(fields,action,method,result,onReloadAction){
    if(!onReloadAction){
        var onReloadAction = action;
    }
    var updateRequest = new XMLHttpRequest();
    updateRequest.onreadystatechange = function(){
        if(updateRequest.readyState == 4 && updateRequest.status == 200){
            var response = updateRequest.responseText;
            if(response == "reload"){
                cm_loadPage(onReloadAction);
            }else if(response == "refresh"){
                location.reload();
            }
            else{
                var resultBox = document.getElementById(result);
                
                if(resultBox.tagName == "INPUT"){
                    resultBox.value = response;
                }else{
                    //alert(resultBox.tagName);
                    resultBox.innerHTML = response;
                }
            }
        }else if(updateRequest.status == 500){
            document.getElementById(result).innerHTML = "Error 500: Saving failed";
        }
    }
    var postRequest = "";
            
    for(var i = 0; i < fields.length; i++){
        if(i == 0){
            postRequest = fields[i] + "=" + encodeURIComponent(document.getElementById(fields[i]).value);
        }else{
            postRequest = postRequest + "&" + fields[i] + "=" + encodeURIComponent(document.getElementById(fields[i]).value);
        }
    }   
            
    console.log(postRequest);
    updateRequest.open(method,"request.php?update&action=" + action,"true");
    updateRequest.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    updateRequest.send(postRequest);
}
END;
    }
    
    /**
     * 
     * @param array $optionsArray
     * @param string $id - ID for the form. MUST NOT contain underscores
     * @param string $action - URL parameters
     * @param string $method - GET or POST
     * 
     */
    public function __construct($optionsArray,$id, $action, $method){
        $this->optionsArray = $optionsArray;
        $this->id = $id;
        $this->method = $method;
        $this->action = $action;
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    public function build($submitLabel){
        //Start form elements
        echo PHP_EOL, "<!-- ajaxForm2 $this->id starts -->", PHP_EOL;
        echo "<div id='$this->id' class='form'>", PHP_EOL;
        
        
        //Echo title if it is set
        if($this->title){
            echo "<div class='formtitle'>$this->title</div>", PHP_EOL;
        }
        
        //Print each element in the option array
        foreach($this->optionsArray as $optionId => $option){
            /* */
            $fullId = $this->id . '_' . $optionId;
            if($option['type'] == 'button'){
                
            }else if($option['type'] == 'readonly'){
               
            }else{
                 $this->optionsIDs[] = $fullId;
                 $this->printOption($fullId, $option);
            }
            
        }
        
        $fields = "[";
        foreach($this->optionsIDs as $key => $field){
            if($key != 0){ $fields .= ","; }
            $fields .= "'$field'";
        }
        $fields .= "]";
        $idArray = explode("=", $this->action);
        $lastId = end($idArray);
        echo "<div class=\"fieldRow\">";
        echo "<p class=\"response\" id=\"$this->id-response\"></p>";
        echo "<button title=\"Item id: $lastId\"onclick=\"cm_updateForm($fields,'$this->action','POST','$this->id-response');\">$submitLabel</button>", \PHP_EOL;
        echo "</div>";
        echo "</div>", PHP_EOL;
        echo "<!-- ajaxForm2 $this->id ends -->", PHP_EOL;
    }
    
    public function printOption($id,$option){
        $type = $option['type'];
        $label = $option['label'];
        $value = $option['value'];
        if(!$label){
            $label = $id;
        }
        
        switch($type){
            case 'select':
            case 'datalist':
            case 'multiselect':
            case 'customlist':
                $options = $option['options'];
                //echo count($options);
                self::$type($id, $value, $options, $label);
                break;
            default:
                if(method_exists($this,$type)){
                    self::$type($id,$value,$label);
                    
                } else{
                    self::input($id, $value, $type, $label);
                }
                break;
        }
        
    }
    
    /**
     * Convert array of key-pair values (eg. the $_POST array)
     * into a usable optionsArray ensuring compatibility with the
     * corresponding SQL tables
     * 
     * @param array $optionsArray
     * @param array $result
     * @return array
     */
    public static function decodeResult($optionsArray,$result){
        //Pop form ID off each element of the POST request
        $plainIDs = [];
        foreach($result as $key=>$value){
            $array = explode('_', $key);
            unset($array[0]);
            $key = implode('_', $array);
            $plainIDs[$key] = $value;
        }
        //Set value for each element in optionsarray
        foreach($optionsArray as $key=>$option){
            $optionsArray[$key]['value'] = $plainIDs[$key];
        }
        
        return $optionsArray;
    }
    
    public static function fetchOneResult($optionsArray,$tablename, $id){
        //Fetch one result from the database and put values in an optionsArray
    }
    
    public static function button($id, $action, $label){}
    
    public static function text($id, $value, $label){
        echo "<div class=\"fieldRow\"><p>$label</p><input type=\"text\" id=\"$id\" name=\"$id\" value=\"$value\" placeholder=\"$label\"/></div>", \PHP_EOL;
    }
    
    public static function input($id, $value, $type, $label){
        echo "<div class=\"fieldRow\"><p>$label</p><input type=\"$type\" id=\"$id\" name=\"$id\" value=\"$value\" placeholder=\"$label\"/></div>", \PHP_EOL;
    }
    
    public static function select($id, $value, $options, $label){
         //Select field with key-pair elements ("value"=>"label")
        echo "<div class=\"fieldRow\"><p>$label</p>", PHP_EOL;
        echo "<select id=\"$id\" name=\"$id\">", PHP_EOL;
        foreach($options as $key => $choice){
                if($key == $value){
                echo "<option value=\"$key\" selected>$choice</option>", PHP_EOL;
            }else{
                echo "<option value=\"$key\">$choice</option>", PHP_EOL;
            }
        }
        echo "</select></div>", PHP_EOL;
    }
    
    public static function checkBox($id,$value,$label){
       $checked = ($value != false)? "checked":"";
       echo "<div class=\"fieldRow checkbox\"><p>$label</p><input id=\"$id\" name=\"$id\" type=\"checkbox\" value=\"$value\" $checked onclick=\"$(this).val(this.checked ? 1 : 0)\"><label for=\"$id\"><span></span></div>", PHP_EOL;
    }
    
    public static function datalist($id, $value, $options, $label){
        $list_id = $id . '_list';
        echo "<div class=\"fieldRow\"><p>$label</p><input type=\"text\" id=\"$id\" name=\"$id\" value=\"$value\" list=\"$list_id\" placeholder=\"$label\"/>", \PHP_EOL;
        echo "<datalist id=\"$list_id\">", PHP_EOL;
        foreach($options as $option){
            echo "<option value=\"$option\">", PHP_EOL;
        }
        echo "</datalist>", PHP_EOL;
        echo "</div>", PHP_EOL;
    }
    
    public static function customlist($id, $value, $options, $label){
         echo "<div class=\"fieldRow\" style=\"height:auto\"><p>$label</p>", PHP_EOL;
        echo "<select style=\"height:auto;\" id=\"$id\" name=\"$id\" value=\"$value\" >", PHP_EOL;
        if(count($options > 0)){
            foreach($options as $key => $option){
                echo "<option value=\"$key\">$option</option>", PHP_EOL;
            }
        }
        echo "</select>", PHP_EOL;
        echo "</div>";
    }
    
    public static function multiselect($id, $value, $options, $label){
        $values = explode(',',$value);
        echo "<div class=\"fieldRow\" style=\"height:auto\"><p>$label</p>", PHP_EOL;
        echo "<select class=\"multiselect\" multiple id=\"$id\" name=\"$id\" value=\"$value\" >", PHP_EOL;
        if(count($options > 0)){
            foreach($options as $key => $option){
                if(in_array($key, $values)){
                    echo "<option value=\"$key\" selected>$option</option>", PHP_EOL;
                }else{
                    echo "<option value=\"$key\">$option</option>", PHP_EOL;
                }
            }
        }
        echo "</select>", PHP_EOL;
        echo "</div>";
    }
    
    public static function lockedinput($id,$value,$label){
        echo "<div class=\"fieldRow\"><p>$label</p><input onClick=\"this.select();\" readonly value=\"$value\"></div>";

    }
    
    
}

function pl_bar($html){
	return $html;
}

?>