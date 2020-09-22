<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightINDescriptorID = $_GET['pupilsightINDescriptorID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/inSettings_delete.php&pupilsightINDescriptorID='.$pupilsightINDescriptorID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/inSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/inSettings_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightINDescriptorID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightINDescriptorID' => $pupilsightINDescriptorID);
            $sql = 'SELECT * FROM pupilsightINDescriptor WHERE pupilsightINDescriptorID=:pupilsightINDescriptorID';
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
                $data = array('pupilsightINDescriptorID' => $pupilsightINDescriptorID);
                $sql = 'DELETE FROM pupilsightINDescriptor WHERE pupilsightINDescriptorID=:pupilsightINDescriptorID';
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
