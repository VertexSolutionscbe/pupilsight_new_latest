<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_class_edit_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
    $pupilsightCourseID = $_GET['pupilsightCourseID'];
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    if ($pupilsightCourseClassID == '' or $pupilsightCourseID == '' or $pupilsightSchoolYearID == '' or $pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightCourseID' => $pupilsightCourseID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT role, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.pupilsightPersonID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.name, pupilsightCourseClass.nameShort, pupilsightCourse.pupilsightCourseID, pupilsightCourse.name AS courseName, pupilsightCourse.nameShort as courseNameShort, pupilsightCourse.description AS courseDescription, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName, pupilsightCourseClassPerson.reportable FROM pupilsightPerson, pupilsightCourseClass, pupilsightCourseClassPerson,pupilsightCourse, pupilsightSchoolYear WHERE pupilsightPerson.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID AND pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID AND pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND (pupilsightPerson.status='Full' OR pupilsightPerson.status='Expected')";
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
            //Let's go!
            $values = $result->fetch();

            $urlParams = ['pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID];

            $page->breadcrumbs
                ->add(__('Course Enrolment by Class'), 'courseEnrolment_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
                ->add(__('Edit %1$s.%2$s Enrolment', ['%1$s' => $values['courseNameShort'], '%2$s' => $values['name']]), 'courseEnrolment_manage_class_edit.php', $urlParams)
                ->add(__('Edit Participant'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
			}
			
			$form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/courseEnrolment_manage_class_edit_editProcess.php?pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID");
                
			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);

			$row = $form->addRow();
				$row->addLabel('yearName', __('School Year'));
				$row->addTextField('yearName')->readonly()->setValue($values['yearName']);
			
			$row = $form->addRow();
				$row->addLabel('courseName', __('Course'));
				$row->addTextField('courseName')->readonly()->setValue($values['courseName']);

			$row = $form->addRow();
				$row->addLabel('name', __('Class'));
				$row->addTextField('name')->readonly()->setValue($values['name']);

			$row = $form->addRow();
				$row->addLabel('participant', __('Participant'));
				$row->addTextField('participant')->readonly()->setValue(Format::name('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student'));

			$roles = array(
                'Student'        => __('Student'),
                'Student - Left' => __('Student - Left'),
                'Teacher'        => __('Teacher'),
                'Teacher - Left' => __('Teacher - Left'),
                'Assistant'      => __('Assistant'),
                'Technician'     => __('Technician'),
                'Parent'         => __('Parent'),
            );

            $row = $form->addRow();
                $row->addLabel('role', __('Role'));
				$row->addSelect('role')->fromArray($roles)->required()->selected($values['role']);
			
			$row = $form->addRow();
				$row->addLabel('reportable', __('Reportable'));
				$row->addYesNo('reportable')->required()->selected($values['reportable']);

			$row = $form->addRow();
				$row->addFooter();
				$row->addSubmit();

			echo $form->getOutput();
        }
    }
}
