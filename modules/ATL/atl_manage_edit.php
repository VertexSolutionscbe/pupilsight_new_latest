<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Check if school year specified
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
        $atlColumnID = $_GET['atlColumnID'] ?? '';
        if ($pupilsightCourseClassID == '' or $atlColumnID == '') {
            echo "<div class='error'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = "SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.pupilsightDepartmentID, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightCourseClass WHERE pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightCourseClass.reportable='Y' ORDER BY course, class";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                try {
                    $data2 = array('atlColumnID' => $atlColumnID);
                    $sql2 = 'SELECT * FROM atlColumn WHERE atlColumnID=:atlColumnID';
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($data2);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($result2->rowCount() != 1) {
                    echo "<div class='error'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    //Let's go!
                    $class = $result->fetch();
                    $values = $result2->fetch();

                    $page->breadcrumbs
                        ->add(__('Manage {courseClass} ATLs', ['courseClass' => $class['course'].'.'.$class['class']]), 'atl_manage.php', ['pupilsightCourseClassID' => $pupilsightCourseClassID])
                        ->add(__('Edit Column'));

                    if (isset($_GET['return'])) {
                        returnProcess($guid, $_GET['return'], null, null);
                    }

                    $form = Form::create('ATL', $_SESSION[$guid]['absoluteURL'].'/modules/ATL/atl_manage_editProcess.php?atlColumnID='.$atlColumnID.'&pupilsightCourseClassID='.$pupilsightCourseClassID.'&address='.$_SESSION[$guid]['address']);
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $form->addRow()->addHeading(__('Basic Information'));

                    $row = $form->addRow();
                        $row->addLabel('className', __('Class'));
                        $row->addTextField('className')->readonly()->setValue(htmlPrep($class['course']).'.'.htmlPrep($class['class']));

                    $row = $form->addRow();
                        $row->addLabel('name', __('Name'));
                        $row->addTextField('name')->isRequired()->maxLength(20);

                    $row = $form->addRow();
                        $row->addLabel('description', __('Description'));
                        $row->addTextField('description')->isRequired()->maxLength(1000);

                    $form->addRow()->addHeading(__('Assessment'));

                    $data = array('pupilsightYearGroupIDList' => $class['pupilsightYearGroupIDList'], 'pupilsightDepartmentID' => $class['pupilsightDepartmentID'], 'rubrics' => __('Rubrics'));
                    $sql = "SELECT CONCAT(scope, ' ', :rubrics) as groupBy, pupilsightRubricID as value, 
                            (CASE WHEN category <> '' THEN CONCAT(category, ' - ', pupilsightRubric.name) ELSE pupilsightRubric.name END) as name 
                            FROM pupilsightRubric 
                            JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightRubric.pupilsightYearGroupIDList))
                            WHERE pupilsightRubric.active='Y' 
                            AND FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList) 
                            AND (scope='School' OR (scope='Learning Area' AND pupilsightDepartmentID=:pupilsightDepartmentID))
                            GROUP BY pupilsightRubric.pupilsightRubricID
                            ORDER BY scope, category, name";

                    $row = $form->addRow();
                        $row->addLabel('pupilsightRubricID', __('Rubric'));
                        $row->addSelect('pupilsightRubricID')->fromQuery($pdo, $sql, $data, 'groupBy')->placeholder();

                    $form->addRow()->addHeading(__('Access'));

                    $row = $form->addRow();
                        $row->addLabel('completeDate', __('Go Live Date'))->prepend('1. ')->append('<br/>'.__('2. Column is hidden until date is reached.'));
                        $row->addDate('completeDate');

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();

                    $form->loadAllValuesFrom($values);

                    echo $form->getOutput();
                }
            }

            //Print sidebar
            $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $pupilsightCourseClassID, 'manage', $highestAction);
        }
    }
}
