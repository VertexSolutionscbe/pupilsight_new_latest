<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../pupilsight.php';
$sql = 'SELECT COUNT(*) as total  FROM `fn_fees_collection`';
$result = $connection2->query($sql);
$campaign = $result->fetch();
if(!empty($campaign['total'])){
echo json_encode($campaign);
} else {
  $data=array();
  $data['total']='0';
  echo json_encode($data);
}