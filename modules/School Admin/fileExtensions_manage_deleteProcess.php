<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFileExtensionID = $_GET['pupilsightFileExtensionID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fileExtensions_manage_delete.php&pupilsightFileExtensionID='.$pupilsightFileExtensionID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fileExtensions_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/fileExtensions_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFileExtensionID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFileExtensionID' => $pupilsightFileExtensionID);
            $sql = 'SELECT * FROM pupilsightFileExtension WHERE pupilsightFileExtensionID=:pupilsightFileExtensionID';
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
                $data = array('pupilsightFileExtensionID' => $pupilsightFileExtensionID);
                $sql = 'DELETE FROM pupilsightFileExtension WHERE pupilsightFileExtensionID=:pupilsightFileExtensionID';
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
