<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightApplicationFormID = $_POST['pupilsightApplicationFormID'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage_reject.php&pupilsightApplicationFormID=$pupilsightApplicationFormID&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";
$URLReject = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Students/applicationForm_manage_reject.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified

    if ($pupilsightApplicationFormID == '' or $pupilsightSchoolYearID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightApplicationFormID' => $pupilsightApplicationFormID);
            $sql = 'SELECT * FROM pupilsightApplicationForm WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID';
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
                $data = array('pupilsightApplicationFormID' => $pupilsightApplicationFormID);
                $sql = "UPDATE pupilsightApplicationForm SET status='Rejected' WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID";
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
