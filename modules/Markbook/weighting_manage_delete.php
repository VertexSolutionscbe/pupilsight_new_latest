<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;
use Pupilsight\Services\Format;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Markbook/weighting_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {

        if (getSettingByScope($connection2, 'Markbook', 'enableColumnWeighting') != 'Y') {
            //Acess denied
            echo "<div class='alert alert-danger'>";
            echo __('Your request failed because you do not have access to this action.');
            echo '</div>';
        }

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Get class variable
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';

        if ($pupilsightCourseClassID == '') {
            echo '<h1>';
            echo __('Delete Markbook Weighting');
            echo '</h1>';
            echo "<div class='alert alert-warning'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';

            return;
        }
        //Check existence of and access to this class.
        else {
            try {
                if ($highestAction == 'Manage Weightings_everything') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo '<h1>';
                echo __('Delete Markbook Weighting');
                echo '</h1>';
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $pupilsightMarkbookWeightID = (isset($_GET['pupilsightMarkbookWeightID']))? $_GET['pupilsightMarkbookWeightID'] : null;
                try {
                    $data2 = array('pupilsightMarkbookWeightID' => $pupilsightMarkbookWeightID);
                    $sql2 = 'SELECT * FROM pupilsightMarkbookWeight WHERE pupilsightMarkbookWeightID=:pupilsightMarkbookWeightID';
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($data2);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result2->rowCount() != 1) {
                    echo '<h1>';
                    echo __('Delete Markbook Weighting');
                    echo '</h1>';
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    $row = $result->fetch();
                    $row2 = $result2->fetch();

                    $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/weighting_manage_deleteProcess.php?pupilsightCourseClassID=$pupilsightCourseClassID");
                    echo $form->getOutput();
                }
            }
        }
    }
}
