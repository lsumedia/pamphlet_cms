<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of boulevard
 *
 * @author Cameron
 */
class boulevard extends optionsPage{
    //put your code here
    public $name = "boulevard";
    //public $title = "The Boulevard";
    
    public function configPage() {
        parent::configPage();
        $ce = new centralElement("ce-medium");
        echo "<h2>Under construction</h2>";
        $ce->end();
    }
}

$boulevard = new boulevard();
$pluginPages[] = $boulevard;
