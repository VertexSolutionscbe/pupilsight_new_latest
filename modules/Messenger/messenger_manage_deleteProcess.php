<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightMessengerID = $_GET['pupilsightMessengerID'];
$search = null;
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/messenger_manage_delete.php&search=$search&pupilsightMessengerID=".$pupilsightMessengerID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/messenger_manage.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messenger_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightMessengerID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                if ($highestAction == 'Manage Messages_all') {
                    $data = array('pupilsightMessengerID' => $pupilsightMessengerID);
                    $sql = 'SELECT pupilsightMessenger.*, title, surname, preferredName FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightMessengerID=:pupilsightMessengerID';
                } else {
                    $data = array('pupilsightMessengerID' => $pupilsightMessengerID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'SELECT pupilsightMessenger.*, title, surname, preferredName FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightMessengerID=:pupilsightMessengerID AND pupilsightMessenger.pupilsightPersonID=:pupilsightPersonID';
                }
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
                    $data = array('pupilsightMessengerID' => $pupilsightMessengerID);
                    $sql = 'DELETE FROM pupilsightMessenger WHERE pupilsightMessengerID=:pupilsightMessengerID';
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
