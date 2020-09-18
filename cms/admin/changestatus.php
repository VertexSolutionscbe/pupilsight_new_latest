<?php 
include("template/header.php");
   include_once '../w2f/adminLib.php';
   $adminlib = new adminlib();
   $name = $_POST['name'];
   $val = $_POST['val'];
   $update = $adminlib->changeSectionStatus($name,$val);
?>
