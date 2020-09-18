<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonFieldID = $_GET['pupilsightPersonFieldID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/userFields_delete.php&pupilsightPersonFieldID=$pupilsightPersonFieldID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/userFields.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userFields_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPersonFieldID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonFieldID' => $pupilsightPersonFieldID);
            $sql = 'SELECT * FROM pupilsightPersonField WHERE pupilsightPersonFieldID=:pupilsightPersonFieldID';
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
                $data = array('pupilsightPersonFieldID' => $pupilsightPersonFieldID);
                $sql = 'DELETE FROM pupilsightPersonField WHERE pupilsightPersonFieldID=:pupilsightPersonFieldID';
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
