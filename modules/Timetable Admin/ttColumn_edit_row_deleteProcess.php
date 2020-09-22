<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
$pupilsightTTColumnID = $_GET['pupilsightTTColumnID'];

if ($pupilsightTTColumnID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ttColumn_edit_row_delete.php&pupilsightTTColumnID=$pupilsightTTColumnID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ttColumn_edit.php&pupilsightTTColumnID=$pupilsightTTColumnID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn_edit_row_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightTTColumnRowID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
                $sql = 'SELECT * FROM pupilsightTTColumnRow WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
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
                    $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
                    $sql = 'DELETE FROM pupilsightTTColumnRow WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
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
}
