<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightCourseID = $_POST['pupilsightCourseID'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$action = $_POST['action'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/courseEnrolment_manage_class_edit.php&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_class_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else if ($pupilsightCourseClassID == '' or $pupilsightCourseID == '' or $pupilsightSchoolYearID == '' or $action == '') {
    $URL .= '&return=error1';
    header("Location: {$URL}"); 
} else {
    $people = isset($_POST['pupilsightPersonID']) ? $_POST['pupilsightPersonID'] : array();

    //Proceed!
    //Check if person specified
    if (count($people) < 1) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        $partialFail = false;
        if ($action == 'Delete') {
            foreach ($people as $pupilsightPersonID) {
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'DELETE FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail == true;
                }
            }
        }
        else if ($action == 'Copy to class') {
            $pupilsightCourseClassIDCopyTo = (isset($_POST['pupilsightCourseClassIDCopyTo']))? $_POST['pupilsightCourseClassIDCopyTo'] : NULL;
            if (!empty($pupilsightCourseClassIDCopyTo)) {

                foreach ($people as $pupilsightPersonID) {
                    // Check for duplicates
                    try {
                        $dataCheck = array('pupilsightCourseClassIDCopyTo' => $pupilsightCourseClassIDCopyTo, 'pupilsightPersonID' => $pupilsightPersonID);
                        $sqlCheck = 'SELECT pupilsightPersonID FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassIDCopyTo AND pupilsightPersonID=:pupilsightPersonID';
                        $resultCheck = $connection2->prepare($sqlCheck);
                        $resultCheck->execute($dataCheck);
                    } catch (PDOException $e) {
                        $partialFail == true;
                    }

                    // Insert new course participants
                    if ($resultCheck->rowCount() == 0) {
                        try {
                            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightCourseClassIDCopyTo' => $pupilsightCourseClassIDCopyTo);
                            $sql = 'INSERT INTO pupilsightCourseClassPerson (pupilsightCourseClassID, pupilsightPersonID, role, reportable) SELECT :pupilsightCourseClassIDCopyTo, pupilsightPersonID, role, reportable FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail == true;
                        }
                    }


                }
            } else {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            }
        } else if ($action == 'Mark as left') {
            foreach ($people as $pupilsightPersonID) {
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "UPDATE pupilsightCourseClassPerson SET role=CONCAT(role, ' - Left ') WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID AND (role = 'Student' OR role = 'Teacher')";
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
