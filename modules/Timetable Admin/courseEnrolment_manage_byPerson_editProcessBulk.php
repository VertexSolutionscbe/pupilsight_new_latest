<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$type = $_POST['type'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$action = $_POST['action'];
$allUsers = $_GET['allUsers'];
$search = isset($_GET['search'])? $_GET['search'] : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/courseEnrolment_manage_byPerson_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightPersonID=$pupilsightPersonID&type=$type&allUsers=$allUsers&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else if ($pupilsightPersonID == '' or $pupilsightSchoolYearID == '' or $action == '') {
    $URL .= '&return=error1';
    header("Location: {$URL}"); 
} else {
    $classes = isset($_POST['pupilsightCourseClassID'])? $_POST['pupilsightCourseClassID'] : array();

    //Proceed!
    //Check if person specified
    if (count($classes) <= 0) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        $partialFail = false;
        if ($action == 'Delete') {
            foreach ($classes as $pupilsightCourseClassID) {
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'DELETE FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail == true;
                }
            }
        } else {
            foreach ($classes as $pupilsightCourseClassID) {
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "UPDATE pupilsightCourseClassPerson SET role=CONCAT(role, ' - Left') WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND (role = 'Student' OR role = 'Teacher')";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail == true;
                }
            }
        }

        if ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
    
}
