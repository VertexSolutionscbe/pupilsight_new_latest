<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    if (empty($pupilsightYearGroupID) || empty($pupilsightSchoolYearID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_sync_deleteProcess.php');
        echo $form->getOutput();
    }
}
