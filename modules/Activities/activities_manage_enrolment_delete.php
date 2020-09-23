<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightActivityID = (isset($_GET['pupilsightActivityID']))? $_GET['pupilsightActivityID'] : null;

    $highestAction = getHighestGroupedAction($guid, '/modules/Activities/activities_manage_enrolment.php', $connection2);
    if ($highestAction == 'My Activities_viewEditEnrolment') {

        try {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
            $sql = "SELECT pupilsightActivity.*, NULL as status, pupilsightActivityStaff.role FROM pupilsightActivity JOIN pupilsightActivityStaff ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStaff.pupilsightActivityID) WHERE pupilsightActivity.pupilsightActivityID=:pupilsightActivityID AND pupilsightActivityStaff.pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityStaff.role='Organiser' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if (!$result || $result->rowCount() == 0) {
            //Acess denied
            echo "<div class='alert alert-danger'>";
            echo __('You do not have access to this action.');
            echo '</div>';
            return;
        }
    }

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightActivityID = $_GET['pupilsightActivityID'];
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    if ($pupilsightPersonID == '' or $pupilsightActivityID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT pupilsightActivity.*, pupilsightActivityStudent.*, surname, preferredName FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivity.pupilsightActivityID=pupilsightActivityStudent.pupilsightActivityID) JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityStudent.pupilsightActivityID=:pupilsightActivityID AND pupilsightActivityStudent.pupilsightPersonID=:pupilsightPersonID';
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
            //Let's go!
            $row = $result->fetch();
            if ($_GET['search'] != '' || $_GET['pupilsightSchoolYearTermID'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Activities/activities_manage_enrolment.php&search='.$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID']."&pupilsightActivityID=$pupilsightActivityID'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/activities_manage_enrolment_deleteProcess.php?pupilsightActivityID=$pupilsightActivityID&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID']);
            echo $form->getOutput();
        }
    }
}
?>
