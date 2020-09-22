<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStaffJobOpeningID = $_GET['pupilsightStaffJobOpeningID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/jobOpenings_manage_delete.php&pupilsightStaffJobOpeningID='.$pupilsightStaffJobOpeningID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/jobOpenings_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/jobOpenings_manage_delete.php') == false) {
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
            //Write to database
            try {
                $data = array('pupilsightStaffJobOpeningID' => $pupilsightStaffJobOpeningID);
                $sql = 'DELETE FROM pupilsightStaffJobOpening WHERE pupilsightStaffJobOpeningID=:pupilsightStaffJobOpeningID';
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
