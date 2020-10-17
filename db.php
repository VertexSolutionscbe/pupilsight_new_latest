<?php
include('C:/xampp/htdocs/pupilsight/config.php');

$conn = new mysqli($databaseServer,$databaseUsername,$databasePassword,$databaseName);

// Check connection
if ($conn -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
 