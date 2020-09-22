<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStaffApplicationFormID = $_POST['pupilsightStaffApplicationFormID'];
$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage_reject.php&pupilsightStaffApplicationFormID=$pupilsightStaffApplicationFormID&search=$search";
$URLReject = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Staff/applicationForm_manage_reject.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified

    if ($pupilsightStaffApplicationFormID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
            $sql = 'SELECT * FROM pupilsightStaffApplicationForm WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&eeturn=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
                $sql = "UPDATE pupilsightStaffApplicationForm SET status='Rejected' WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLReject = $URLReject.'&return=success0';
            header("Location: {$URLReject}");
        }
    }
}
