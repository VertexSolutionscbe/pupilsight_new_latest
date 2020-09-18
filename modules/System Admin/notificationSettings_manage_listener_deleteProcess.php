<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\System\NotificationGateway;

include '../../pupilsight.php';

$pupilsightNotificationEventID = (isset($_GET['pupilsightNotificationEventID']))? $_GET['pupilsightNotificationEventID'] : null;
$pupilsightNotificationListenerID = (isset($_GET['pupilsightNotificationListenerID']))? $_GET['pupilsightNotificationListenerID'] : null;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/notificationSettings_manage_edit.php&pupilsightNotificationEventID=".$pupilsightNotificationEventID;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/notificationSettings_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    if (empty($pupilsightNotificationEventID) || empty($pupilsightNotificationListenerID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $gateway = new NotificationGateway($pdo);

        $result = $gateway->selectNotificationListener($pupilsightNotificationListenerID);
        if ($result->rowCount() != 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        }

        $result = $gateway->deleteNotificationListener($pupilsightNotificationListenerID);

        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit;
    }
}
