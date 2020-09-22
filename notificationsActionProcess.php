<?php
/*
Pupilsight, Flexible & Open School System
*/

include './pupilsight.php';

$URLBack = $_SESSION[$guid]['absoluteURL'].'/index.php?q=notifications.php';
$pupilsightNotificationID = $_GET['pupilsightNotificationID'] ?? '';

if (empty($pupilsightNotificationID) || empty($_SESSION[$guid]['pupilsightPersonID'])) {
    $URLBack = $URLBack.'&return=error1';
    header("Location: {$URLBack}");
    exit();
} else {
    // Check for existence of notification, belonging to this user
    $data = array('pupilsightNotificationID' => $pupilsightNotificationID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
    $sql = "SELECT * FROM pupilsightNotification WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightNotificationID=:pupilsightNotificationID";
    
    $notification = $pdo->selectOne($sql, $data);

    if (empty($notification)) {
        $URLBack = $URLBack.'&return=error2';
        header("Location: {$URLBack}");
        exit();
    } else {
        $URL = $_SESSION[$guid]['absoluteURL'].$notification['actionLink'];

        //Archive notification
        $data = array('pupilsightNotificationID' => $pupilsightNotificationID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        $sql = "UPDATE pupilsightNotification SET status='Archived' WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightNotificationID=:pupilsightNotificationID";
            
        $pdo->update($sql, $data);

        if (!$pdo->getQuerySuccess()) {
            $URLBack = $URLBack.'&return=error2';
            header("Location: {$URLBack}");
            exit();
        }

        //Success 0
        header("Location: {$URL}");
    }
}
