<?php 
include("template/header.php");
   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $id = $_GET['id'];
   $delete = $adminlib->deletePupilSightSectionData($id);
   echo "<script>alert('Category Deleted Successfully');</script>";
   echo "<script>window.location='category.php'</script>";
   
?>
