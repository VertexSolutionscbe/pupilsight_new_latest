<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightActivityID = $_GET['pupilsightActivityID'];
$pupilsightActivityStaffID = $_GET['pupilsightActivityStaffID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/activities_manage_edit.php&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!

    //Check if school year specified
    if ($pupilsightActivityID == '' or $pupilsightActivityStaffID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightActivityStaffID' => $pupilsightActivityStaffID, 'pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT * FROM pupilsightActivityStaff WHERE pupilsightActivityStaffID=:pupilsightActivityStaffID AND pupilsightActivityID=:pupilsightActivityID';
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
                $data = array('pupilsightActivityStaffID' => $pupilsightActivityStaffID);
                $sql = 'DELETE FROM pupilsightActivityStaff WHERE pupilsightActivityStaffID=:pupilsightActivityStaffID';
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
