<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Set variables
        $today = date('Y-m-d');

        //Proceed!
        //Get viewBy, date and class variables
        $params = [];
        $viewBy = null;
        if (isset($_GET['viewBy'])) {
            $viewBy = $_GET['viewBy'];
        }
        $subView = null;
        if (isset($_GET['subView'])) {
            $subView = $_GET['subView'];
        }
        if ($viewBy != 'date' and $viewBy != 'class') {
            $viewBy = 'date';
        }
        $pupilsightCourseClassID = null;
        $date = null;
        $dateStamp = null;
        if ($viewBy != 'date' and $viewBy != 'class') {
            $viewBy = 'date';
        }
        if ($viewBy == 'date') {
            $date = $_GET['date'];
            if (isset($_GET['dateHuman'])) {
                $date = dateConvert($guid, $_GET['dateHuman']);
            }
            if ($date == '') {
                $date = date('Y-m-d');
            }
            list($dateYear, $dateMonth, $dateDay) = explode('-', $date);
            $dateStamp = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);
            $params += [
                'viewBy' => 'date',
                'date' => $date,
            ];
        } elseif ($viewBy == 'class') {
            $class = null;
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = isset($_GET['pupilsightCourseClassID'])? $_GET['pupilsightCourseClassID'] : '';
            $params += [
                'viewBy' => 'class',
                'date' => $class,
                'pupilsightCourseClassID' => $pupilsightCourseClassID,
                'subView' => $subView,
            ];
        }
        $paramsVar = '&' . http_build_query($params); // for backward compatibile uses below (should be get rid of)

        list($todayYear, $todayMonth, $todayDay) = explode('-', $today);
        $todayStamp = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);

        //Check if school year specified
        $pupilsightCourseClassID = isset($_GET['pupilsightCourseClassID'])? $_GET['pupilsightCourseClassID'] : '';
        $pupilsightPlannerEntryID = isset($_GET['pupilsightPlannerEntryID'])? $_GET['pupilsightPlannerEntryID'] : '';
        if ($pupilsightPlannerEntryID == '' or ($viewBy == 'class' and $pupilsightCourseClassID == 'Y')) {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            $proceed = true;
            try {
                if ($viewBy == 'date') {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('date' => $date, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                    } else {
                        $data = array('date' => $date, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    }
                } else {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                    } else {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    }
                }
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
                //Let's go!
                $row = $result->fetch();
                if ($viewBy == 'date') {
                    $extra = dateConvertBack($guid, $date);
                } else {
                    $extra = $row['course'].'.'.$row['class'];
                }

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/planner_deleteProcess.php?pupilsightPlannerEntryID=$pupilsightPlannerEntryID");
                echo $form->getOutput();
            }
        }
    }
}
