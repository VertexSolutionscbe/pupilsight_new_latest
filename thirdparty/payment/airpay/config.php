<?php

// Keep this file very secured. You may keep it in the folders below Document Root.

  include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight.php';

  $sql = 'SELECT * FROM fn_fee_payment_gateway WHERE name = "AIRPAY" ';
  $result = $connection2->query($sql);
  $value = $result->fetch();

  if (!empty($value)) {
      $username =  $value['username']; // Username
      $password =  $value['password']; // Password
      $secret =    $value['key_secret']; // API key
      $mercid = $value['mid']; //Merchant ID
  } else {
      $username =  '5926256'; // Username
      $password =  'me65Pf2K'; // Password
      $secret =    'A3brM5V9wjMWZh29'; // API key
      $mercid = '40594'; //Merchant ID
  }


  

	// You will get above 4 variales on settings Page.	

?>