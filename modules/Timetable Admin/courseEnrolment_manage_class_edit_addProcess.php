<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];

if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '' or $pupilsightCourseClassID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/courseEnrolment_manage_class_edit.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_class_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
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
                        $sql = 'UPDATE pupilsightCourseClassPerson SET role=:role WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID';
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
