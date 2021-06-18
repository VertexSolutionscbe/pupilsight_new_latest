<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include $_SERVER["DOCUMENT_ROOT"] . '/pupilsight/db.php';
/*
echo '<pre>';
print_r($_POST);
print_r($st_id);
echo '</pre>';
*/
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . '/assign_staff_toSubject.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_staff_toSubject.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
    $ids = $_POST['ids'];

    if ($pupilsightRollGroupID == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $sql = 'UPDATE `assignstaff_tosubject` SET `pupilsightSchoolYearID`='.$pupilsightSchoolYearID.',`pupilsightProgramID`='.$pupilsightProgramID.',`pupilsightYearGroupID`='.$pupilsightYearGroupID.',`pupilsightRollGroupID`='.$pupilsightRollGroupID.' WHERE id IN ('.$ids.') ';
            $conn->query($sql);
        } catch (PDOException $e) {
            $URL .= '&return=error9';
            header("Location: {$URL}");
            exit();
        }
        $URL .= "&return=success0";
        header("Location: {$URL}");
    }
}
