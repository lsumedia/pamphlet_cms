<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class radio extends optionsPage{
    //todo
    public $name = "plugin_radio";
    //public $title = "Radio";
    
    public function displayPage(){
        
    }
    
    public function configPage(){
        ce::begin("ce-large");
        $editForm = new ajaxForm("radio_form",$this->name,"POST");
        $editForm->formTitle("Radio Players");
        ce::end();
    }
    
}

$pluginPages[] = new radio();