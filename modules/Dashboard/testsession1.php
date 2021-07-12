<?php
session_start();
if( isset($_POST['select_amount']) ) {
    // save values from other page to session
    $_SESSION['amount'] = $_POST['select_amount'];

}
?>