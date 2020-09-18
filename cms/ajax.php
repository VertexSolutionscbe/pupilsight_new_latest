<?php 
include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
$input = $_POST;
$msg = $adminlib->sendMessageData($input);
echo $msg;

?>