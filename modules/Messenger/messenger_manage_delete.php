<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messenger_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $search = isset($_GET['search']) ? $_GET['search'] : null;

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $pupilsightMessengerID = $_GET['pupilsightMessengerID'];
        if ($pupilsightMessengerID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Manage Messages_all') {
                    $data = array('pupilsightMessengerID' => $pupilsightMessengerID);
                    $sql = 'SELECT pupilsightMessenger.*, title, surname, preferredName FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightMessengerID=:pupilsightMessengerID';
                } else {
                    $data = array('pupilsightMessengerID' => $pupilsightMessengerID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'SELECT pupilsightMessenger.*, title, surname, preferredName FROM pupilsightMessenger JOIN pupilsightPerson ON (pupilsightMessenger.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightMessengerID=:pupilsightMessengerID AND pupilsightMessenger.pupilsightPersonID=:pupilsightPersonID';
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record cannot be found.');
                echo '</div>';
            } else {
                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/messenger_manage_deleteProcess.php?pupilsightMessengerID=$pupilsightMessengerID&search=$search");
                echo $form->getOutput();
            }
        }
    }
}
