<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

if ($_POST['pupilsightDepartmentID'] != '') {
    $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
} else {
    $pupilsightDepartmentID = null;
}
$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$orderBy = $_POST['orderBy'];
$description = $_POST['description'];
$map = $_POST['map'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightYearGroupIDList = (isset($_POST['pupilsightYearGroupIDList']))? implode(',', $_POST['pupilsightYearGroupIDList']) : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/course_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightSchoolYearID == '' or $name == '' or $nameShort == '' or $map == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightCourse WHERE (name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID)';
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
                $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'name' => $name, 'nameShort' => $nameShort, 'orderBy' => $orderBy, 'description' => $description, 'map' => $map, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList);
                $sql = 'INSERT INTO pupilsightCourse SET pupilsightDepartmentID=:pupilsightDepartmentID, pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, orderBy=:orderBy, description=:description, map=:map, pupilsightYearGroupIDList=:pupilsightYearGroupIDList';
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
