<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
    if ($pupilsightCourseClassID == '') {
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
            $class = $result->fetch();

            $page->breadcrumbs
              ->add(__('Manage {courseClass} ATLs', ['courseClass' => $class['course'].'.'.$class['class']]), 'atl_manage.php', ['pupilsightCourseClassID' => $pupilsightCourseClassID])
              ->add(__('Add Multiple Columns'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = Form::create('ATL', $_SESSION[$guid]['absoluteURL'].'/modules/ATL/atl_manage_addProcess.php?pupilsightCourseClassID='.$pupilsightCourseClassID.'&address='.$_SESSION[$guid]['address']);
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $form->addRow()->addHeading(__('Basic Information'));

            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT pupilsightYearGroup.name as groupBy, pupilsightCourseClassID as value, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightYearGroup ON (pupilsightCourse.pupilsightYearGroupIDList LIKE concat( '%', pupilsightYearGroup.pupilsightYearGroupID, '%' )) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.reportable='Y' ORDER BY pupilsightYearGroup.sequenceNumber, name";

            $row = $form->addRow();
                $row->addLabel('pupilsightCourseClassIDMulti', __('Class'));
                $row->addSelect('pupilsightCourseClassIDMulti')
                    ->fromQuery($pdo, $sql, $data, 'groupBy')
                    ->selectMultiple()
                    ->isRequired()
                    ->selected($pupilsightCourseClassID);

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
                $rubrics = $row->addSelect('pupilsightRubricID')->fromQuery($pdo, $sql, $data, 'groupBy')->placeholder();

                // Look for and select an Approach to Learning rubric
                $rubrics->selected(array_reduce($rubrics->getOptions(), function ($result, $items) {
                    foreach ($items as $key => $value) {
                        $result = (stripos($value, 'Approach to Learning') === false) ? $result : $key;
                    }
                    return $result;
                }, false));

            $form->addRow()->addHeading(__('Access'));

            $row = $form->addRow();
                $row->addLabel('completeDate', __('Go Live Date'))->prepend('1. ')->append('<br/>'.__('2. Column is hidden until date is reached.'));
                $row->addDate('completeDate');

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $pupilsightCourseClassID, 'manage', 'Manage ATLs_all');
}
