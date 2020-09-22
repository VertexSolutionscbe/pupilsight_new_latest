<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\SMS;

require_once '../../pupilsight.php';

$from = $_POST['from'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$smsGateway = getSettingByScope($connection2, 'Messenger', 'smsGateway');

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage_edit.php') == false) {
    die(__('Your request failed because you do not have access to this action.'));
} elseif (empty($from) || empty($phoneNumber)) {
    die(__('You have not specified one or more required parameters.'));
} elseif (empty($smsGateway)) {
    die(sprintf(__('SMS NOT CONFIGURED. Please contact %1$s for help.'), $_SESSION[$guid]['organisationAdministratorName']));
} else {
    // Proceed!
    $body = __('{name} sent you a test SMS via {system}', ['name' => $from, 'system' => $_SESSION[$guid]['systemName']]);

    $result = $container->get(SMS::class)
        ->content($body)
        ->send([$phoneNumber]);

    echo !empty($result)
        ? __('Your request was completed successfully.')
        : __('Your request failed.');
}
