<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightActivityID = $_GET['pupilsightActivityID'];
$pupilsightActivitySlotID = $_GET['pupilsightActivitySlotID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/activities_manage_edit.php&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!

    //Check if school year specified
    if ($pupilsightActivityID == '' or $pupilsightActivitySlotID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightActivitySlotID' => $pupilsightActivitySlotID, 'pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT * FROM pupilsightActivitySlot WHERE pupilsightActivitySlotID=:pupilsightActivitySlotID AND pupilsightActivityID=:pupilsightActivityID';
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
                $data = array('pupilsightActivitySlotID' => $pupilsightActivitySlotID);
                $sql = 'DELETE FROM pupilsightActivitySlot WHERE pupilsightActivitySlotID=:pupilsightActivitySlotID';
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
