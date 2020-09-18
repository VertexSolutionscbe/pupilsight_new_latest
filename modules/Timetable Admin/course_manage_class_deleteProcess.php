<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];

if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/course_manage_class_delete.php&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightSchoolYearID=$pupilsightSchoolYearID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/course_manage_edit.php&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_class_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightCourseClassID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = 'SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
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
                //Try to delete entries in pupilsightTTDayRowClass
                try {
                    $dataSelect = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sqlSelect = 'SELECT * FROM pupilsightTTDayRowClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                if ($resultSelect->rowCount() > 0) {
                    while ($rowSelect = $resultSelect->fetch()) {
                        try {
                            $dataDelete = array('pupilsightTTDayRowClassID' => $rowSelect['pupilsightTTDayRowClassID']);
                            $sqlDelete = 'DELETE FROM pupilsightTTDayRowClassException WHERE pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID';
                            $resultDelete = $connection2->prepare($sqlDelete);
                            $resultDelete->execute($dataDelete);
                        } catch (PDOException $e) {
                        }
                    }
                }

                try {
                    $dataDelete = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sqlDelete = 'DELETE FROM pupilsightTTDayRowClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
                    $resultDelete = $connection2->prepare($sqlDelete);
                    $resultDelete->execute($dataDelete);
                } catch (PDOException $e) {
                }

                //Delete students and other participants
                try {
                    $dataDelete = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sqlDelete = 'DELETE FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
                    $resultDelete = $connection2->prepare($sqlDelete);
                    $resultDelete->execute($dataDelete);
                } catch (PDOException $e) {
                }

                //Write to database
                try {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'DELETE FROM pupilsightCourseClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
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
