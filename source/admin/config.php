<?php

/* Pamphlet Config File */

$config = array(
  
    /* Site title (appears in the top bar) */
    'siteTitle' => 'LSUTV Admin',
    
    /* Site name prefix for cookies; make sure this is unique if using multiple Pamphlet installations on one server */
    'sitePrefix' => 'pamphlet',
    
    /* Database options */ 
    
    /* Database name */
    'db_name' => 'grovestr_lsutv',
    /* Database server */
    'db_host' => 'localhost',
    /* Database user name */
    'db_user' => 'grovestr_media',
    /* Database user password */
    'db_pass' => 'i<3media16',
    
    /* Authenticator permission required to access the site
     * (set to NULL to allow access to any logged-in user)
     */
    
    'access_perm' => 'lsutv_edit_content',
    
    /* Last.fm API access - uncomment and fill in to use */
    /*
    'lastfm_apikey' => '',
    'lastfm_secret' => ''
    */
);
