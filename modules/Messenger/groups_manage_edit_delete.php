<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;
use Pupilsight\Domain\Messenger\GroupGateway;

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightGroupID = (isset($_GET['pupilsightGroupID']))? $_GET['pupilsightGroupID'] : null;
    $pupilsightPersonID = (isset($_GET['pupilsightPersonID']))? $_GET['pupilsightPersonID'] : null;

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if ($pupilsightGroupID == '' || $pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $groupGateway = $container->get(GroupGateway::class);

        $highestAction = getHighestGroupedAction($guid, '/modules/Messenger/groups_manage.php', $connection2);
        if ($highestAction == 'Manage Groups_all') {
            $result = $groupGateway->selectGroupByID($pupilsightGroupID);
        } else {
            $result = $groupGateway->selectGroupByIDAndOwner($pupilsightGroupID, $_SESSION[$guid]['pupilsightPersonID']);
        }

        if ($result->isEmpty()) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $result = $groupGateway->selectGroupPersonByID($pupilsightGroupID, $pupilsightPersonID);

            if ($result->isEmpty()) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record cannot be found.');
                echo '</div>';
            } else {
                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/groups_manage_edit_deleteProcess.php?pupilsightGroupID=$pupilsightGroupID&pupilsightPersonID=$pupilsightPersonID");
                echo $form->getOutput();
            }
        }
    }
}
?>