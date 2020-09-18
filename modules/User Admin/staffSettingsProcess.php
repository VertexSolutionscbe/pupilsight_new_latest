<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\System\SettingGateway;

require_once '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/staffSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $partialFail = false;

    $settingGateway = $container->get(SettingGateway::class);

    $settingsToUpdate = [
        'Staff' => [
            'absenceApprovers',
            'substituteTypes',
            'urgentNotifications',
            'urgencyThreshold',
            'absenceFullDayThreshold',
            'absenceHalfDayThreshold',
            'absenceNotificationGroups',
            'salaryScalePositions',
            'responsibilityPosts',
            'jobOpeningDescriptionTemplate',
        ],
        'System' => [
            'nameFormatStaffFormal',
            'nameFormatStaffFormalReversed',
            'nameFormatStaffInformal',
            'nameFormatStaffInformalReversed',
        ]
    ];

    foreach ($settingsToUpdate as $scope => $settings) {
        foreach ($settings as $name) {
            $value = $_POST[$name] ?? '';

            $updated = $settingGateway->updateSettingByScope($scope, $name, $value);
            $partialFail &= !$updated;
        }
    }
   
    $URL .= $partialFail
        ? '&return=error2'
        : '&return=success0';
    header("Location: {$URL}");
}
