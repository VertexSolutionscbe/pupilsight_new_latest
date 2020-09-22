<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Messenger\GroupGateway;

include '../../pupilsight.php';

$pupilsightGroupID = isset($_GET['pupilsightGroupID'])? $_GET['pupilsightGroupID'] : '';
$pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/groups_manage_edit_delete.php&pupilsightGroupID=$pupilsightGroupID&pupilsightPersonID=$pupilsightPersonID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/groups_manage_edit.php&pupilsightGroupID=$pupilsightGroupID";

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else if (empty($pupilsightGroupID) || empty($pupilsightPersonID)) {
    $URL .= '&return=error1';
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
        $deleted = $groupGateway->deleteGroupPerson($pupilsightGroupID, $pupilsightPersonID);

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
