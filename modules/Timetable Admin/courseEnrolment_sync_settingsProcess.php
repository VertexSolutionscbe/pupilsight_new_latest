<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/courseEnrolment_sync.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    $partialFail = false;

    $autoEnrolCourses = (isset($_POST['autoEnrolCourses']))? $_POST['autoEnrolCourses'] : 'N';
    try {
        $data = array('value' => $autoEnrolCourses);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Timetable Admin' AND name='autoEnrolCourses'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $partialFail = true;
    }

    if ($partialFail) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    } else {
        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit;
    }
}
