<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\System\NotificationGateway;

include '../../pupilsight.php';

$pupilsightNotificationEventID = (isset($_POST['pupilsightNotificationEventID']))? $_POST['pupilsightNotificationEventID'] : null;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/notificationSettings_manage_edit.php&pupilsightNotificationEventID=".$pupilsightNotificationEventID;

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

        $pupilsightPersonID = (isset($_POST['pupilsightPersonID']))? $_POST['pupilsightPersonID'] : '';
        $scopeType = (isset($_POST['scopeType']))? $_POST['scopeType'] : '';
        $scopeID = (isset($_POST[$scopeType]))? $_POST[$scopeType] : 0;

        if (empty($pupilsightPersonID) || empty($scopeType)) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        } else {
            $listener = array(
                'pupilsightNotificationEventID' => $pupilsightNotificationEventID,
                'pupilsightPersonID'            => $pupilsightPersonID,
                'scopeType'                 => $scopeType,
                'scopeID'                   => $scopeID
            );

            $result = $gateway->insertNotificationListener($listener);

            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit;
        }
    }
}
