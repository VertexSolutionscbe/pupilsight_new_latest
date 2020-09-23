<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;

require_once '../../pupilsight.php';

$search = $_GET['search'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/substitutes_manage_add.php&search='.$search;

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $subGateway = $container->get(SubstituteGateway::class);

    $data = [
        'pupilsightPersonID' => $_POST['pupilsightPersonID'] ?? '',
        'active'         => $_POST['active'] ?? '',
        'type'           => $_POST['type'] ?? '',
        'details'        => $_POST['details'] ?? '',
        'priority'       => $_POST['priority'] ?? '',
    ];

    // Validate the required values are present
    if (empty($data['pupilsightPersonID']) || empty($data['active'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $person = $container->get(UserGateway::class)->getByID($data['pupilsightPersonID']);

    if (empty($person)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Validate that this person doesn't already have a record
    if (!$subGateway->unique($data, ['pupilsightPersonID'])) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    // Create the substitute
    $pupilsightSubstituteID = $subGateway->insert($data);

    $URL .= !$pupilsightSubstituteID
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}&editID=$pupilsightSubstituteID");
}
