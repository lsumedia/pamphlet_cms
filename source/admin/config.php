<?php

/* Pamphlet Config File */

$config = array(
  
    /* Site title (appears in the top bar) */
    'siteTitle' => 'Pamphlet',
    
    /* Site name prefix for cookies; make sure this is unique if using multiple Pamphlet installations on one server */
    'sitePrefix' => 'pamphlet',
    
    /* Database options */ 
    
    'dbHost' => '',
    'dbDataBase' => '',
    'dbUser' => '',
    'dbPass' => '',
    
    /* Recovery user account which permits logging in if user accounts are broken
    in some way. Disable when not needed. */
    'recoveryUserEnabled' => false,
    'recoveryUserName' => 'recovery',
    'recoveryUserPass' => 'pass',
    
    /* Last.fm API access - uncomment and fill in to use */
    /*
    'lastfm_apikey' => '',
    'lastfm_secret' => ''
    */
);
