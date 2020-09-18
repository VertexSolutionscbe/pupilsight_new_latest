<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\CourseGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Courses & Classes'), 'course_manage.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Edit Course & Classes'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (isset($_GET['deleteReturn'])) {
        $deleteReturn = $_GET['deleteReturn'];
    } else {
        $deleteReturn = '';
    }
    $deleteReturnMessage = '';
    $class = 'error';
    if (!($deleteReturn == '')) {
        if ($deleteReturn == 'success0') {
            $deleteReturnMessage = __('Your request was completed successfully.');
            $class = 'success';
        }
        echo "<div class='$class'>";
        echo $deleteReturnMessage;
        echo '</div>';
    }

    //Check if school year specified
    $pupilsightCourseID = $_GET['pupilsightCourseID'];
    if ($pupilsightCourseID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT pupilsightCourseID, pupilsightDepartmentID, pupilsightCourse.name AS name, pupilsightCourse.nameShort as nameShort, orderBy, pupilsightCourse.description, pupilsightCourse.map, pupilsightCourse.pupilsightSchoolYearID, pupilsightSchoolYear.name as yearName, pupilsightYearGroupIDList FROM pupilsightCourse, pupilsightSchoolYear WHERE pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
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

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/course_manage_editProcess.php?pupilsightCourseID='.$pupilsightCourseID);
			$form->setFactory(DatabaseFormFactory::create($pdo));

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('pupilsightSchoolYearID', $values['pupilsightSchoolYearID']);

			$row = $form->addRow();
				$row->addLabel('schoolYearName', __('School Year'));
				$row->addTextField('schoolYearName')->required()->readonly()->setValue($values['yearName']);

			$sql = "SELECT pupilsightDepartmentID as value, name FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
			$row = $form->addRow();
				$row->addLabel('pupilsightDepartmentID', __('Learning Area'));
				$row->addSelect('pupilsightDepartmentID')->fromQuery($pdo, $sql)->placeholder();

			$row = $form->addRow();
				$row->addLabel('name', __('Name'))->description(__('Must be unique for this school year.'));
				$row->addTextField('name')->required()->maxLength(60);

			$row = $form->addRow();
				$row->addLabel('nameShort', __('Short Name'));
				$row->addTextField('nameShort')->required()->maxLength(12);

			$row = $form->addRow();
				$row->addLabel('orderBy', __('Order'))->description(__('May be used to adjust arrangement of courses in reports.'));
				$row->addNumber('orderBy')->maxLength(3);

			$row = $form->addRow();
				$column = $row->addColumn('blurb');
				$column->addLabel('description', __('Blurb'));
				$column->addEditor('description', $guid)->setRows(20);

			$row = $form->addRow();
				$row->addLabel('map', __('Include In Curriculum Map'));
                $row->addYesNo('map')->required();

			$row = $form->addRow();
				$row->addLabel('pupilsightYearGroupIDList', __('Year Groups'))->description(__('Enrolable year groups.'));
				$row->addCheckboxYearGroup('pupilsightYearGroupIDList')->loadFromCSV($values);

			$row = $form->addRow();
				$row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo '<h2>';
            echo __('Edit Classes');
            echo '</h2>';

            $courseGateway = $container->get(CourseGateway::class);

            $classes = $courseGateway->selectClassesByCourseID($pupilsightCourseID);

            // DATA TABLE
            $table = DataTable::create('courseClassManage');

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/Timetable Admin/course_manage_class_add.php')
                ->addParam('pupilsightSchoolYearID', $values['pupilsightSchoolYearID'])
                ->addParam('pupilsightCourseID', $pupilsightCourseID)
                ->displayLabel();

            $table->addColumn('nameShort', __('Short Name'))->width('20%');
            $table->addColumn('name', __('Name'))->width('20%');
            $table->addColumn('participantsTotal', __('Participants'));
            $table->addColumn('reportable', __('Reportable'))->format(Format::using('yesNo', 'reportable'));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightSchoolYearID', $values['pupilsightSchoolYearID'])
                ->addParam('pupilsightCourseID', $pupilsightCourseID)
                ->addParam('pupilsightCourseClassID')
                ->format(function ($class, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Timetable Admin/course_manage_class_edit.php');

                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable Admin/course_manage_class_delete.php');

                    $actions->addAction('enrolment', __('Enrolment'))
                        ->setIcon('attendance')
                        ->setURL('/modules/Timetable Admin/courseEnrolment_manage_class_edit.php');
                });

            echo $table->render($classes->toDataSet());
        }
    }
}
