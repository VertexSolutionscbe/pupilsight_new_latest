<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTDayID = $_GET['pupilsightTTDayID'];
$pupilsightTTID = $_GET['pupilsightTTID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
//$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightTTDayRowClassID = $_GET['pupilsightTTDayRowClassID'];
$pupilsightProgramID = $_GET['pupilsightProgramID'];
$pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit_class_delete.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClassID=$pupilsightTTDayRowClassID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit_class.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightProgramID=$pupilsightProgramID&pupilsightYearGroupID=$pupilsightYearGroupID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_delete.php') == false) {
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
                $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID);
                $sql = 'SELECT pupilsightTTDayRowClassID FROM pupilsightTTDayRowClass WHERE pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID ';
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
                    $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID);
                    $sql = 'DELETE FROM pupilsightTTDayRowClass WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightTTDayID=:pupilsightTTDayID ';
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
