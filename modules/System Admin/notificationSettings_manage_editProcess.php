<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\System\NotificationGateway;

include '../../pupilsight.php';

$pupilsightNotificationEventID = (isset($_POST['pupilsightNotificationEventID']))? $_POST['pupilsightNotificationEventID'] : null;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/notificationSettings.php";

if (isActionAccessible($guid, $connection2, '/modules/System Admin/notificationSettings_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    if ($pupilsightNotificationEventID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $gateway = new NotificationGateway($pdo);

        $result = $gateway->selectNotificationEventByID($pupilsightNotificationEventID);
        if ($result->rowCount() != 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        }

        $event = $result->fetch();

        $event['active'] = (isset($_POST['active']))? $_POST['active'] : $event['active'];

        $result = $gateway->updateNotificationEvent($event);

        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit;
    }
}
