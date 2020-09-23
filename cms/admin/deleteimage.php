<?php 
include("template/header.php");
   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $id = $_POST['id'];
   $col = $_POST['col'];
   $delete = $adminlib->deletePupilSightSectionImageData($id,$col);
   echo "<script>alert('Image Deleted Successfully');</script>";
?>
