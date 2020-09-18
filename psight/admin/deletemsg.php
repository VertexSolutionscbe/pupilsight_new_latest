<?php 
include("template/header.php");
   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $id = $_GET['id'];
   $delete = $adminlib->deletePupilSightMessageData($id);
   echo "<script>window.location='message.php'</script>";
?>
