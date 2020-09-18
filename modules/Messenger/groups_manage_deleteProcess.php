<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Messenger\GroupGateway;

include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$pupilsightGroupID = isset($_GET['pupilsightGroupID'])? $_GET['pupilsightGroupID'] : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/groups_manage_delete.php&pupilsightGroupID=$pupilsightGroupID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/groups_manage.php&pupilsightGroupID=$pupilsightGroupID";

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else if (empty($pupilsightGroupID)) { 
    $URL .= '&return=error2';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    $groupGateway = $container->get(GroupGateway::class);

    $highestAction = getHighestGroupedAction($guid, '/modules/Messenger/groups_manage.php', $connection2);
    if ($highestAction == 'Manage Groups_all') {
        $values = $groupGateway->selectGroupByID($pupilsightGroupID);
    } else {
        $values = $groupGateway->selectGroupByIDAndOwner($pupilsightGroupID, $_SESSION[$guid]['pupilsightPersonID']);
    }

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        $deleted = $groupGateway->deleteGroup($pupilsightGroupID);
        $groupGateway->deletePeopleByGroupID($pupilsightGroupID);

        if (!$deleted) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        } else {
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
            exit;
        }
    }
}
