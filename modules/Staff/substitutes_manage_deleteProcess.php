<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\SubstituteGateway;

require_once '../../pupilsight.php';

$pupilsightSubstituteID = $_GET['pupilsightSubstituteID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/substitutes_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightSubstituteID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $subGateway = $container->get(SubstituteGateway::class);
    $values = $subGateway->getByID($pupilsightSubstituteID);

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $deleted = $subGateway->delete($pupilsightSubstituteID);

    $URL .= !$deleted
        ? '&return=error2'
        : '&return=success0';

    header("Location: {$URL}");
}
