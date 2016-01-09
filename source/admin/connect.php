<?php
$mysql_host = "localhost";
$mysql_database = "";
$mysql_user = "";
$mysql_password = "";


$connection = new mysqli($mysql_host,$mysql_user,$mysql_password,$mysql_database);
if($connection -> connect_errno){
	echo "Failed to connect to MySQL:(" . $connection->connect_errno . ") " . $connection->connect_error;
}
?>