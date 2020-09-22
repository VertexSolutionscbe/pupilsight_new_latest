<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightApplicationFormID = $_GET['pupilsightApplicationFormID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$search = isset($_GET['search'])? $_GET['search'] : '';
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/applicationForm_manage_edit.php&pupilsightApplicationFormID=$pupilsightApplicationFormID&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Students/applicationForm_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
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
            // Clean up the links between this and other forms
            try {
                $data = array('pupilsightApplicationFormID' => $pupilsightApplicationFormID);
                $sql = 'DELETE FROM pupilsightApplicationFormLink WHERE pupilsightApplicationFormID1=:pupilsightApplicationFormID OR pupilsightApplicationFormID2=:pupilsightApplicationFormID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL = $URL.'&return=success0';
            header("Location: {$URL}");
        }
    }
}
