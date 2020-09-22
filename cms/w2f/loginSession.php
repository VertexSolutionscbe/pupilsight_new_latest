<?php 
@session_start();
ob_start();
if(!isset($_SESSION['pusrid'])){
  header('location:index.php');
}
