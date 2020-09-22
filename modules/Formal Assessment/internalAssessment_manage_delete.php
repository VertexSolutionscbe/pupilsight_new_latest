<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
    $pupilsightInternalAssessmentColumnID = $_GET['pupilsightInternalAssessmentColumnID'];
    if ($pupilsightCourseClassID == '' or $pupilsightInternalAssessmentColumnID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
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
            try {
                $data2 = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID);
                $sql2 = 'SELECT * FROM pupilsightInternalAssessmentColumn WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID';
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data2);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result2->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $result->fetch();
                $values2 = $result2->fetch();

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/internalAssessment_manage_deleteProcess.php?pupilsightInternalAssessmentColumnID=$pupilsightInternalAssessmentColumnID");
                echo $form->getOutput();
            }
        }
    }
}
?>
