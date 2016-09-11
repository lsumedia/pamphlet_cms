<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class blog extends optionsPage{
    
    public $name = 'blog';
    public $title = 'Blog';
   
    public static function formArray(){
        global $auth;
        return [
            'title' => ['type' => 'text', 'label' => 'Title', 'value' => ''],
            'author' => ['type' => 'select', 'label' => 'Author', 'options' => self::select_users(), 'value' => $auth->server_get_profile()['username']],
            'date' => ['type' => 'date', 'label' => 'Date', 'value' => Date('Y-m-d')],
            'featured_image' => ['type' => 'file', 'label' => 'Thumbnail image', 'value' => ''],
            'content' => ['type' => 'richtext', 'label' => 'Content', 'value' => ''],
            
        ];
    }
    
    public static function select_users(){
        $users = authenticator::server_get_users();
        
        $clean = [];
        
        foreach($users as $user){
            $clean[$user['username']] = $user['fullname'] . " ({$user['username']})";
        }
        return $clean;
    }
    
    public function configPage() {
        global $auth;
        ce::begin('');
        
        $form = new customForm(self::formArray(), 'editForm', 'blog', 'POST', 'blog');
        
        $form->title = 'New blog post';
        $form->build('Add post');
        
        
        ce::end();
    }
    
    public function updatePage() {
        
        $sql = customForm::insertSQL(self::formArray(), $_POST, 'blog');
        echo $sql;
        
    }
    
}

$pluginPages[] = new blog();