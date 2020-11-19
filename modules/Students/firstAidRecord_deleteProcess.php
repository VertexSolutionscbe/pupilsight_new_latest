<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFirstAidID = $_GET['pupilsightFirstAidID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/firstAidRecord_delete.php&pupilsightFirstAidID='.$pupilsightFirstAidID.'&search='.$_GET['search'];
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/firstAidRecord.php&search='.$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFirstAidID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFirstAidID' => $pupilsightFirstAidID);
            $sql = 'SELECT * FROM pupilsightFirstAid WHERE pupilsightFirstAidID=:pupilsightFirstAidID';
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
                
                $data = array('pupilsightFirstAidID' => $pupilsightFirstAidID);
                $sql = 'DELETE FROM pupilsightFirstAid WHERE pupilsightFirstAidID=:pupilsightFirstAidID';
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
