<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightMessengerCannedResponseID = $_GET['pupilsightMessengerCannedResponseID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/cannedResponse_manage_edit.php&pupilsightMessengerCannedResponseID='.$pupilsightMessengerCannedResponseID;

if (isActionAccessible($guid, $connection2, '/modules/Messenger/cannedResponse_manage_edit.php') == false) {
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
            //Validate Inputs
            $subject = $_POST['subject'];
            $body = $_POST['body'];

            if ($subject == '' or $body == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('subject' => $subject, 'pupilsightMessengerCannedResponseID' => $pupilsightMessengerCannedResponseID);
                    $sql = 'SELECT * FROM pupilsightMessengerCannedResponse WHERE subject=:subject AND NOT pupilsightMessengerCannedResponseID=:pupilsightMessengerCannedResponseID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('subject' => $subject, 'body' => $body, 'pupilsightMessengerCannedResponseID' => $pupilsightMessengerCannedResponseID);
                        $sql = 'UPDATE pupilsightMessengerCannedResponse SET subject=:subject, body=:body WHERE pupilsightMessengerCannedResponseID=:pupilsightMessengerCannedResponseID';
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
    }
}
