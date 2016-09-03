<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function get_username(){
    global $auth;
    
    return $auth->server_get_profile()['username'];
}


function profile(){
    global $auth;
    return $auth->server_get_profile();
}

function has_permission($permission_name){
    global $auth;
    return $auth->server_check_permission($permission_name);
}

/* Block users who do not have the given permission */
function block($permission_name){
    global $auth;
    
    if(!has_permission($permission_name)){
        echo "You do not have permission to perform this action";
        die();
    }
    
}

