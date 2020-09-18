<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../pupilsight.php';
$sql = 'SELECT COUNT(i.id) as total
FROM fn_fee_invoice as  i
WHERE NOT EXISTS (SELECT c.fn_fees_invoice_id FROM fn_fees_collection as  c WHERE i.id = c.fn_fees_invoice_id)';
$result = $connection2->query($sql);
$campaign = $result->fetch();
if(!empty($campaign['total'])){
echo json_encode($campaign);
} else {
  $data=array();
  $data['total']='0';
  echo json_encode($data);
}