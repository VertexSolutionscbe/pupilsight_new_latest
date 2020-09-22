<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightCourseID = $_POST['pupilsightCourseID'];
$reportable = $_POST['reportable'];
$attendance = (isset($_POST['attendance']))? $_POST['attendance'] : 'N';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/course_manage_class_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_class_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $name == '' or $nameShort == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT * FROM pupilsightCourseClass WHERE ((name=:name) OR (nameShort=:nameShort)) AND pupilsightCourseID=:pupilsightCourseID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightCourseID' => $pupilsightCourseID, 'name' => $name, 'nameShort' => $nameShort, 'reportable' => $reportable, 'attendance' => $attendance);
                $sql = 'INSERT INTO pupilsightCourseClass SET pupilsightCourseID=:pupilsightCourseID, name=:name, nameShort=:nameShort, reportable=:reportable, attendance=:attendance';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
