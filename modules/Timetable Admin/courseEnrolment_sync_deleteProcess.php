<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = isset($_POST['pupilsightSchoolYearID'])? $_POST['pupilsightSchoolYearID'] : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/courseEnrolment_sync.php&pupilsightSchoolYearID='.$pupilsightSchoolYearID;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $pupilsightYearGroupID = (isset($_POST['pupilsightYearGroupID']))? $_POST['pupilsightYearGroupID'] : null;

    if (empty($pupilsightYearGroupID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $data = array('pupilsightYearGroupID' => $pupilsightYearGroupID);
        $sql = "DELETE FROM pupilsightCourseClassMap WHERE pupilsightCourseClassMap.pupilsightYearGroupID=:pupilsightYearGroupID";

        $pdo->executeQuery($data, $sql);

        if ($pdo->getQuerySuccess() == false) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        } else {
            $URL .= "&return=success0";
            header("Location: {$URL}");
            exit;
        }
    }
}
