<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_bump.php') == false) {
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
        if ($viewBy == 'class') {
            $class = null;
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
            $params += [
                'viewBy' => 'class',
                'date' => $class,
                'pupilsightCourseClassID' => $pupilsightCourseClassID,
                'subView' => $subView,
            ];
        }

        if ($viewBy == 'date') {
            echo "<div class='alert alert-danger'>";
            echo __('You do not have access to this action.');
            echo '</div>';
        } else {
            list($todayYear, $todayMonth, $todayDay) = explode('-', $today);
            $todayStamp = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);

            //Check if school year specified
            $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
            $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
            if ($pupilsightPlannerEntryID == '' or ($viewBy == 'class' and $pupilsightCourseClassID == 'Y')) {
                echo "<div class='alert alert-danger'>";
                echo __('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                $proceed = true;
                try {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                    } else {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
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
                    $values = $result->fetch();

                    $page->breadcrumbs
                        ->add(__('Planner for {classDesc}', [
                            'classDesc' => $values['course'].'.'.$values['class'],
                        ]), 'planner.php', $params)
                        ->add(__('Bump Lesson Plan'));

                    if (isset($_GET['return'])) {
                        returnProcess($guid, $_GET['return'], null, null);
                    }

                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/planner_bumpProcess.php?pupilsightPlannerEntryID=$pupilsightPlannerEntryID");

                    $form->addHiddenValue('viewBy', $viewBy);
                    $form->addHiddenValue('subView', $subView);
                    $form->addHiddenValue('date', $date);
                    $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $row = $form->addRow();
                        $row->addLabel('direction', __('Bump Direction'));
                        $row->addSelect('direction')->fromArray(array('forward' => __('Forward'), 'backward' => __('Backward')))->required();

                    $form->addRow()->addContent(sprintf(__('Pressing "Yes" below will move this lesson, and all preceeding or succeeding lessons in this class, to the previous or next available time slot. <b>Are you sure you want to bump %1$s?'), $values['name']));

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();

                    echo $form->getOutput();
                }
            }
            //Print sidebar
            $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $todayStamp, $_SESSION[$guid]['pupilsightPersonID'], $dateStamp, $pupilsightCourseClassID);
        }
    }
}
