<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/department_course_edit.php&pupilsightDepartmentID=$pupilsightDepartmentID&pupilsightCourseID=$pupilsightCourseID";

if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if ($pupilsightDepartmentID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        //Proceed!
        //Validate Inputs
        $description = $_POST['description'];

        if ($pupilsightDepartmentID == '' or $pupilsightCourseID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Check access to specified course
            try {
                $data = array('pupilsightCourseID' => $pupilsightCourseID);
                $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightCourseID=:pupilsightCourseID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Get role within learning area
                $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);

                if ($role != 'Coordinator' and $role != 'Assistant Coordinator' and $role != 'Teacher (Curriculum)') {
                    $URL .= '&return=error0';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('description' => $description, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'UPDATE pupilsightCourse SET description=:description WHERE pupilsightCourseID=:pupilsightCourseID';
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
