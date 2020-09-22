<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../pupilsight.php';
echo $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

$sql = 'Select * FROM campaign WHERE status = "2" ';
$result = $connection2->query($sql);
$campaign = $result->fetchAll();
echo json_encode($campaign);
