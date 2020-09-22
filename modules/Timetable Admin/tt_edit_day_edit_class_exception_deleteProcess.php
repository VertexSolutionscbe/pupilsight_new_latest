<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTDayID = $_GET['pupilsightTTDayID'];
$pupilsightTTID = $_GET['pupilsightTTID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightTTDayRowClassID = $_GET['pupilsightTTDayRowClassID'];
$pupilsightTTDayRowClassExceptionID = $_GET['pupilsightTTDayRowClassExceptionID'];

if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '' or $pupilsightCourseClassID == '' or $pupilsightTTDayRowClassID == '' or $pupilsightTTDayRowClassExceptionID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit_class_exception_delete.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClass=$pupilsightTTDayRowClassID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightTTDayRowClassExceptionID=$pupilsightTTDayRowClassExceptionID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit_class_exception.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClass=$pupilsightTTDayRowClassID&pupilsightCourseClassID=$pupilsightCourseClassID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_exception_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightTTDayID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightTTDayRowClassID FROM pupilsightTTDayRowClass JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightTTColumnRowID=$pupilsightTTColumnRowID AND pupilsightTTDayID=$pupilsightTTDayID AND pupilsightTTColumnRowID=$pupilsightTTColumnRowID AND pupilsightCourseClass.pupilsightCourseClassID=$pupilsightCourseClassID";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() < 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                try {
                    $data = array('pupilsightTTDayRowClassExceptionID' => $pupilsightTTDayRowClassExceptionID);
                    $sql = 'SELECT * FROM pupilsightTTDayRowClassException WHERE pupilsightTTDayRowClassExceptionID=:pupilsightTTDayRowClassExceptionID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() < 1) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightTTDayRowClassExceptionID' => $pupilsightTTDayRowClassExceptionID);
                        $sql = 'DELETE FROM pupilsightTTDayRowClassException WHERE pupilsightTTDayRowClassExceptionID=:pupilsightTTDayRowClassExceptionID';
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
