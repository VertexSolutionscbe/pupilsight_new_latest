<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTColumnID = $_GET['pupilsightTTColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/ttColumn_delete.php&pupilsightTTColumnID='.$pupilsightTTColumnID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/ttColumn.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightTTColumnID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightTTColumnID' => $pupilsightTTColumnID);
            $sql = 'SELECT * FROM pupilsightTTColumn WHERE pupilsightTTColumnID=:pupilsightTTColumnID';
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
            //Delete Course
            try {
                $data = array('pupilsightTTColumnID' => $pupilsightTTColumnID);
                $sql = 'DELETE FROM pupilsightTTColumn WHERE pupilsightTTColumnID=:pupilsightTTColumnID';
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
