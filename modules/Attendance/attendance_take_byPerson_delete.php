<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

	$pupilsightAttendanceLogPersonID = isset($_GET['pupilsightAttendanceLogPersonID'])? $_GET['pupilsightAttendanceLogPersonID'] : '';
	$pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';
	$currentDate = isset($_GET['currentDate']) ? $_GET['currentDate'] : '';

	if ( empty($pupilsightAttendanceLogPersonID) || empty($pupilsightPersonID) || empty($currentDate) ) {
		echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
	} else {
	    //Proceed!
	    try {
			$dataPerson = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightAttendanceLogPersonID' => $pupilsightAttendanceLogPersonID );
			$sqlPerson = "SELECT pupilsightAttendanceLogPersonID FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID ";
			$resultPerson = $connection2->prepare($sqlPerson);
			$resultPerson->execute($dataPerson);
		} catch (PDOException $e) {
			echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
		}

	    if ($resultPerson->rowCount() != 1) {
	    	echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
	    } else {
			$form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']. '/attendance_take_byPerson_deleteProcess.php?pupilsightAttendanceLogPersonID='.$pupilsightAttendanceLogPersonID.'&pupilsightPersonID='.$pupilsightPersonID.'&currentDate='.$currentDate);
			echo $form->getOutput();
	    }
	}
}
