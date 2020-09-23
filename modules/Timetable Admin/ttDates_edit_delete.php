<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttDates_edit_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $dateStamp = $_GET['dateStamp'] ?? '';
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'] ?? '';
    if ($pupilsightSchoolYearID == '' or $dateStamp == '' or $pupilsightTTDayID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('date' => date('Y-m-d', $dateStamp), 'pupilsightTTDayID' => $pupilsightTTDayID);
            $sql = 'SELECT * FROM pupilsightTTDayDate JOIN pupilsightTTDay ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTT ON (pupilsightTTDay.pupilsightTTID=pupilsightTT.pupilsightTTID) WHERE date=:date AND pupilsightTTDay.pupilsightTTDayID=:pupilsightTTDayID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();

            //Proceed!
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/ttDates_edit_deleteProcess.php?pupilsightSchoolYearID=$pupilsightSchoolYearID&dateStamp=$dateStamp&pupilsightTTDayID=$pupilsightTTDayID");
            echo $form->getOutput();
        }
    }
}
