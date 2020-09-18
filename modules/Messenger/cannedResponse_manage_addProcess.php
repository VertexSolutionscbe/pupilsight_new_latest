<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/cannedResponse_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Messenger/cannedResponse_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $subject = $_POST['subject'];
    $body = $_POST['body'];

    if ($body == '' or $body == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniqueness
        try {
            $data = array('subject' => $subject);
            $sql = 'SELECT * FROM pupilsightMessengerCannedResponse WHERE subject=:subject';
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
                $data = array('subject' => $subject, 'body' => $body, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = 'INSERT INTO pupilsightMessengerCannedResponse SET subject=:subject, body=:body, pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

            //Success 0
            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
