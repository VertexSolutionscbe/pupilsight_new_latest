<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];

if ($pupilsightCourseID == '' or $pupilsightCourseClassID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/studentEnrolment_manage_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable/studentEnrolment_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Check access to the course
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = "SELECT pupilsightCourseClassID, pupilsightCourseClass.name, pupilsightCourseClass.nameShort, pupilsightCourse.pupilsightCourseID, pupilsightCourse.name AS courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourse.description AS courseDescription, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName, pupilsightYearGroupIDList FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (role='Coordinator' OR role='Assistant Coordinator') AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassID=:pupilsightCourseClassID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        }
        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Run through each of the selected participants.
            $update = true;
            $choices = $_POST['Members'];
            $role = $_POST['role'];

            if (count($choices) < 1 or $role == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                foreach ($choices as $t) {
                    //Check to see if student is already registered in this class
                    try {
                        $data = array('pupilsightPersonID' => $t, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sql = 'SELECT * FROM pupilsightCourseClassPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $update = false;
                    }
                    //If student not in course, add them
                    if ($result->rowCount() == 0) {
                        try {
                            $data = array('pupilsightPersonID' => $t, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'role' => $role);
                            $sql = 'INSERT INTO pupilsightCourseClassPerson SET pupilsightPersonID=:pupilsightPersonID, pupilsightCourseClassID=:pupilsightCourseClassID, role=:role';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $update = false;
                        }
                    } else {
                        try {
                            $data = array('pupilsightPersonID' => $t, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'role' => $role);
                            $sql = "UPDATE pupilsightCourseClassPerson SET role=:role, reportable='Y' WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $update = false;
                        }
                    }
                }
                //Write to database
                if ($update == false) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
