<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightTTSpaceChangeID = $_GET['pupilsightTTSpaceChangeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/spaceChange_manage_delete.php&pupilsightTTSpaceChangeID='.$pupilsightTTSpaceChangeID.'&pupilsightCourseClassID='.$pupilsightCourseClassID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/spaceChange_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/spaceChange_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightTTSpaceChangeID == '' OR $pupilsightCourseClassID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Check for access
            try {
                if ($highestAction == 'Manage Facility Changes_allClasses') {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sqlSelect = 'SELECT pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else if ($highestAction == 'Manage Facility Changes_myDepartment') {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID2' => $pupilsightCourseClassID);
                    $sqlSelect = '(SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)
                    UNION
                    (SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND (pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID2 AND role=\'Coordinator\') AND pupilsightCourseClassID=:pupilsightCourseClassID2)';
                } else {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sqlSelect = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                }
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($resultSelect->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
            else {
                try {
                    if ($highestAction == 'Manage Facility Changes_allClasses' OR $highestAction == 'Manage Facility Changes_myDepartment') {
                        $data = array('pupilsightTTSpaceChangeID' => $pupilsightTTSpaceChangeID);
                        $sql = 'SELECT pupilsightTTSpaceChangeID, pupilsightTTSpaceChange.date, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, spaceOld.name AS spaceOld, spaceNew.name AS spaceNew FROM pupilsightTTSpaceChange JOIN pupilsightTTDayRowClass ON (pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) LEFT JOIN pupilsightSpace AS spaceOld ON (pupilsightTTDayRowClass.pupilsightSpaceID=spaceOld.pupilsightSpaceID) LEFT JOIN pupilsightSpace AS spaceNew ON (pupilsightTTSpaceChange.pupilsightSpaceID=spaceNew.pupilsightSpaceID) WHERE pupilsightTTSpaceChangeID=:pupilsightTTSpaceChangeID ORDER BY date, course, class';
                    } else {
                        $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightTTSpaceChangeID' => $pupilsightTTSpaceChangeID);
                        $sql = 'SELECT pupilsightTTSpaceChangeID, pupilsightTTSpaceChange.date, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, spaceOld.name AS spaceOld, spaceNew.name AS spaceNew FROM pupilsightTTSpaceChange JOIN pupilsightTTDayRowClass ON (pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID)  JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) LEFT JOIN pupilsightSpace AS spaceOld ON (pupilsightTTDayRowClass.pupilsightSpaceID=spaceOld.pupilsightSpaceID) LEFT JOIN pupilsightSpace AS spaceNew ON (pupilsightTTSpaceChange.pupilsightSpaceID=spaceNew.pupilsightSpaceID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightTTSpaceChangeID=:pupilsightTTSpaceChangeID ORDER BY date, course, class';
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2a';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error2b';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightTTSpaceChangeID' => $pupilsightTTSpaceChangeID);
                        $sql = 'DELETE FROM pupilsightTTSpaceChange WHERE pupilsightTTSpaceChangeID=:pupilsightTTSpaceChangeID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URLDelete = $URLDelete.'&return=success0';
                    header("Location: {$URLDelete}");
                }
            }
        }
    }
}
