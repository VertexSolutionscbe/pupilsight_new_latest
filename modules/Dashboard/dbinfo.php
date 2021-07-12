<?php

/*$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pd_demo3"; */
include_once($_SERVER['DOCUMENT_ROOT'] . '././config.php');

$servername = "127.0.0.1";
$username = $databaseUsername;
$password = $databasePassword;
$dbname = $databaseName; 

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
?>