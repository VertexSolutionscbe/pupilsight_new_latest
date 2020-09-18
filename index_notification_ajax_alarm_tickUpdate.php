<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

$pupilsightAlarmID = $_POST['pupilsightAlarmID'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];

//Proceed!
if ($pupilsightAlarmID == '' or $pupilsightPersonID == '') { echo "<div class='alert alert-danger'>";
    echo __('An error has occurred.');
    echo '</div>';
} else {
    //Check confirmation of alarm
    try {
        $dataConfirm = array('pupilsightAlarmID' => $pupilsightAlarmID, 'pupilsightAlarmID2' => $pupilsightAlarmID, 'pupilsightPersonID' => $pupilsightPersonID);
        $sqlConfirm = 'SELECT surname, preferredName, pupilsightAlarmConfirmID, pupilsightPerson.pupilsightPersonID AS confirmer, pupilsightAlarm.pupilsightPersonID as sounder FROM pupilsightPerson JOIN pupilsightAlarm ON (pupilsightAlarm.pupilsightAlarmID=:pupilsightAlarmID) LEFT JOIN pupilsightAlarmConfirm ON (pupilsightAlarmConfirm.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightAlarmConfirm.pupilsightAlarmID=:pupilsightAlarmID2) WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID';
        $resultConfirm = $connection2->prepare($sqlConfirm);
        $resultConfirm->execute($dataConfirm);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    if ($resultConfirm->rowCount() != 1) {
        echo "<div class='alert alert-danger'>";
        echo __('An error has occurred.');
        echo '</div>';
    } else {
        $rowConfirm = $resultConfirm->fetch();

        echo "<td style='color: #fff'>";
        echo formatName('', $rowConfirm['preferredName'], $rowConfirm['surname'], 'Staff', true, true).'<br/>';
        echo '</td>';
        echo "<td style='color: #fff'>";
        if ($rowConfirm['sounder'] == $rowConfirm['confirmer']) {
            echo __('NA');
        } else {
            if ($rowConfirm['pupilsightAlarmConfirmID'] != '') {
                echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/> ";
            }
        }
        echo '</td>';
        echo "<td style='color: #fff'>";
        if ($rowConfirm['sounder'] != $rowConfirm['confirmer']) {
            if ($rowConfirm['pupilsightAlarmConfirmID'] == '') {
                echo "<a target='_parent' href='".$_SESSION[$guid]['absoluteURL'].'/index_notification_ajax_alarmConfirmProcess.php?pupilsightPersonID='.$rowConfirm['confirmer']."&pupilsightAlarmID=$pupilsightAlarmID'><img title='".__('Confirm')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick_light.png'/></a> ";
            }
        }
        echo '</td>';
    }
}
