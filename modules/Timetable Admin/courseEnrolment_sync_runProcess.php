<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightYearGroupIDList = (isset($_POST['pupilsightYearGroupIDList']))? $_POST['pupilsightYearGroupIDList'] : null;
$pupilsightSchoolYearID = (isset($_POST['pupilsightSchoolYearID']))? $_POST['pupilsightSchoolYearID'] : null;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/courseEnrolment_sync_run.php&pupilsightSchoolYearID='.$pupilsightSchoolYearID.'&pupilsightYearGroupIDList='.$pupilsightYearGroupIDList;
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/courseEnrolment_sync.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync_run.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    $syncData = (isset($_POST['syncData']))? $_POST['syncData'] : false;

    if (empty($pupilsightYearGroupIDList) || empty($pupilsightSchoolYearID) || empty($syncData)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $partialFail = false;

        foreach ($syncData as $pupilsightRollGroupID => $usersToEnrol) {
            if (empty($usersToEnrol)) continue;

            foreach ($usersToEnrol as $pupilsightPersonID => $role) {
                $data = array(
                    'pupilsightRollGroupID' => $pupilsightRollGroupID,
                    'pupilsightPersonID' => $pupilsightPersonID,
                    'role' => $role,
                );

                $sql = "INSERT INTO pupilsightCourseClassPerson (`pupilsightCourseClassID`, `pupilsightPersonID`, `role`, `reportable`)
                        SELECT pupilsightCourseClassMap.pupilsightCourseClassID, :pupilsightPersonID, :role, 'Y'
                        FROM pupilsightCourseClassMap
                        LEFT JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClassMap.pupilsightCourseClassID AND pupilsightCourseClassPerson.role=:role)
                        WHERE pupilsightCourseClassMap.pupilsightRollGroupID=:pupilsightRollGroupID
                        AND pupilsightCourseClassPerson.pupilsightCourseClassPersonID IS NULL";
                $pdo->executeQuery($data, $sql);

                if (!$pdo->getQuerySuccess()) $partialFail = true;
            }
        }

        if ($partialFail) {
            $URL .= '&return=warning3';
            header("Location: {$URL}");
            exit;
        } else {
            $URLSuccess .= '&return=success0';
            header("Location: {$URLSuccess}");
            exit;
        }
    }
}
