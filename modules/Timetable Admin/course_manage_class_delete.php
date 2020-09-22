<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_class_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
    $pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    if ($pupilsightCourseClassID == '' or $pupilsightCourseID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
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
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/course_manage_class_deleteProcess.php?pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID");
            echo $form->getOutput();
        }
    }
}
