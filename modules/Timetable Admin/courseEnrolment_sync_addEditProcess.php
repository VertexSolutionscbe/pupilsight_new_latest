<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightYearGroupID = (isset($_REQUEST['pupilsightYearGroupID']))? $_REQUEST['pupilsightYearGroupID'] : null;
$pupilsightSchoolYearID = (isset($_REQUEST['pupilsightSchoolYearID']))? $_REQUEST['pupilsightSchoolYearID'] : null;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/courseEnrolment_sync_edit.php&pupilsightYearGroupID='.$pupilsightYearGroupID.'&pupilsightSchoolYearID='.$pupilsightSchoolYearID;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    $syncEnabled = (isset($_POST['syncEnabled']))? $_POST['syncEnabled'] : null;
    $syncTo = (isset($_POST['syncTo']))? $_POST['syncTo'] : null;
    
    if (empty($pupilsightYearGroupID) || empty($pupilsightSchoolYearID) || empty($syncTo) || empty($syncEnabled)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $partialFail = false;

        foreach ($syncTo as $pupilsightCourseClassID => $pupilsightRollGroupID) {
            if (!empty($syncEnabled[$pupilsightCourseClassID]) && !empty($pupilsightRollGroupID)) {
                // Enabled and Set: insert or update
                $data = array(
                    'pupilsightCourseClassID' => $pupilsightCourseClassID,
                    'pupilsightRollGroupID' => $pupilsightRollGroupID,
                    'pupilsightYearGroupID' => $pupilsightYearGroupID,
                );

                $sql = "INSERT INTO pupilsightCourseClassMap SET pupilsightCourseClassID=:pupilsightCourseClassID, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightYearGroupID=:pupilsightYearGroupID ON DUPLICATE KEY UPDATE pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightYearGroupID=:pupilsightYearGroupID";
                $pdo->executeQuery($data, $sql);

                if (!$pdo->getQuerySuccess()) $partialFail = true;
            } else {
                // Not enabled or not set: delete record (if one exists)
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
                $sql = "DELETE FROM pupilsightCourseClassMap WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightYearGroupID=:pupilsightYearGroupID";
                $pdo->executeQuery($data, $sql);

                if (!$pdo->getQuerySuccess()) $partialFail = true;
            }
        }

        if ($partialFail) {
            $URL .= '&return=warning3';
            header("Location: {$URL}");
            exit;
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit;
        }
    }
}
