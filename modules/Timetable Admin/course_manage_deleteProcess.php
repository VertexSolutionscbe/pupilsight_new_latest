<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseID = $_GET['pupilsightCourseID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/course_manage_delete.php&pupilsightCourseID='.$pupilsightCourseID.'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'].'&search='.$_POST['search'];
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/course_manage.php&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'].'&search='.$_POST['search'];

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightCourseID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightCourseID=:pupilsightCourseID';
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
                $dataSelect = array('pupilsightCourseID' => $pupilsightCourseID);
                $sqlSelect = 'SELECT pupilsightTTDayRowClassID FROM pupilsightTTDayRowClass JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseID=:pupilsightCourseID';
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
                $dataSelect = array('pupilsightCourseID' => $pupilsightCourseID);
                $sqlSelect = 'SELECT pupilsightCourseClassID FROM pupilsightCourseClass WHERE pupilsightCourseID=:pupilsightCourseID';
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) {
            }
            if ($resultSelect->rowCount() > 0) {
                while ($rowSelect = $resultSelect->fetch()) {
                    try {
                        $dataDelete = array('pupilsightCourseClassID' => $rowSelect['pupilsightCourseClassID']);
                        $sqlDelete = 'DELETE FROM pupilsightTTDayRowClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
                        $resultDelete = $connection2->prepare($sqlDelete);
                        $resultDelete->execute($dataDelete);
                    } catch (PDOException $e) {
                    }
                }
            }

            //Delete students
            try {
                $dataStudent = array('pupilsightCourseID' => $pupilsightCourseID);
                $sqlStudent = 'SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseID=:pupilsightCourseID';
                $resultStudent = $connection2->prepare($sqlStudent);
                $resultStudent->execute($dataStudent);
            } catch (PDOException $e) {
            }
            while ($rowStudent = $resultStudent->fetch()) {
                try {
                    $dataDelete = array('pupilsightCourseClassID' => $rowStudent['pupilsightCourseClassID']);
                    $sqlDelete = 'DELETE FROM pupilsightCourseClassPerson WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
                    $resultDelete = $connection2->prepare($sqlDelete);
                    $resultDelete->execute($dataDelete);
                } catch (PDOException $e) {
                }
            }

            //Delete classes
            try {
                $dataDelete = array('pupilsightCourseID' => $pupilsightCourseID);
                $sqlDelete = 'DELETE FROM pupilsightCourseClass WHERE pupilsightCourseID=:pupilsightCourseID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Delete Course
            try {
                $dataDelete = array('pupilsightCourseID' => $pupilsightCourseID);
                $sqlDelete = 'DELETE FROM pupilsightCourse WHERE pupilsightCourseID=:pupilsightCourseID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
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
