<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStaffJobOpeningID = $_GET['pupilsightStaffJobOpeningID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/jobOpenings_manage_edit.php&pupilsightStaffJobOpeningID='.$pupilsightStaffJobOpeningID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/jobOpenings_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if role specified
    if ($pupilsightStaffJobOpeningID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightStaffJobOpeningID' => $pupilsightStaffJobOpeningID);
            $sql = 'SELECT * FROM pupilsightStaffJobOpening WHERE pupilsightStaffJobOpeningID=:pupilsightStaffJobOpeningID';
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
            //Validate Inputs
            $type = $_POST['type'];
            $jobTitle = $_POST['jobTitle'];
            $dateOpen = dateConvert($guid, $_POST['dateOpen']);
            $active = $_POST['active'];
            $description = $_POST['description'];

            if ($type == '' or $jobTitle == '' or $dateOpen == '' or $active == '' or $description == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('type' => $type, 'jobTitle' => $jobTitle, 'dateOpen' => $dateOpen, 'active' => $active, 'description' => $description, 'pupilsightStaffJobOpeningID' => $pupilsightStaffJobOpeningID);
                    $sql = 'UPDATE pupilsightStaffJobOpening SET type=:type, jobTitle=:jobTitle, dateOpen=:dateOpen, active=:active, description=:description WHERE pupilsightStaffJobOpeningID=:pupilsightStaffJobOpeningID';
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
