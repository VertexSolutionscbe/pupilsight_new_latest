<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../pupilsight.php';
$sql = 'Select count(*) as campaign_total FROM campaign WHERE status = "2" ';
$result = $connection2->query($sql);
$campaign = $result->fetch();
if(!empty($campaign['campaign_total'])){
echo json_encode($campaign);
} else {
  $data=array();
  $data['campaign_total']='0';
  echo json_encode($data);
}