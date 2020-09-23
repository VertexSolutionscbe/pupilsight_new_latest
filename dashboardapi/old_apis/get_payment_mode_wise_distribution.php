<?php
/*
* Pupilsight, Flexible & Open School System
*Fee Item wise Distribution
 -Class wise distribution (Collected : Total)
*/
include '../pupilsight.php';
$sql = 'SELECT cls.name as class, COUNT(c.id) as total  FROM  fn_fees_collection as c 
LEFT JOIN pupilsightstudentenrolment as enrol ON c.pupilsightPersonID=enrol.pupilsightPersonID
LEFT JOIN pupilsightyeargroup as cls ON enrol.pupilsightYearGroupID=cls.pupilsightYearGroupID
LEFT JOIN fn_masters as m ON c.payment_mode_id=m.id
WHERE m.type="payment_mode" GROUP BY cls.name';
$result = $connection2->query($sql);
$campaign = $result->fetchAll();
echo json_encode($campaign);