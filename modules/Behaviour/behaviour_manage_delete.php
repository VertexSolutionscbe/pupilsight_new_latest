<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_delete.php') == false) {
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
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $pupilsightBehaviourID = $_GET['pupilsightBehaviourID'];
        if ($pupilsightBehaviourID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                $data = array('pupilsightBehaviourID' => $pupilsightBehaviourID);
                $sql = 'SELECT * FROM pupilsightBehaviour WHERE pupilsightBehaviourID=:pupilsightBehaviourID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/behaviour_manage_deleteProcess.php?pupilsightBehaviourID=$pupilsightBehaviourID&pupilsightPersonID=".$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type']);
	            echo $form->getOutput();
            }
        }
    }
}
?>
