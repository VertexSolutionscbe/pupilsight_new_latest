<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if courseschool year specified
    $pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
    $pupilsightCourseID = $_GET['pupilsightCourseID'];

    if ($pupilsightDepartmentID == '' or $pupilsightCourseID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT pupilsightSchoolYear.name AS year, pupilsightDepartment.name AS department, pupilsightCourse.name AS course, description, pupilsightCourse.pupilsightSchoolYearID FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightDepartment.pupilsightDepartmentID=pupilsightCourse.pupilsightDepartmentID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightCourseID=:pupilsightCourseID';
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
            $values = $result->fetch();

            //Get role within learning area
            $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);

            $extra = '';
            if (($role == 'Coordinator' or $role == 'Assistant Coordinator' or $role == 'Teacher (Curriculum)' or $role == 'Teacher') and $values['pupilsightSchoolYearID'] != $_SESSION[$guid]['pupilsightSchoolYearID']) {
                $extra = ' '.$values['year'];
            }
            
            $urlParams = ['pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightCourseID' => $pupilsightCourseID];
            
            $page->breadcrumbs
                ->add(__('View All'), 'departments.php')
                ->add($values['department'], 'department.php', $urlParams)
                ->add($values['course'].$extra, 'department_course.php', $urlParams)
                ->add(__('Edit Course'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            if ($role != 'Coordinator' and $role != 'Assistant Coordinator' and $role != 'Teacher (Curriculum)') {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {

                $form = Form::create('courseEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/department_course_editProcess.php?pupilsightDepartmentID='.$pupilsightDepartmentID.'&pupilsightCourseID='.$pupilsightCourseID);
                
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                
                $form->addRow()->addHeading(__('Overview'));
                $form->addRow()->addEditor('description', $guid)->setRows(20)->setValue($values['description']);
            
                $row = $form->addRow();
                    $row->addSubmit();
                
                echo $form->getOutput();
            }
        }
    }
}
