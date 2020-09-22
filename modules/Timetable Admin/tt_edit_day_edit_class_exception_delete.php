<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;
use Pupilsight\Domain\Timetable\TimetableDayGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_exception_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'] ?? '';
    $pupilsightTTID = $_GET['pupilsightTTID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'] ?? '';
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
    $pupilsightTTDayRowClassID = $_GET['pupilsightTTDayRowClassID'] ?? '';
    $pupilsightTTDayRowClassExceptionID = $_GET['pupilsightTTDayRowClassExceptionID'] ?? '';

    if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '' or $pupilsightCourseClassID == '' or $pupilsightTTDayRowClassID == '' or $pupilsightTTDayRowClassExceptionID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $timetableDayGateway = $container->get(TimetableDayGateway::class);
        $values = $timetableDayGateway->getTTDayRowClassByID($pupilsightTTDayID, $pupilsightTTColumnRowID, $pupilsightCourseClassID);

        if (empty($values)) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $timetableDayGateway->getTTDayRowClassExceptionByID($pupilsightTTDayRowClassExceptionID);

            if (empty($values)) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record cannot be found.');
                echo '</div>';
            } else {
                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/tt_edit_day_edit_class_exception_deleteProcess.php?&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClassID=$pupilsightTTDayRowClassID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightTTDayRowClassExceptionID=$pupilsightTTDayRowClassExceptionID");
                echo $form->getOutput();
            }
        }
    }
}
