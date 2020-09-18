<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../pupilsight.php';
$pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
$date=date('Y-m-d');
$sql = 'SELECT SUM(seats) as seats_total FROM campaign WHERE status = "2" AND academic_id="'.$pupilsightSchoolYearID.'" AND start_date BETWEEN "'.$date.'" AND "'.$date.'" AND   end_date   BETWEEN "'.$date.'" AND "'.$date.'"';
$result = $connection2->query($sql);
$campaign = $result->fetch();
if(!empty($campaign['seats_total'])){
echo json_encode($campaign);
} else {
  $data=array();
  $data['seats_total']='0';
  echo json_encode($data);
}
