<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightApplicationFormID = $_POST['pupilsightApplicationFormID'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage_delete.php&pupilsightApplicationFormID=$pupilsightApplicationFormID&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

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
            $row = $result->fetch();

            //Write to database
            try {
                $data = array('pupilsightApplicationFormID' => $pupilsightApplicationFormID);
                $sql = 'DELETE FROM pupilsightApplicationForm WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Attempt to write logo
            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], getModuleIDFromName($connection2, 'Students'), $_SESSION[$guid]['pupilsightPersonID'], 'Application Form - Delete', array('pupilsightApplicationFormID' => $pupilsightApplicationFormID, 'applicationFormContents' => serialize($row)), $_SERVER['REMOTE_ADDR']);


            // Clean up the application form relationships
            try {
                $data = array('pupilsightApplicationFormID' => $pupilsightApplicationFormID);
                $sql = 'DELETE FROM pupilsightApplicationFormRelationship WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

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

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
