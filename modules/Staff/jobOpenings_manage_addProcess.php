<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/jobOpenings_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/jobOpenings_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $type = $_POST['type'];
    $jobTitle = $_POST['jobTitle'];
    $dateOpen = dateConvert($guid, $_POST['dateOpen']);
    $active = $_POST['active'];
    $description = $_POST['description'];

    if ($type == '' or $jobTitle == '' or $dateOpen == '' or $active == '' or $description == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Write to database
        try {
            $data = array('type' => $type, 'jobTitle' => $jobTitle, 'dateOpen' => $dateOpen, 'active' => $active, 'description' => $description, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = 'INSERT INTO pupilsightStaffJobOpening SET type=:type, jobTitle=:jobTitle, dateOpen=:dateOpen, active=:active, description=:description, pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

        //Success 0
        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
