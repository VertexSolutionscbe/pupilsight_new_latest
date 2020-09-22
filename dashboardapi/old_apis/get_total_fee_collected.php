<?php
/*
Pupilsight, Flexible & Open School System
* Class wise distribution (Collected : Total)
*/
include '../pupilsight.php';
$sql = 'SELECT COUNT(i.id) as total
FROM fn_fee_invoice as  i
WHERE  EXISTS (SELECT c.fn_fees_invoice_id FROM fn_fees_collection as  c WHERE i.id = c.fn_fees_invoice_id)';
//$sql='SELECT count(c.id) as total FROM  fn_fees_collection as c 
//LEFT JOIN pupilsightstudentenrolment as enrol ON c.pupilsightPersonID=enrol.pupilsightPersonID
//LEFT JOIN pupilsightyeargroup as cls ON enrol.pupilsightYearGroupID=cls.pupilsightYearGroupID';
$result = $connection2->query($sql);
$campaign = $result->fetch();
if(!empty($campaign['total'])){
echo json_encode($campaign);
} else {
  $data=array();
  $data['total']='0';
  echo json_encode($data);
}