<?php
/*
Pupilsight, Flexible & Open School System
*/

include './pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=notifications.php';

if (isset($_GET['pupilsightNotificationID']) == false) {
    $URL = $URL.'&return=error1';
    header("Location: {$URL}");
    exit();
} else {
    $pupilsightNotificationID = $_GET['pupilsightNotificationID'];

    //Check for existence of notification, beloning to this user
    try {
        $data = array('pupilsightNotificationID' => $pupilsightNotificationID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sql = 'SELECT * FROM pupilsightNotification WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightNotificationID=:pupilsightNotificationID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo $e->getMessage();
        $URL = $URL.'&return=error2';
        header("Location: {$URL}");
        exit();
    }

    if ($result->rowCount() != 1) {
        $URL = $URL.'&return=error2';
        header("Location: {$URL}");
        exit();
    } else {
        //Delete notification
        try {
            $data = array('pupilsightNotificationID' => $pupilsightNotificationID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = 'DELETE FROM pupilsightNotification WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightNotificationID=:pupilsightNotificationID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Success 0
        $URL = $URL.'&return=success0';
        header("Location: {$URL}");
    }
}
