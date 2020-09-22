<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_class_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';
    $page->breadcrumbs
        ->add(__('Manage Courses & Classes'), 'course_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Edit Course & Classes'), 'course_manage_edit.php', ['pupilsightCourseID' => $pupilsightCourseID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Add Class'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable Admin/course_manage_class_edit.php&pupilsightCourseClassID='.$_GET['editID'].'&pupilsightCourseID='.$pupilsightCourseID.'&pupilsightSchoolYearID='.$pupilsightSchoolYearID;
    }

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT pupilsightCourseID, pupilsightCourse.name AS courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourse.description AS courseDescription, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName FROM pupilsightCourse, pupilsightSchoolYear WHERE pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
			$values = $result->fetch(); 

			$form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/course_manage_class_addProcess.php');

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
			$form->addHiddenValue('pupilsightCourseID', $pupilsightCourseID);
			
			$row = $form->addRow();
				$row->addLabel('schoolYearName', __('School Year'));
				$row->addTextField('schoolYearName')->required()->readonly()->setValue($values['yearName']);
			
			$row = $form->addRow();
				$row->addLabel('courseName', __('Course'));
				$row->addTextField('courseName')->required()->readonly()->setValue($values['courseName']);

			$row = $form->addRow();
				$row->addLabel('name', __('Name'))->description(__('Must be unique for this course.'));
				$row->addTextField('name')->required()->maxLength(30);
			
			$row = $form->addRow();
				$row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique for this course.'));
				$row->addTextField('nameShort')->required()->maxLength(8);

			$row = $form->addRow();
				$row->addLabel('reportable', __('Reportable?'))->description(__('Should this class show in reports?'));
				$row->addYesNo('reportable');

			if (isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byCourseClass.php")) {
				$row = $form->addRow();
				$row->addLabel('attendance', __('Track Attendance?'))->description(__('Should this class allow attendance to be taken?'));
				$row->addYesNo('attendance');
			}

			$row = $form->addRow();
				$row->addFooter();
				$row->addSubmit();
		
			echo $form->getOutput();
        }
    }
}
