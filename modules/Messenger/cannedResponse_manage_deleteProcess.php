<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightMessengerCannedResponseID = $_GET['pupilsightMessengerCannedResponseID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/cannedResponse_manage_delete.php&pupilsightMessengerCannedResponseID='.$pupilsightMessengerCannedResponseID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/cannedResponse_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Messenger/cannedResponse_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if role specified
    if ($pupilsightMessengerCannedResponseID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightMessengerCannedResponseID' => $pupilsightMessengerCannedResponseID);
            $sql = 'SELECT * FROM pupilsightMessengerCannedResponse WHERE pupilsightMessengerCannedResponseID=:pupilsightMessengerCannedResponseID';
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
                $data = array('pupilsightMessengerCannedResponseID' => $pupilsightMessengerCannedResponseID);
                $sql = 'DELETE FROM pupilsightMessengerCannedResponse WHERE pupilsightMessengerCannedResponseID=:pupilsightMessengerCannedResponseID';
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
