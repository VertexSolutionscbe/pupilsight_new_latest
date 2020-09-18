<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/yearGroup_manage_delete.php&pupilsightYearGroupID='.$pupilsightYearGroupID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/yearGroup_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/yearGroup_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightYearGroupID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightYearGroupID' => $pupilsightYearGroupID);
            $sql = 'SELECT * FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
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
                $data = array('pupilsightYearGroupID' => $pupilsightYearGroupID);
                $sql = 'DELETE FROM pupilsightYearGroup WHERE pupilsightYearGroupID=:pupilsightYearGroupID';
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
