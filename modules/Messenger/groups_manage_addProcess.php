<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Messenger\GroupGateway;

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/groups_manage_add.php";

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    //Validate Inputs
    $name = isset($_POST['name'])? $_POST['name'] : '';
    $choices = isset($_POST['members'])? $_POST['members'] : array();

    if (empty($name) || empty($choices)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $groupGateway = $container->get(GroupGateway::class);

        //Create the group
        $data = array('pupilsightPersonIDOwner' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'name' => $name);
        $AI = $groupGateway->insertGroup($data);

        if (!$AI) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            $partialFail = false;

            //Run through each of the selected participants.
            foreach ($choices as $pupilsightPersonID) {
                $data = array('pupilsightGroupID' => $AI, 'pupilsightPersonID' => $pupilsightPersonID);
                $inserted = $groupGateway->insertGroupPerson($data);
                $partialFail &= !$inserted;
            }

            //Write to database
            if ($partialFail) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
                exit;
            } else {
                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
                exit;
            }
        }
    }
}
