<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightTTDayID = $_GET['pupilsightTTDayID'];
    $pupilsightTTID = $_GET['pupilsightTTID'];
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    $pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
    //$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
    $pupilsightProgramID = $_GET['pupilsightProgramID'];
    $pupilsightTTDayRowClassID = $_GET['pupilsightTTDayRowClassID'];
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];

    if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID,'pupilsightTTDayRowClassID'=>$pupilsightTTDayRowClassID);
            $sql = 'SELECT pupilsightTTDayRowClassID FROM pupilsightTTDayRowClass  WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightTTDayID=:pupilsightTTDayID  AND pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID';
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
            $pupilsightTTDayRowClassID = $row['pupilsightTTDayRowClassID'];

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/tt_edit_day_edit_class_deleteProcess.php?&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClassID=$pupilsightTTDayRowClassID&pupilsightProgramID=$pupilsightProgramID&pupilsightYearGroupID=$pupilsightYearGroupID");
            echo $form->getOutput();
        }
    } 
}
