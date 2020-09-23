<?php
/*
* Pupilsight, Flexible & Open School System
*Fee Item wise Distribution
 -Class wise distribution (Collected : Total)
*/
include '../pupilsight.php';
$sql = 'SELECT count(c.id) as total FROM  fn_fees_collection as c 
LEFT JOIN pupilsightStudentEnrolment as enrol ON c.pupilsightPersonID=enrol.pupilsightPersonID
LEFT JOIN pupilsightYearGroup as cls ON enrol.pupilsightYearGroupID=cls.pupilsightYearGroupID';
$result = $connection2->query($sql);
$campaign = $result->fetch();
if(!empty($campaign['total'])){
echo json_encode($campaign);
} else {
  $data=array();
  $data['total']='0';
  echo json_encode($data);
}