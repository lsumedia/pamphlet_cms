<?php
/* NAS Testing Server */

$mysql_host = "localhost";
$mysql_database = "falkegg";
$mysql_user = "root";
$mysql_password = "";



$connection =  new mysqli($mysql_host,$mysql_user,$mysql_password,$mysql_database);
if($connection->mysqli_errno){
	echo "Failed to connect to MySQL:(" . $connection->mysqli_errno . ") " . $connection->connect_error;
}


/* 1&1 Hosting */

/*
    $connection = mysqli_connect($host_name, $user_name, $password, $database);
    if (mysqli_connect_errno())
    {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
*/
?>