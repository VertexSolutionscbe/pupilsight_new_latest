<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;

require_once '../../pupilsight.php';

$search = $_GET['search'] ?? '';
$pupilsightSubstituteID = $_POST['pupilsightSubstituteID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/substitutes_manage_edit.php&pupilsightSubstituteID='.$pupilsightSubstituteID.'&search='.$search;

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $subGateway = $container->get(SubstituteGateway::class);

    $data = [
        'active'         => $_POST['active'] ?? '',
        'type'           => $_POST['type'] ?? '',
        'details'        => $_POST['details'] ?? '',
        'priority'       => $_POST['priority'] ?? '',
    ];

    // Validate the required values are present
    if (empty($pupilsightSubstituteID) || empty($data['active'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $sub = $subGateway->getByID($pupilsightSubstituteID);
    $person = $container->get(UserGateway::class)->getByID($sub['pupilsightPersonID']);

    if (empty($sub) || empty($person)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Update the substitute
    $updated = $subGateway->update($pupilsightSubstituteID, $data);

    $URL .= !$updated
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
