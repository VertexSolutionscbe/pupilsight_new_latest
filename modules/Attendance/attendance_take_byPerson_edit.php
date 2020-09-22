<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Module\Attendance\AttendanceView;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';

$urlParams = ['pupilsightPersonID' => $_GET['pupilsightPersonID'], 'currentDate' => $_GET['currentDate']];

$page->breadcrumbs
	->add(__('Take Attendance by Person'), 'attendance_take_byPerson.php', $urlParams)
	->add(__('Edit Attendance by Person'));      

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
	$pupilsightAttendanceLogPersonID = isset($_GET['pupilsightAttendanceLogPersonID'])? $_GET['pupilsightAttendanceLogPersonID'] : '';
	$pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';

	if ( empty($pupilsightAttendanceLogPersonID) || empty($pupilsightPersonID) ) {
		echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
	} else {
	    //Proceed!
	    if (isset($_GET['return'])) {
	        returnProcess($guid, $_GET['return'], null, array('error3' => __('Your request failed because the specified date is in the future, or is not a school day.')));
	    }
	    $attendance = new AttendanceView($pupilsight, $pdo);

	    try {
			$dataPerson = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightAttendanceLogPersonID' => $pupilsightAttendanceLogPersonID );
			$sqlPerson = "SELECT p.preferredName, p.surname, type, reason, comment, date, context, timestampTaken, pupilsightAttendanceLogPerson.pupilsightCourseClassID, t.preferredName as teacherPreferredName, t.surname as teacherSurname, pupilsightCourseClass.nameShort as className, pupilsightCourse.nameShort as courseName FROM pupilsightAttendanceLogPerson JOIN pupilsightPerson p ON (pupilsightAttendanceLogPerson.pupilsightPersonID=p.pupilsightPersonID) JOIN pupilsightPerson t ON (pupilsightAttendanceLogPerson.pupilsightPersonIDTaker=t.pupilsightPersonID) LEFT JOIN pupilsightCourseClass ON (pupilsightAttendanceLogPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) LEFT JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID ";
			$resultPerson = $connection2->prepare($sqlPerson);
			$resultPerson->execute($dataPerson);
		} catch (PDOException $e) {
			echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
		}

	    if ($resultPerson->rowCount() != 1) {
	    	echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
	    } else {
            $values = $resultPerson->fetch();
            $currentDate = dateConvertBack($guid, $values['date']);

			$form = Form::create('attendanceEdit', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byPerson_editProcess.php');
			$form->setAutocomplete('off');

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('pupilsightAttendanceLogPersonID', $pupilsightAttendanceLogPersonID);
			$form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
			$form->addHiddenValue('currentDate', $currentDate);

			$form->addRow()->addHeading(__('Edit Attendance'));

			$row = $form->addRow();
				$row->addLabel('student', __('Student'));
				$row->addTextField('student')->readonly()->setValue(formatName('', htmlPrep($values['preferredName']), htmlPrep($values['surname']), 'Student', true));

			$row = $form->addRow();
				$row->addLabel('date', __('Date'));
				$row->addDate('date')->readonly()->setValue($currentDate);

			$row = $form->addRow();
				$row->addLabel('recordedBy', __('Recorded By'));
				$row->addTextField('recordedBy')->readonly()->setValue(formatName('', htmlPrep($values['teacherPreferredName']), htmlPrep($values['teacherSurname']), 'Staff', false, true));

			$row = $form->addRow();
				$row->addLabel('time', __('Time'));
				$row->addTextField('time')->readonly()->setValue(substr($values['timestampTaken'], 11) . ' ' . dateConvertBack($guid, substr($values['timestampTaken'], 0, 10)));
				
			$row = $form->addRow();
				$row->addLabel('where', __('Where'));
				$row->addTextField('where')->readonly()->setValue(__($values['context']));

			$row = $form->addRow();
				$row->addLabel('type', __('Type'));
				$row->addSelect('type')->fromArray(array_keys($attendance->getAttendanceTypes()));

			$row = $form->addRow();
				$row->addLabel('reason', __('Reason'));
				$row->addSelect('reason')->fromArray($attendance->getAttendanceReasons());

			$row = $form->addRow();
				$row->addLabel('comment', __('Comment'))->description(__('255 character limit'));
				$row->addTextArea('comment')->setRows(3)->maxLength(255);

			$row = $form->addRow();
				$row->addFooter();
				$row->addSubmit()->addClass('submit_align submt');

			$form->loadAllValuesFrom($values);

			echo $form->getOutput();
	        
	    }
	}
}
