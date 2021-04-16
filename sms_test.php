<?php

try {
  $numbers = "9883928942";
  $msg = "test ing sdhdjs shdhshd kjsjd s";

  $senderid = "SJBHSB";
  $url1 = "https://japi.instaalerts.zone/httpapi/QueryStringReceiver?ver=1.0&key=WVDLxrEydZYYMKZ8w6aJLQ==&encrpt=0&send=" . $senderid;
  $url1 .= "&text=" . urlencode($msg);
  $url1 .= "&dest=" . $numbers;
  $res = file_get_contents($url1);
  $res1 = explode('&', $res);
  $res2 = explode('=', $res1[1]);
  $res3 = $res2[1];
  //print_r($res);//die();
  $res4 = explode('&', $res1[0]);
  $res5 = explode('=', $res4[0]);
  print_r($res5[1]);
  die();
} catch (Exception $e) {
  print_r($e);
}
