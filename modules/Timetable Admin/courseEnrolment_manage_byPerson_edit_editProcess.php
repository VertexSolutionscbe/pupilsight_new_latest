<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$type = $_GET['type'];
$allUsers = $_GET['allUsers'];
$search = $_GET['search'];

if ($pupilsightCourseClassID == '' or $pupilsightSchoolYearID == '' or $pupilsightPersonID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/courseEnrolment_manage_byPerson_edit_edit.php&type=$type&pupilsightPersonID=$pupilsightPersonID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseClassID=$pupilsightCourseClassID&allUsers=$allUsers&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_byPerson_edit_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightPersonID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
                $sql = "SELECT role, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.pupilsightPersonID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.name, pupilsightCourseClass.nameShort, pupilsightCourse.pupilsightCourseID, pupilsightCourse.name AS courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourse.description AS courseDescription, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName FROM pupilsightPerson, pupilsightCourseClass, pupilsightCourseClassPerson,pupilsightCourse, pupilsightSchoolYear WHERE pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID AND pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND (pupilsightPerson.status='Full' OR pupilsightPerson.status='Expected')";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                //Validate Inputs
                $role = $_POST['role'];
                $reportable = $_POST['reportable'];

                if ($role == '') {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('role' => $role, 'reportable' => $reportable, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
                        $sql = 'UPDATE pupilsightCourseClassPerson SET role=:role, reportable=:reportable WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
